<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'payment_type' => $this->payment_type,
            
            // Contrato
            'contract' => [
                'id' => $this->contract->id,
                'number' => $this->contract->contract_number,
                'counterparty' => [
                    'id' => $this->contract->counterparty->id,
                    'name' => $this->contract->counterparty->name,
                ],
            ],
            
            // Schedule (se existir)
            'schedule' => $this->whenLoaded('schedule', function () {
                return [
                    'id' => $this->schedule->id,
                    'milestone' => $this->schedule->milestone_name,
                ];
            }),
            
            // Measurement (se existir)
            'measurement' => $this->whenLoaded('measurement', function () {
                return [
                    'id' => $this->measurement->id,
                    'number' => $this->measurement->measurement_number,
                ];
            }),
            
            // Valores
            'financial' => [
                'currency' => $this->currency->value,
                'currency_symbol' => $this->currency->symbol(),
                'gross_amount' => (float) $this->gross_amount,
                'exchange_rate' => $this->exchange_rate ? (float) $this->exchange_rate : null,
                
                // Impostos
                'vat' => [
                    'rate' => (float) $this->vat_rate,
                    'amount' => (float) $this->vat_amount,
                ],
                'withholding_tax' => [
                    'rate' => (float) $this->withholding_tax_rate,
                    'amount' => (float) $this->withholding_tax_amount,
                ],
                'stamp_duty' => [
                    'rate' => (float) $this->stamp_duty_rate,
                    'amount' => (float) $this->stamp_duty_amount,
                ],
                'retention_amount' => (float) $this->retention_amount,
                
                'total_tax' => (float) $this->total_tax_amount,
                'net_amount' => (float) $this->net_amount,
            ],
            
            // Datas
            'dates' => [
                'due' => $this->due_date?->toDateString(),
                'invoice' => $this->invoice_date?->toDateString(),
                'payment' => $this->payment_date?->toDateString(),
                'days_until_due' => $this->days_until_due,
                'is_overdue' => $this->is_overdue,
            ],
            
            // Referência bancária
            'bank' => [
                'reference' => $this->bank_reference,
                'method' => $this->payment_method,
            ],
            
            // Estado
            'status' => $this->status,
            'payment_notes' => $this->payment_notes,
            
            // Aprovação
            'approval' => [
                'requested_by' => $this->requested_by,
                'requested_at' => $this->requested_at?->toISOString(),
                'approved_by' => $this->approved_by,
                'approved_at' => $this->approved_at?->toISOString(),
            ],
            
            // Fatura
            'invoice' => [
                'number' => $this->invoice_number,
                'documents' => $this->supporting_documents,
            ],
            
            // Ações permitidas
            'can_be_approved' => $this->can_be_approved,
            'can_be_paid' => $this->can_be_paid,
            
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Links
            'links' => [
                'self' => route('api.v1.payments.show', $this->id),
            ],

            'measurement' => $this->whenLoaded('measurement', function () {
    return [
        'id' => $this->measurement->id,
        'number' => $this->measurement->measurement_number,
    ];
}),
        ];
    }
}