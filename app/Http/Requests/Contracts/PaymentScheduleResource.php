<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'milestone_name' => $this->milestone_name,
            'sequence_order' => $this->sequence_order,
            'due_date' => $this->due_date?->toDateString(),
            'percentage' => $this->percentage ? (float) $this->percentage : null,
            'amount' => $this->amount ? (float) $this->amount : null,
            'calculated_amount' => (float) $this->calculated_amount,
            'is_conditional' => $this->is_conditional,
            'condition_type' => $this->condition_type,
            'condition_description' => $this->condition_description,
            'status' => $this->status,
            'is_overdue' => $this->is_overdue,
            'paid_at' => $this->paid_at?->toDateString(),
        ];
    }
}