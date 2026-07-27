<?php

declare(strict_types=1);

namespace App\Http\Requests\Entities;

use App\Support\Enums\EntityType;
use App\Support\Enums\Province;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('entity'));
    }

    public function rules(): array
    {
        $entityId = $this->route('entity')?->id;

        return [
            'entity_type' => ['sometimes', Rule::enum(EntityType::class)],
            'name' => ['sometimes', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'nif' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) use ($entityId) {
                    $cleanNif = preg_replace('/\D/', '', $value);
                    
                    if (!in_array(strlen($cleanNif), [10, 14])) {
                        $fail('O NIF deve ter 10 dígitos (pessoa coletiva) ou 14 dígitos (pessoa singular).');
                    }
                    
                    
                    // Verifica unicidade ignorando o próprio registro
                    $exists = \App\Domain\Entities\Models\Entity::where('nif', $cleanNif)
                        ->where('id', '!=', $entityId)
                        ->exists();
                    
                    if ($exists) {
                        $fail('Este NIF já se encontra registado no sistema.');
                    }
                },
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'phone_alt' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', Rule::enum(Province::class)],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'bank_accounts' => ['sometimes', 'array'],
            'bank_accounts.*.bank' => ['required_with:bank_accounts', 'string', 'max:100'],
            'bank_accounts.*.iban' => ['required_with:bank_accounts', 'string', 'max:34'],
            'bank_accounts.*.account_holder' => ['required_with:bank_accounts', 'string', 'max:255'],
            'bank_accounts.*.is_default' => ['boolean'],
            'agt_certificate_expiry' => ['nullable', 'date'],
            'inss_certificate_expiry' => ['nullable', 'date'],
            'is_tax_exempt' => ['boolean'],
            'tax_regime' => ['sometimes', 'in:general,simplified,exempt'],
            'activity_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'in:active,suspended,blacklisted'],
        ];
    }
}