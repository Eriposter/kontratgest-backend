<?php

declare(strict_types=1);

namespace App\Http\Requests\Contracts;

use App\Support\Enums\Currency;
use App\Support\Enums\PaymentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('contract'));
    }

    public function rules(): array
    {
        $contractId = $this->route('contract')?->id;

        return [
            'contract_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('contracts', 'contract_number')->ignore($contractId),
            ],
            'contract_type_id' => ['sometimes', 'uuid', 'exists:contract_types,id'],
            'counterparty_id' => ['sometimes', 'uuid', 'exists:entities,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'object' => ['nullable', 'string', 'max:5000'],
            'currency' => ['sometimes', Rule::enum(Currency::class)],
            'total_amount' => ['sometimes', 'numeric', 'min:0.01'],
            'vat_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'withholding_tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'signature_date' => ['nullable', 'date'],
            'duration_months' => ['nullable', 'integer', 'min:1'],
            'payment_model' => ['sometimes', Rule::enum(PaymentModel::class)],
            'requires_bna_registration' => ['boolean'],
            'bna_registration_number' => ['nullable', 'string', 'max:50'],
            'tribunal_de_contas_visto' => ['boolean'],
            'tribunal_visto_number' => ['nullable', 'string', 'max:50'],
            'specific_data' => ['sometimes', 'array'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'payment_schedules' => ['sometimes', 'array'],
            'payment_schedules.*.milestone_name' => ['required_with:payment_schedules', 'string', 'max:255'],
            'payment_schedules.*.due_date' => ['nullable', 'date'],
            'payment_schedules.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_schedules.*.amount' => ['nullable', 'numeric', 'min:0'],
            'payment_schedules.*.is_conditional' => ['boolean'],
            'payment_schedules.*.condition_type' => ['nullable', 'string', 'max:30'],
            'payment_schedules.*.condition_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}