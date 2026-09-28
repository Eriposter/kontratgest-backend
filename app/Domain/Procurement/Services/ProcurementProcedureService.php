<?php

namespace App\Domain\Procurement\Services;

use App\Domain\Procurement\Models\ProcurementProcedure;
use App\Domain\Procurement\Models\ProcurementPhase;
use App\Domain\PAC\Models\PlanNeed;
use App\Support\Enums\ProcedureType;
use App\Support\Enums\ProcedureStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProcurementProcedureService
{
    public function list(?string $status = null, ?string $type = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = ProcurementProcedure::with(['need', 'contract', 'winningEntity', 'phases'])
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('procedure_type', $type);
        }

        return $query->paginate($perPage);
    }

    public function find(string $id): ProcurementProcedure
    {
        return ProcurementProcedure::with([
            'need', 'need.plan', 'contract', 'winningEntity',
            'phases', 'documents', 'candidates', 'candidates.entity'
        ])->findOrFail($id);
    }

    public function findByNeed(string $needId): ?ProcurementProcedure
    {
        return ProcurementProcedure::with(['phases', 'documents'])
            ->where('plan_need_id', $needId)
            ->first();
    }

    public function create(PlanNeed $need, array $data): ProcurementProcedure
    {
        return DB::transaction(function () use ($need, $data) {
            $procedure = ProcurementProcedure::create([
                'plan_need_id' => $need->id,
                'procedure_type' => $data['procedure_type'],
                'procedure_start_date' => $data['procedure_start_date'],
                'procedure_end_date' => $data['procedure_end_date'],
                'confirmed_start_date' => $data['confirmed_start_date'] ?? null,
                'confirmed_end_date' => $data['confirmed_end_date'] ?? null,
                'status' => ProcedureStatus::PLANNED,
                'estimated_amount' => $need->estimated_amount,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Criar fases automaticamente baseado no tipo de procedimento
            $procedureType = ProcedureType::from($data['procedure_type']);
            $phases = $procedureType->phases();

            foreach ($phases as $index => $phaseName) {
                ProcurementPhase::create([
                    'procedure_id' => $procedure->id,
                    'phase_name' => $phaseName,
                    'sequence_order' => $index + 1,
                    'status' => 'pending',
                ]);
            }

            // Atualizar necessidade para "em contratação"
            $need->update(['status' => 'in_progress']);

            return $procedure->fresh(['phases']);
        });
    }

    public function start(ProcurementProcedure $procedure): ProcurementProcedure
    {
        $procedure->update(['status' => ProcedureStatus::IN_PROGRESS]);

        // Ativar primeira fase
        $firstPhase = $procedure->phases()->where('sequence_order', 1)->first();
        if ($firstPhase) {
            $firstPhase->update([
                'status' => 'ongoing',
                'start_date' => now(),
            ]);
        }

        return $procedure->fresh(['phases']);
    }

    public function completePhase(ProcurementProcedure $procedure, string $phaseId, array $data): ProcurementPhase
    {
        $phase = $procedure->phases()->findOrFail($phaseId);

        $phase->update([
            'status' => 'completed',
            'end_date' => $data['end_date'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'observations' => $data['observations'] ?? null,
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        // Ativar próxima fase
        $nextPhase = $procedure->phases()
            ->where('sequence_order', '>', $phase->sequence_order)
            ->where('status', 'pending')
            ->orderBy('sequence_order')
            ->first();

        if ($nextPhase) {
            $nextPhase->update([
                'status' => 'ongoing',
                'start_date' => now(),
            ]);
        } else {
            // Todas as fases completas
            $procedure->update(['status' => ProcedureStatus::EVALUATION]);
        }

        return $phase->fresh();
    }

    public function complete(
        ProcurementProcedure $procedure,
        string $winningEntityId,
        ?string $adjudicationNotes = null,
        ?array $documents = null
    ): ProcurementProcedure {
        return DB::transaction(function () use ($procedure, $winningEntityId, $adjudicationNotes, $documents) {
            $procedure->update([
                'status' => ProcedureStatus::COMPLETED,
                'winning_entity_id' => $winningEntityId,
                'adjudication_notes' => $adjudicationNotes,
                'documents' => $documents ?? $procedure->documents,
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            // Atualizar candidato vencedor
            $procedure->candidates()->where('entity_id', $winningEntityId)->update(['status' => 'winner']);

            // Atualizar necessidade
            $procedure->need->update(['status' => 'contracted']);

            return $procedure->fresh(['phases', 'winningEntity']);
        });
    }

    public function cancel(ProcurementProcedure $procedure, string $reason): ProcurementProcedure
    {
        return DB::transaction(function () use ($procedure, $reason) {
            $procedure->update([
                'status' => ProcedureStatus::CANCELLED,
                'notes' => trim(($procedure->notes ?? '') . "\n[Cancelado] {$reason}"),
            ]);

            // Cancelar todas as fases pendentes/em curso
            $procedure->phases()
                ->whereIn('status', ['pending', 'ongoing'])
                ->update(['status' => 'cancelled']);

            // Reverter necessidade
            $procedure->need->update(['status' => 'planned']);

            return $procedure->fresh(['phases']);
        });
    }
}