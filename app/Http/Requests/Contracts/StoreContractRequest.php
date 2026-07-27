<?php

declare(strict_types=1);

namespace App\Http\Requests\Contracts;

use App\Support\Enums\Currency;
use App\Support\Enums\PaymentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Contracts\Models\Contract::class);
    }

    public function rules(): array
    {
        return [
            'contract_number' => ['nullable', 'string', 'max:50', 'unique:contracts,contract_number'],
            'contract_type_id' => ['required', 'uuid', 'exists:contract_types,id'],
            'counterparty_id' => ['required', 'uuid', 'exists:entities,id'],
            
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'object' => ['nullable', 'string', 'max:5000'],
            
            // Financeiro
            'currency' => ['required', Rule::enum(Currency::class)],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'vat_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'withholding_tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            
            // Datas
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'signature_date' => ['nullable', 'date'],
            'duration_months' => ['nullable', 'integer', 'min:1'],
            
            // Pagamento
            'payment_model' => ['required', Rule::enum(PaymentModel::class)],
            
            // Compliance
            'requires_bna_registration' => ['boolean'],
            'bna_registration_number' => ['nullable', 'string', 'max:50'],
            'tribunal_de_contas_visto' => ['boolean'],
            'tribunal_visto_number' => ['nullable', 'string', 'max:50'],
            
            // Dados específicos
            'specific_data' => ['sometimes', 'array'],
            
            // Notas
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            
            // Plano de pagamentos
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

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'A data de fim deve ser posterior ou igual à data de início.',
            'payment_schedules.*.percentage.max' => 'A percentagem não pode exceder 100%.',
        ];
    }
}