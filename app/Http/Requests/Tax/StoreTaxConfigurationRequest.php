<?php

declare(strict_types=1);

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Tax\Models\TaxConfiguration::class);
    }

    public function rules(): array
    {
        return [
            'tax_type' => ['required', 'in:iva,industrial,stamp_duty,withholding'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            
            'applicable_rules' => ['sometimes', 'array'],
            'applicable_rules.is_exempt' => ['sometimes', 'boolean'],
            'applicable_rules.applies_to' => ['sometimes', 'array'],
            'applicable_rules.applies_to.*' => ['string'],
            'applicable_rules.min_amount' => ['sometimes', 'numeric', 'min:0'],
            'applicable_rules.entity_type' => ['sometimes', 'in:individual,collective,public'],
            
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'valid_to.after_or_equal' => 'A data de validade final deve ser posterior ou igual à data inicial.',
        ];
    }
}