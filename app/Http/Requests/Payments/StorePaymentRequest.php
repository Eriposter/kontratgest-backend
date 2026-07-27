<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use App\Support\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Payments\Models\Payment::class);
    }

    public function rules(): array
    {
        return [
            'contract_id' => ['required', 'uuid', 'exists:contracts,id'],
            'payment_schedule_id' => ['nullable', 'uuid', 'exists:payment_schedules,id'],
            
            'payment_type' => ['required', 'in:invoice,advance,measurement,milestone,final'],
            
            // Valores
            'currency' => ['sometimes', Rule::enum(Currency::class)],
            'gross_amount' => ['required', 'numeric', 'min:0.01'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            
            // Datas
            'due_date' => ['nullable', 'date'],
            'invoice_date' => ['nullable', 'date'],
            
            // Fatura
            'invoice_number' => ['nullable', 'string', 'max:100'],
            
            // Retenção manual (se necessário)
            'retention_amount' => ['nullable', 'numeric', 'min:0'],
            
            // Notas
            'payment_notes' => ['nullable', 'string', 'max:2000'],
            
            // Documentos de suporte
            'supporting_documents' => ['sometimes', 'array'],
            'supporting_documents.*' => ['url'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'O pagamento deve ter pelo menos um documento de suporte.',
        ];
    }
}