<?php

declare(strict_types=1);

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
            'executed_amount' => $this->executed_amount ? (float) $this->executed_amount : null,
            'priority' => $this->priority,
            'priority_label' => $this->priority_label,
            'planned_quarter' => $this->planned_quarter,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'contract' => $this->whenLoaded('contract', function () {
                return [
                    'id' => $this->contract->id,
                    'contract_number' => $this->contract->contract_number,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}