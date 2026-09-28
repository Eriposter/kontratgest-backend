<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractingProcedureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan_need_id' => $this->plan_need_id,
            'contract_id' => $this->contract_id,
            'winning_entity_id' => $this->winning_entity_id,
            'procedure_start_date' => $this->procedure_start_date?->toDateString(),
            'procedure_end_date' => $this->procedure_end_date?->toDateString(),
            'confirmed_start_date' => $this->confirmed_start_date?->toDateString(),
            'confirmed_end_date' => $this->confirmed_end_date?->toDateString(),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'duration_days' => $this->duration_days,
            'documents' => $this->documents,
            'notes' => $this->notes,
            'adjudication_notes' => $this->adjudication_notes,
            'created_by' => $this->createdBy?->name,
            'completed_by' => $this->completed_by,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            
            // Relações
            'need' => $this->whenLoaded('need', fn() => [
                'id' => $this->need->id,
                'title' => $this->need->title,
                'estimated_amount' => $this->need->estimated_amount,
                'plan' => $this->need->whenLoaded('plan', fn() => [
                    'year' => $this->need->plan->year,
                    'title' => $this->need->plan->title,
                ]),
            ]),
            'contract' => $this->whenLoaded('contract', fn() => [
                'id' => $this->contract->id,
                'contract_number' => $this->contract->contract_number,
                'title' => $this->contract->title,
            ]),
            'winning_entity' => $this->whenLoaded('winningEntity', fn() => [
                'id' => $this->winningEntity->id,
                'name' => $this->winningEntity->name ?? $this->winningEntity->identification->name ?? null,
            ]),
        ];
    }
}