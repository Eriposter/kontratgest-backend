<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
            
            // Tipo e contraparte
            'type' => [
                'id' => $this->type->id,
                'code' => $this->type->code,
                'name' => $this->type->name,
            ],
            'counterparty' => [
                'id' => $this->counterparty->id,
                'name' => $this->counterparty->name,
                'nif' => $this->counterparty->nif,
            ],
            
            // Detalhes
            'title' => $this->title,
            'description' => $this->description,
            'object' => $this->object,
            
            // Financeiro
            'financial' => [
                'currency' => $this->currency->value,
                'currency_symbol' => $this->currency->symbol(),
                'total_amount' => (float) $this->total_amount,
                'vat_rate' => (float) $this->vat_rate,
                'vat_amount' => (float) $this->vat_amount,
                'withholding_tax_rate' => (float) $this->withholding_tax_rate,
                'withholding_tax_amount' => (float) $this->withholding_tax_amount,
                'net_amount' => (float) $this->net_amount,
                'exchange_rate' => $this->exchange_rate ? (float) $this->exchange_rate : null,
            ],
            
            // Datas
            'dates' => [
                'start' => $this->start_date->toDateString(),
                'end' => $this->end_date?->toDateString(),
                'signature' => $this->signature_date?->toDateString(),
                'duration_months' => $this->duration_months,
                'days_until_expiry' => $this->days_until_expiry,
                'is_expired' => $this->is_expired,
            ],
            
            // Pagamento
            'payment' => [
                'model' => $this->payment_model->value,
                'model_label' => $this->payment_model->label(),
                'total_paid' => (float) $this->total_paid,
                'balance' => (float) $this->balance,
                'schedules' => PaymentScheduleResource::collection($this->whenLoaded('paymentSchedules')),
            ],
            
            // Compliance
            'compliance' => [
                'requires_bna' => $this->requires_bna_registration,
                'bna_number' => $this->bna_registration_number,
                'bna_date' => $this->bna_registration_date?->toDateString(),
                'tribunal_visto' => $this->tribunal_de_contas_visto,
                'tribunal_visto_number' => $this->tribunal_visto_number,
                'tribunal_visto_date' => $this->tribunal_visto_date?->toDateString(),
            ],
            
            // Dados específicos
            'specific_data' => $this->specific_data,
            
            // Estado
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            
            'internal_notes' => $this->internal_notes,
            
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Links
            'links' => [
                'self' => route('api.v1.contracts.show', $this->id),
                'documents' => route('api.v1.contracts.documents.index', $this->id),
            ],
        ];
    }
}
