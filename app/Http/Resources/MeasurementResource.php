<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeasurementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'measurement_number' => $this->measurement_number,
            'sequence_number' => $this->sequence_number,
            
            // Contrato
            'contract' => [
                'id' => $this->contract->id,
                'number' => $this->contract->contract_number,
                'counterparty' => [
                    'id' => $this->contract->counterparty->id,
                    'name' => $this->contract->counterparty->name,
                ],
            ],
            
            // Período
            'period' => [
                'start' => $this->period_start->toDateString(),
                'end' => $this->period_end->toDateString(),
            ],
            
            // Valores
            'financial' => [
                'total_amount' => (float) $this->total_amount,
                'cumulative_amount' => (float) $this->cumulative_amount,
                'retention_percentage' => (float) $this->retention_percentage,
                'retention_amount' => (float) $this->retention_amount,
                'net_amount' => (float) $this->net_amount,
            ],
            
            // Estado
            'status' => $this->status,
            'observations' => $this->observations,
            
            // Aprovação
            'approval' => [
                'submitted_by' => $this->submitted_by,
                'submitted_at' => $this->submitted_at?->toISOString(),
                'approved_by' => $this->approved_by,
                'approved_at' => $this->approved_at?->toISOString(),
                'notes' => $this->approval_notes,
            ],
            
            // Pagamento
            'payment' => [
                'id' => $this->payment_id,
                'paid_at' => $this->paid_at?->toDateString(),
            ],
            
            // Ações permitidas
            'can_be_submitted' => $this->can_be_submitted,
            'can_be_approved' => $this->can_be_approved,
            'can_be_paid' => $this->can_be_paid,
            
            // Itens
            'items' => MeasurementItemResource::collection($this->whenLoaded('items')),
            
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Links
            'links' => [
                'self' => route('api.v1.measurements.show', $this->id),
            ],
        ];
    }
}