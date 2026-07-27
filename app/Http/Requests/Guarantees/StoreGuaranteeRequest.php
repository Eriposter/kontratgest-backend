<?php

declare(strict_types=1);

namespace App\Http\Requests\Guarantees;

use App\Support\Enums\Currency;
use App\Support\Enums\GuaranteePurpose;
use App\Support\Enums\GuaranteeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuaranteeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Guarantees\Models\Guarantee::class);
    }

    public function rules(): array
    {
        return [
            'contract_id' => ['required', 'uuid', 'exists:contracts,id'],
            'guarantee_number' => ['nullable', 'string', 'max:50', 'unique:guarantees,guarantee_number'],
            'guarantee_type' => ['required', Rule::enum(GuaranteeType::class)],
            'purpose' => ['required', Rule::enum(GuaranteePurpose::class)],
            
            // Entidade emissora
            'issuing_entity' => ['required', 'string', 'max:255'],
            'issuing_entity_nif' => ['nullable', 'string', 'size:9', 'regex:/^\d{9}$/'],
            'issuing_entity_contact' => ['nullable', 'string', 'max:255'],
            
            // Valores
            'currency' => ['required', Rule::enum(Currency::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            
            // Datas
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
            
            // Condições
            'release_conditions' => ['nullable', 'string', 'max:2000'],
            'document_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'expiry_date.after' => 'A data de expiração deve ser posterior à data de emissão.',
            'issuing_entity_nif.size' => 'O NIF deve ter exatamente 9 dígitos.',
            'issuing_entity_nif.regex' => 'O NIF deve conter apenas dígitos numéricos.',
        ];
    }
}