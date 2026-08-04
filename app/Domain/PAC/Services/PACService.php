<?php

declare(strict_types=1);

namespace App\Domain\PAC\Services;

use App\Domain\Contracts\Models\Contract;
use App\Domain\PAC\Models\AnnualContractPlan;
use App\Domain\PAC\Models\PlanNeed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Domain\Contracts\Models\ContractType;

class PACService
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = AnnualContractPlan::with(['needs', 'createdBy', 'approvedBy'])
            ->where('company_id', current_company()->id);

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        return $query->orderByDesc('year')
                    ->orderByDesc('created_at')
                    ->paginate($perPage);
    }

    public function find(string $id): AnnualContractPlan
    {
        return AnnualContractPlan::with(['needs.contract', 'createdBy', 'approvedBy'])
            ->findOrFail($id);
    }

    public function create(array $data): AnnualContractPlan
    {
        $data['company_id'] = current_company()->id;
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        return AnnualContractPlan::create($data);
    }

    public function update(AnnualContractPlan $plan, array $data): AnnualContractPlan
    {
        $plan->update($data);
        return $plan->fresh();
    }

    public function submit(AnnualContractPlan $plan): AnnualContractPlan
    {
        $plan->update(['status' => 'submitted']);
        return $plan->fresh();
    }

    public function approve(AnnualContractPlan $plan): AnnualContractPlan
    {
        $plan->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return $plan->fresh();
    }

    public function cancel(AnnualContractPlan $plan): AnnualContractPlan
    {
        $plan->update(['status' => 'cancelled']);
        return $plan->fresh();
    }

    // ─── Necessidades ──────────────────────────────────────

    public function addNeed(AnnualContractPlan $plan, array $data): PlanNeed
    {
        $need = $plan->needs()->create($data);
        $this->recalculateTotals($plan);
        return $need;
    }

    public function updateNeed(PlanNeed $need, array $data): PlanNeed
    {
        $need->update($data);
        $this->recalculateTotals($need->plan);
        return $need->fresh();
    }

    public function deleteNeed(PlanNeed $need): void
    {
        $plan = $need->plan;
        $need->delete();
        $this->recalculateTotals($plan);
    }

    public function generateContract(PlanNeed $need, array $data): Contract
{
    // Buscar o tipo de contrato
    $contractType = ContractType::where('code', $data['contract_type'])->first();
    
    if (!$contractType) {
        throw new \Exception("Tipo de contrato '{$data['contract_type']}' não encontrado");
    }

    // 🔥 OBTER COMPANY_ID DE FORMA SEGURA
    $user = auth()->user();
    $companyId = $user->company_id ?? null;
    
    // Se o utilizador não tiver company_id, tentar obter da empresa atual
    if (!$companyId && function_exists('current_company')) {
        $companyId = current_company()->id ?? null;
    }

    // Garantir que payment_model tem valor
    $paymentModel = $data['payment_model'] ?? 'single';

    // Criar o contrato
    $contractData = [
        'pac_need_id' => $need->id,
        'contract_number' => $this->generateContractNumber(),
        'contract_type_id' => $contractType->id,
        'contract_type_specification' => $data['contract_type_specification'] ?? null,
        'title' => $data['title'],
        'object' => $data['object'] ?? $data['title'],
        'counterparty_id' => $data['counterparty_id'],
        'total_amount' => $data['total_amount'],
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'signature_date' => $data['signature_date'] ?? null,
        'vat_rate' => $data['vat_rate'] ?? 14,
        'withholding_tax_rate' => $data['withholding_tax_rate'] ?? 2,
        'payment_model' => $paymentModel,
        'currency' => 'AOA',
        'status' => 'draft',
        'internal_notes' => $data['notes'] ?? null,
        'current_progress' => 0,
    ];

    // Só adicionar company_id se existir
    if ($companyId) {
        $contractData['company_id'] = $companyId;
    }

    $contract = Contract::create($contractData);

    // Atualizar a necessidade do PAC
    $need->update([
        'status' => 'contracted',
        'executed_amount' => $data['total_amount'],
        'contract_id' => $contract->id,
    ]);

    // Atualizar o PAC
    if (method_exists($need->plan, 'updateFinancials')) {
        $need->plan->updateFinancials();
    }

    return $contract;
}

private function generateContractNumber(): string
{
    $year = date('Y');
    
    // Buscar o último contrato do ano
    $lastContract = Contract::whereYear('created_at', $year)
        ->orderBy('created_at', 'desc')
        ->first();
    
    if ($lastContract) {
        // Extrair o número do final do contract_number
        // Ex: CT/2026/0005 -> 5
        $parts = explode('/', $lastContract->contract_number);
        $lastNumber = (int) end($parts);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    // 🔥 CONVERTER PARA STRING ANTES DO STR_PAD
    $formattedNumber = str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    
    return "CT/{$year}/{$formattedNumber}";
}

    private function recalculateTotals(AnnualContractPlan $plan): void
    {
        $plan->update([
            'total_planned_amount' => $plan->needs()->sum('estimated_amount'),
            'total_executed_amount' => $plan->needs()->whereNotNull('executed_amount')->sum('executed_amount'),
        ]);
    }
}