<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnualContractPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'title' => $this->title,
            'description' => $this->description,
            'financial' => [
                'total_planned' => (float) $this->total_planned_amount,
                'total_executed' => (float) $this->total_executed_amount,
                'execution_percentage' => $this->execution_percentage,
            ],
            'status' => $this->status,
            'status_label' => $this->status_label,
            'approval' => [
                'approved_by' => $this->approvedBy?->name,
                'approved_at' => $this->approved_at?->toISOString(),
            ],
            'created_by' => $this->createdBy?->name,
            'needs_count' => $this->needs->count(),
            'needs' => PlanNeedResource::collection($this->whenLoaded('needs')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
} 