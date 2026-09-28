<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementProcedureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan_need_id' => $this->plan_need_id,
            'contract_id' => $this->contract_id,
            'winning_entity_id' => $this->winning_entity_id,
            'procedure_type' => $this->procedure_type->value,
            'procedure_type_label' => $this->procedure_type_label,
            'procedure_start_date' => $this->procedure_start_date?->toDateString(),
            'procedure_end_date' => $this->procedure_end_date?->toDateString(),
            'confirmed_start_date' => $this->confirmed_start_date?->toDateString(),
            'confirmed_end_date' => $this->confirmed_end_date?->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status_label,
            'estimated_amount' => $this->estimated_amount,
            'contracted_amount' => $this->contracted_amount,
            'duration_days' => $this->duration_days,
            'progress_percentage' => $this->progress_percentage,
            'completed_phases_count' => $this->completed_phases_count,
            'total_phases_count' => $this->total_phases_count,
            'documents' => $this->documents,
            'notes' => $this->notes,
            'adjudication_notes' => $this->adjudication_notes,
            'created_by' => $this->createdBy?->name,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            
            // Relações
            'need' => $this->whenLoaded('need', function () {
                $needData = [
                    'id' => $this->need->id,
                    'title' => $this->need->title,
                    'estimated_amount' => $this->need->estimated_amount,
                ];
                
                // ✅ CORREÇÃO: Usar relationLoaded() no Model em vez de whenLoaded()
                if ($this->need->relationLoaded('plan')) {
                    $needData['plan'] = [
                        'year' => $this->need->plan->year,
                        'title' => $this->need->plan->title,
                    ];
                }
                
                return $needData;
            }),
            
            'contract' => $this->whenLoaded('contract', fn() => [
                'id' => $this->contract->id,
                'contract_number' => $this->contract->contract_number,
                'title' => $this->contract->title,
            ]),
            
            'winning_entity' => $this->whenLoaded('winningEntity', fn() => [
                'id' => $this->winningEntity->id,
                'name' => $this->winningEntity->name ?? ($this->winningEntity->identification->name ?? null),
            ]),
            
            'phases' => ProcurementPhaseResource::collection($this->whenLoaded('phases')),
            
            'candidates' => $this->whenLoaded('candidates', fn() => 
                $this->candidates->map(fn($candidate) => [
                    'id' => $candidate->id,
                    'entity_id' => $candidate->entity_id,
                    'entity_name' => $candidate->entity?->name ?? ($candidate->entity?->identification->name ?? null),
                    'status' => $candidate->status,
                    'status_label' => $candidate->status_label,
                    'proposed_amount' => $candidate->proposed_amount,
                    'technical_score' => $candidate->technical_score,
                    'financial_score' => $candidate->financial_score,
                    'total_score' => $candidate->total_score,
                ])
            ),
        ];
    }
}