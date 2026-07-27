<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuaranteeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'guarantee_number' => $this->guarantee_number,
            
            // Tipo e propósito
            'type' => [
                'code' => $this->guarantee_type->value,
                'label' => $this->guarantee_type->label(),
            ],
            'purpose' => [
                'code' => $this->purpose->value,
                'label' => $this->purpose->label(),
            ],
            
            // Contrato
            'contract' => [
                'id' => $this->contract->id,
                'number' => $this->contract->contract_number,
                'counterparty' => [
                    'id' => $this->contract->counterparty->id,
                    'name' => $this->contract->counterparty->name,
                ],
            ],
            
            // Entidade emissora
            'issuer' => [
                'name' => $this->issuing_entity,
                'nif' => $this->issuing_entity_nif,
                'contact' => $this->issuing_entity_contact,
            ],
            
            // Valores
            'financial' => [
                'currency' => $this->currency->value,
                'currency_symbol' => $this->currency->symbol(),
                'amount' => (float) $this->amount,
                'exchange_rate' => $this->exchange_rate ? (float) $this->exchange_rate : null,
                'amount_in_aoa' => (float) $this->amount_in_aoa,
            ],
            
            // Datas
            'dates' => [
                'issue' => $this->issue_date->toDateString(),
                'expiry' => $this->expiry_date->toDateString(),
                'validity_days' => $this->validity_days,
                'days_until_expiry' => $this->days_until_expiry,
                'is_expired' => $this->is_expired,
                'is_expiring_soon' => $this->is_expiring_soon,
            ],
            
            // Libertação
            'release' => [
                'conditions' => $this->release_conditions,
                'date' => $this->release_date?->toDateString(),
                'released_by' => $this->released_by,
                'notes' => $this->release_notes,
            ],
            
            // Execução
            'execution' => [
                'was_executed' => $this->was_executed,
                'amount' => $this->executed_amount ? (float) $this->executed_amount : null,
                'date' => $this->executed_at?->toDateString(),
                'reason' => $this->execution_reason,
            ],
            
            // Estado
            'status' => $this->status,
            'can_release' => $this->can_release,
            'can_execute' => $this->can_execute,
            
            'document_reference' => $this->document_reference,
            'notes' => $this->notes,
            
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Documentos
            'documents' => GuaranteeDocumentResource::collection($this->whenLoaded('documents')),
            
            // Links
            'links' => [
                'self' => route('api.v1.guarantees.show', $this->id),
            ],
        ];
    }
}