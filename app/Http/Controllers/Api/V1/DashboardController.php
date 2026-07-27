<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Entities\Models\Entity;
use App\Domain\Guarantees\Models\Guarantee;
use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\Payment;
use App\Http\Controllers\Controller;
use App\Support\Enums\ContractStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/v1/dashboard/overview
     * Retorna todas as métricas do dashboard numa única chamada.
     */
    public function overview(): JsonResponse
    {
        // ─── Contratos ──────────────────────────────────────
        $activeContracts = Contract::where('status', ContractStatus::ACTIVE)->count();
        $pendingContracts = Contract::where('status', ContractStatus::PENDING_APPROVAL)->count();
        $draftContracts = Contract::where('status', ContractStatus::DRAFT)->count();
        
        $totalContractValue = Contract::where('status', ContractStatus::ACTIVE)
            ->selectRaw("SUM(CASE WHEN currency = 'AOA' THEN total_amount ELSE total_amount * COALESCE(exchange_rate, 1) END) as total")
            ->value('total') ?? 0;

        // Contratos por tipo
        $contractsByType = Contract::select('contract_types.name as type', DB::raw('count(*) as count'))
            ->join('contract_types', 'contracts.contract_type_id', '=', 'contract_types.id')
            ->where('contracts.status', ContractStatus::ACTIVE)
            ->groupBy('contract_types.name')
            ->pluck('count', 'type')
            ->toArray();

        // ─── Cauções ────────────────────────────────────────
        $guaranteesExpiring = Guarantee::active()
            ->expiringIn(30)
            ->with(['contract.counterparty'])
            ->get();

        $guaranteesExpired = Guarantee::where('status', 'active')
            ->where('expiry_date', '<', now())
            ->count();

        $totalGuaranteeValue = Guarantee::active()->sum('amount');

        // ─── Pagamentos ─────────────────────────────────────
        $overduePayments = Payment::overdue()
            ->with(['contract.counterparty'])
            ->get();

        $pendingPayments = Payment::pending()
            ->with(['contract.counterparty'])
            ->get();

        $paidThisMonth = Payment::paid()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('net_amount');

        $totalOverdueAmount = $overduePayments->sum('net_amount');

        // ─── Autos de Medição ───────────────────────────────
        $pendingMeasurements = Measurement::where('status', 'submitted')->count();
        $approvedUnpaidMeasurements = Measurement::approved()
            ->whereNull('payment_id')
            ->count();

        // ─── Entidades (Compliance) ─────────────────────────
        $entitiesWithExpiredCertificates = Entity::active()
            ->withExpiredCertificates()
            ->count();

        $entitiesWithExpiringCertificates = Entity::active()
            ->withCertificatesExpiringIn(30)
            ->with(['documents'])
            ->get();

        // ─── Atividade Recente (simulada com últimos registos) ─
        $recentActivities = $this->getRecentActivities();

        return response()->json([
            'data' => [
                'contracts' => [
                    'active' => $activeContracts,
                    'pending_approval' => $pendingContracts,
                    'draft' => $draftContracts,
                    'total_value' => (float) $totalContractValue,
                    'by_type' => $contractsByType,
                ],
                'guarantees' => [
                    'expiring_soon' => $guaranteesExpiring->map(fn ($g) => [
                        'id' => $g->id,
                        'number' => $g->guarantee_number,
                        'amount' => (float) $g->amount,
                        'currency' => $g->currency->value,
                        'expiry_date' => $g->expiry_date->toDateString(),
                        'days_until_expiry' => $g->days_until_expiry,
                        'counterparty' => $g->contract->counterparty->name,
                        'contract_number' => $g->contract->contract_number,
                    ]),
                    'expired' => $guaranteesExpired,
                    'total_value' => (float) $totalGuaranteeValue,
                ],
                'payments' => [
                    'overdue' => $overduePayments->map(fn ($p) => [
                        'id' => $p->id,
                        'number' => $p->payment_number,
                        'net_amount' => (float) $p->net_amount,
                        'currency' => $p->currency->value,
                        'due_date' => $p->due_date?->toDateString(),
                        'days_overdue' => $p->due_date ? now()->diffInDays($p->due_date) : 0,
                        'counterparty' => $p->contract->counterparty->name,
                    ]),
                    'pending' => $pendingPayments->count(),
                    'total_overdue_amount' => (float) $totalOverdueAmount,
                    'paid_this_month' => (float) $paidThisMonth,
                ],
                'measurements' => [
                    'pending_approval' => $pendingMeasurements,
                    'approved_unpaid' => $approvedUnpaidMeasurements,
                ],
                'compliance' => [
                    'entities_expired_certificates' => $entitiesWithExpiredCertificates,
                    'entities_expiring_certificates' => $entitiesWithExpiringCertificates->map(fn ($e) => [
                        'id' => $e->id,
                        'name' => $e->name,
                        'nif' => $e->nif,
                        'agt_expiry' => $e->agt_certificate_expiry?->toDateString(),
                        'inss_expiry' => $e->inss_certificate_expiry?->toDateString(),
                        'days_until_agt_expiry' => $e->agt_certificate_expiry 
                            ? (int) now()->diffInDays($e->agt_certificate_expiry, false) 
                            : null,
                        'days_until_inss_expiry' => $e->inss_certificate_expiry 
                            ? (int) now()->diffInDays($e->inss_certificate_expiry, false) 
                            : null,
                    ]),
                ],
                'recent_activities' => $recentActivities,
            ],
        ]);
    }

    /**
     * Obter atividades recentes (baseado em activity_log).
     */
    private function getRecentActivities(): array
    {
        try {
            return DB::table('activity_log')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    $properties = json_decode($log->properties, true) ?? [];
                    $attributes = $properties['attributes'] ?? [];
                    
                    return [
                        'id' => $log->id,
                        'event' => $log->event,
                        'description' => $log->description,
                        'subject_type' => class_basename($log->subject_type),
                        'subject_id' => $log->subject_id,
                        'created_at' => $log->created_at,
                        'entity_name' => $attributes['name'] 
                            ?? $attributes['title'] 
                            ?? $attributes['contract_number'] 
                            ?? $attributes['measurement_number']
                            ?? $attributes['guarantee_number']
                            ?? $attributes['payment_number']
                            ?? 'Registo',
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}