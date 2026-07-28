<?php

declare(strict_types=1);

namespace App\Domain\PAC\Services;

use App\Domain\Contracts\Models\Contract;
use App\Domain\PAC\Models\AnnualContractPlan;
use App\Domain\PAC\Models\PlanNeed;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function generateContract(PlanNeed $need, array $contractData): Contract
    {
        // Criar contrato a partir da necessidade
        $contract = Contract::create(array_merge($contractData, [
            'company_id' => current_company()->id,
            'status' => 'draft',
            'pac_need_id' => $need->id,
        ]));

        // Atualizar necessidade
        $need->update([
            'contract_id' => $contract->id,
            'executed_amount' => $contract->total_amount,
            'status' => 'contracted',
            'pac_need_id' => $need->id,
        ]);

        // Recalcular totais do PAC
        $this->recalculateTotals($need->plan);

        return $contract;
    }

    private function recalculateTotals(AnnualContractPlan $plan): void
    {
        $plan->update([
            'total_planned_amount' => $plan->needs()->sum('estimated_amount'),
            'total_executed_amount' => $plan->needs()->whereNotNull('executed_amount')->sum('executed_amount'),
        ]);
    }
} 