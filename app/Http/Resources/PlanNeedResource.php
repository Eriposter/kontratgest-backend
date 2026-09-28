<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanNeedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_type' => $this->contract_type,
            'contract_type_label' => $this->contract_type_label,
            'procedure_type' => $this->procedure_type,
            'procedure_type_label' => $this->procedure_type_label,
            'title' => $this->title,
            'description' => $this->description,
            'justification' => $this->justification,
            'estimated_amount' => (float) $this->estimated_amount,
            'executed_amount' => (float) $this->executed_amount,
            'priority' => $this->priority,
            'priority_label' => $this->priority_label,
            'planned_quarter' => $this->planned_quarter,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'proposed_start_date' => $this->proposed_start_date?->toDateString(),
            'proposed_end_date' => $this->proposed_end_date?->toDateString(),
            
            // 🔥 ADICIONAR ESTES DOIS CAMPOS
            'contracting_procedure' => $this->whenLoaded('contractingProcedure', function () {
                if (!$this->contractingProcedure) return null;
                return [
                    'id' => $this->contractingProcedure->id,
                    'status' => $this->contractingProcedure->status,
                    'status_label' => $this->contractingProcedure->status_label,
                ];
            }),
            
            'contract' => $this->whenLoaded('contract', function () {
                if (!$this->contract) return null;
                return [
                    'id' => $this->contract->id,
                    'contract_number' => $this->contract->contract_number,
                    'title' => $this->contract->title,
                ];
            }),
            
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}