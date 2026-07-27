<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->entity_type,
            'type_label' => $this->entity_type->label(),
            
            // Identificação
            'identification' => [
                'name' => $this->name,
                'legal_name' => $this->legal_name,
                'nif' => $this->nif,
                'nif_type' => $this->nif_type,
                'activity_code' => $this->activity_code,
            ],
            
            // Contacto
            'contact' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'phone_alt' => $this->phone_alt,
                'website' => $this->website,
            ],
            
            // Endereço
            'address' => [
                'street' => $this->address,
                'city' => $this->city,
                'province' => $this->province,
                'province_label' => $this->province?->label(),
                'postal_code' => $this->postal_code,
            ],
            
            // Bancário
            'banking' => [
                'accounts' => $this->bank_accounts,
                'default_account' => $this->default_bank_account,
            ],
            
            // Compliance (muito importante para Angola)
            'compliance' => [
                'is_compliant' => $this->is_compliant,
                'tax_exempt' => $this->is_tax_exempt,
                'tax_regime' => $this->tax_regime,
                'certificates' => [
                    'agt' => [
                        'expiry' => $this->agt_certificate_expiry?->toDateString(),
                        'is_valid' => $this->is_agt_certificate_valid,
                        'days_until_expiry' => $this->agt_certificate_expiry
                            ? (int) now()->diffInDays($this->agt_certificate_expiry, false)
                            : null,
                    ],
                    'inss' => [
                        'expiry' => $this->inss_certificate_expiry?->toDateString(),
                        'is_valid' => $this->is_inss_certificate_valid,
                        'days_until_expiry' => $this->inss_certificate_expiry
                            ? (int) now()->diffInDays($this->inss_certificate_expiry, false)
                            : null,
                    ],
                ],
            ],
            
            'notes' => $this->notes,
            'status' => $this->status,
            
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // HATEOAS links
            'links' => [
                'self' => route('api.v1.entities.show', $this->id),
                'documents' => route('api.v1.entities.documents.index', $this->id),
            ],
        ];
    }
}