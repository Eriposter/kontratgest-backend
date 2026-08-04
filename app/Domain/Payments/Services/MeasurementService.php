<?php

declare(strict_types=1);

namespace App\Domain\Payments\Services;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\MeasurementItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeasurementService
{
    /**
     * Listar autos de medição.
     */
    public function list(
        ?string $contractId = null,
        ?string $status = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Measurement::query()
            ->with(['contract.counterparty', 'items'])
            ->orderByDesc('created_at');

        if ($contractId) {
            $query->where('contract_id', $contractId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

        /**
     * Criar novo auto de medição (Versão Blindada contra erros NOT NULL)
     */
    public function create(array $data): Measurement
    {
        // 1. Extrair os itens dos dados
        $items = $data['items'] ?? [];
        unset($data['items']); // ← REMOVE para evitar MassAssignmentException

        // 2. Obter o contrato para cálculos de sequência
        $contract = \App\Domain\Contracts\Models\Contract::findOrFail($data['contract_id']);

        // 3. Gerar sequência e número da medição
        $data['sequence_number'] = Measurement::where('contract_id', $contract->id)->count() + 1;
        
        $year = date('Y');
        $month = date('m');
        $count = Measurement::whereYear('created_at', $year)
                           ->whereMonth('created_at', $month)
                           ->count() + 1;
        $data['measurement_number'] = "AM/{$year}/{$month}/" . str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        // 4. 🔥 CÁLCULOS FINANCEIROS OBRIGATÓRIOS (Resolve os erros NOT NULL)
        $totalAmount = (float) ($data['total_amount'] ?? 0);
        $retentionPercentage = (float) ($data['retention_percentage'] ?? 0);

        // Calcula o valor da retenção
        $data['retention_amount'] = $totalAmount * ($retentionPercentage / 100);

        // Calcula o valor acumulado (Soma dos autos anteriores deste contrato + o atual)
        $previousTotal = Measurement::where('contract_id', $contract->id)->sum('total_amount');
        $data['cumulative_amount'] = $previousTotal + $totalAmount;

        // 5. Definir valores padrão de sistema
        $data['company_id'] = current_company()->id;
        $data['created_by'] = auth()->id();
        $data['status'] = $data['status'] ?? 'draft';

        // 6. Criar a medição (Agora com TODOS os campos NOT NULL preenchidos)
        $measurement = Measurement::create($data);

        // 7. Criar os itens associados
        if (!empty($items)) {
            foreach ($items as $item) {
                $measurement->items()->create([
                    'item_code' => $item['item_code'] ?? '',
                    'description' => $item['description'],
                    'unit' => $item['unit'] ?? 'un',
                    'quantity' => (float) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'total_amount' => (float) $item['quantity'] * (float) $item['unit_price'],
                ]);
            }
        }

        return $measurement->load('items', 'contract');
    }

    public function update(Measurement $measurement, array $data): Measurement
    {
        // 1. Extrair os itens
        $items = $data['items'] ?? null;
        unset($data['items']); // ← REMOVE

        // 2. Atualizar os dados principais da medição
        $measurement->update($data);

        // 3. Sincronizar os itens
        if ($items !== null) {
            DB::transaction(function () use ($measurement, $items) {
                $measurement->items()->delete();
                
                foreach ($items as $item) {
                    $measurement->items()->create([
                        'item_code' => $item['item_code'] ?? '',
                        'description' => $item['description'],
                        'unit' => $item['unit'] ?? 'un',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }
            });
        }

        return $measurement->fresh()->load('items', 'contract');
    }

    /**
     * Submeter auto de medição para aprovação.
     */
    public function submit(Measurement $measurement, string $submittedBy): Measurement
    {
        if (!$measurement->can_be_submitted) {
            throw new \InvalidArgumentException('Auto de medição não pode ser submetido neste estado.');
        }

        $measurement->update([
            'status' => 'submitted',
            'submitted_by' => $submittedBy,
            'submitted_at' => now(),
        ]);

        return $measurement;
    }

    /**
     * Aprovar auto de medição.
     */
    public function approve(Measurement $measurement, string $approvedBy, string $notes = ''): Measurement
    {
        if (!$measurement->can_be_approved) {
            throw new \InvalidArgumentException('Auto de medição não pode ser aprovado neste estado.');
        }

        $measurement->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $measurement;
    }

    /**
     * Rejeitar auto de medição.
     */
    public function reject(Measurement $measurement, string $notes): Measurement
    {
        if ($measurement->status !== 'submitted') {
            throw new \InvalidArgumentException('Apenas autos submetidos podem ser rejeitados.');
        }

        $measurement->update([
            'status' => 'rejected',
            'approval_notes' => $notes,
        ]);

        return $measurement;
    }

    /**
 * Marcar auto de medição como pago
 */
public function markAsPaid(Measurement $measurement, string $paymentId): Measurement
{
    $measurement->update([
        'status' => 'paid',
        'payment_id' => $paymentId,
        'paid_at' => now(),
    ]);

    return $measurement;
}

    /**
     * Obter autos pendentes de aprovação.
     */
    public function getPendingMeasurements(): Collection
    {
        return Measurement::pending()
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Obter autos aprovados mas não pagos.
     */
    public function getApprovedButUnpaidMeasurements(): Collection
    {
        return Measurement::approved()
            ->whereNull('payment_id')
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Gerar número de auto de medição.
     */
    private function generateMeasurementNumber(Contract $contract): string
    {
        $year = date('Y');
        $count = Measurement::whereYear('created_at', $year)->count();

        return sprintf('AM/%s/%05d', $year, $count + 1);
    }

    /**
     * Criar itens do auto de medição.
     */
    private function createItems(Measurement $measurement, array $items): void
    {
        foreach ($items as $item) {
            // Calcular total automaticamente
            $totalAmount = $item['quantity'] * $item['unit_price'];

            MeasurementItem::create([
                'measurement_id' => $measurement->id,
                'item_code' => $item['item_code'] ?? null,
                'description' => $item['description'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_amount' => $totalAmount,
                'specific_data' => $item['specific_data'] ?? [],
            ]);
        }
    }
}