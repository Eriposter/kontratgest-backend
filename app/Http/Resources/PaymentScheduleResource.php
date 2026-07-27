<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Obter o total_amount do contexto ou calcular de forma segura
        $contractTotalAmount = $this->additional['contract_total_amount'] ?? null;
    
    // Se não temos contexto, tentar carregar de forma segura
    if ($contractTotalAmount === null && $this->relationLoaded('contract')) {
        $contractTotalAmount = (float) $this->contract->total_amount;
    }
    
    // Calcular o valor do marco
    $calculatedAmount = 0.0;
    if ($this->amount) {
        $calculatedAmount = (float) $this->amount;
    } elseif ($this->percentage && $contractTotalAmount !== null) {
        $calculatedAmount = $contractTotalAmount * ((float) $this->percentage / 100);
    }

    return [
        'id' => $this->id,
        'milestone_name' => $this->milestone_name,
        'sequence_order' => $this->sequence_order,
        'due_date' => $this->due_date?->toDateString(),
        'percentage' => $this->percentage ? (float) $this->percentage : null,
        'amount' => $this->amount ? (float) $this->amount : null,
        'calculated_amount' => $calculatedAmount,
        'is_conditional' => $this->is_conditional,
        'condition_type' => $this->condition_type,
        'condition_description' => $this->condition_description,
        'status' => $this->status,
        'is_overdue' => $this->is_overdue,
        'paid_at' => $this->paid_at?->toDateString(),
    ];
}
}