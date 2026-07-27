<?php

declare(strict_types=1);

namespace App\Http\Requests\Entities;

use App\Support\Enums\EntityType;
use App\Support\Enums\Province;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Entity::class);
    }

    public function rules(): array
    {
        return [
            'entity_type' => ['required', Rule::enum(EntityType::class)],
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'nif' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleanNif = preg_replace('/\D/', '', $value);
                    
                    // Verifica se é 10 dígitos (coletiva) ou 14 dígitos (singular)
                    if (!in_array(strlen($cleanNif), [10, 14])) {
                        $fail('O NIF deve ter 10 dígitos (pessoa coletiva) ou 14 dígitos (pessoa singular).');
                    }
                    
                },
                'unique:entities,nif',
            ],
            
            // Contacto
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'phone_alt' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            
            // Endereço
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', Rule::enum(Province::class)],
            'postal_code' => ['nullable', 'string', 'max:20'],
            
            // Bancário
            'bank_accounts' => ['sometimes', 'array'],
            'bank_accounts.*.bank' => ['required_with:bank_accounts', 'string', 'max:100'],
            'bank_accounts.*.iban' => [
                'required_with:bank_accounts',
                'string',
                'max:34',
                'regex:/^AO\d{23}$/',
            ],
            'bank_accounts.*.account_holder' => ['required_with:bank_accounts', 'string', 'max:255'],
            'bank_accounts.*.is_default' => ['boolean'],
            
            // Compliance
            'agt_certificate_expiry' => ['nullable', 'date', 'after:today'],
            'inss_certificate_expiry' => ['nullable', 'date', 'after:today'],
            'is_tax_exempt' => ['boolean'],
            'tax_regime' => ['sometimes', 'in:general,simplified,exempt'],
            'activity_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nif.unique' => 'Este NIF já se encontra registado no sistema.',
            'bank_accounts.*.iban.regex' => 'O IBAN angolano deve começar por AO seguido de 23 dígitos.',
            'agt_certificate_expiry.after' => 'A certidão da AGT deve ter uma data futura.',
            'inss_certificate_expiry.after' => 'A certidão do INSS deve ter uma data futura.',
        ];
    }
}