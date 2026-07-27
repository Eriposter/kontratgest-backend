<?php

declare(strict_types=1);

namespace App\Http\Requests\Guarantees;

use App\Support\Enums\Currency;
use App\Support\Enums\GuaranteePurpose;
use App\Support\Enums\GuaranteeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuaranteeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('guarantee'));
    }

    public function rules(): array
    {
        $guaranteeId = $this->route('guarantee')?->id;

        return [
            'guarantee_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('guarantees', 'guarantee_number')->ignore($guaranteeId),
            ],
            'guarantee_type' => ['sometimes', Rule::enum(GuaranteeType::class)],
            'purpose' => ['sometimes', Rule::enum(GuaranteePurpose::class)],
            'issuing_entity' => ['sometimes', 'string', 'max:255'],
            'issuing_entity_nif' => ['nullable', 'string', 'size:9', 'regex:/^\d{9}$/'],
            'issuing_entity_contact' => ['nullable', 'string', 'max:255'],
            'currency' => ['sometimes', Rule::enum(Currency::class)],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'issue_date' => ['sometimes', 'date'],
            'expiry_date' => ['sometimes', 'date', 'after:issue_date'],
            'release_conditions' => ['nullable', 'string', 'max:2000'],
            'document_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}