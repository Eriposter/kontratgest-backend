<?php

declare(strict_types=1);

namespace App\Domain\PAC\Services;

use App\Domain\PAC\Models\ContractingProcedure;
use App\Domain\PAC\Models\PlanNeed;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContractingProcedureService
{
    public function list(?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = ContractingProcedure::with(['need', 'contract', 'winningEntity', 'createdBy'])
            ->whereHas('need.plan', fn($q) => $q->where('company_id', current_company()->id))
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function findByNeed(string $needId): ?ContractingProcedure
    {
        return ContractingProcedure::with(['need', 'contract', 'winningEntity'])
            ->where('plan_need_id', $needId)
            ->first();
    }

    public function create(PlanNeed $need, array $data): ContractingProcedure
    {
        return ContractingProcedure::create([
            'plan_need_id' => $need->id,
            'procedure_start_date' => $data['procedure_start_date'],
            'procedure_end_date' => $data['procedure_end_date'],
            'confirmed_start_date' => $data['confirmed_start_date'] ?? null,
            'confirmed_end_date' => $data['confirmed_end_date'] ?? null,
            'status' => 'planned',
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    public function update(ContractingProcedure $procedure, array $data): ContractingProcedure
    {
        $procedure->update($data);
        return $procedure->fresh();
    }

    public function start(ContractingProcedure $procedure): ContractingProcedure
    {
        $procedure->update(['status' => 'in_progress']);
        return $procedure->fresh();
    }

    public function moveToEvaluation(ContractingProcedure $procedure): ContractingProcedure
    {
        $procedure->update(['status' => 'evaluation']);
        return $procedure->fresh();
    }

    public function complete(
        ContractingProcedure $procedure,
        string $winningEntityId,
        ?string $adjudicationNotes = null,
        ?array $documents = null
    ): ContractingProcedure {
        $procedure->update([
            'status' => 'completed',
            'winning_entity_id' => $winningEntityId,
            'adjudication_notes' => $adjudicationNotes,
            'documents' => $documents ?? $procedure->documents,
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        // Atualizar necessidade para "em contratação"
        $procedure->need->update(['status' => 'in_progress']);

        return $procedure->fresh();
    }

    public function cancel(ContractingProcedure $procedure, string $reason): ContractingProcedure
    {
        $procedure->update([
            'status' => 'cancelled',
            'notes' => trim(($procedure->notes ?? '') . "\n[Cancelado] {$reason}"),
        ]);

        // Reverter necessidade para "planeada"
        $procedure->need->update(['status' => 'planned']);

        return $procedure->fresh();
    }
}