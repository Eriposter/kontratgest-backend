<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeasurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Domain\Payments\Models\Measurement::class);
    }

    public function rules(): array
    {
        return [
            'contract_id' => ['required', 'uuid', 'exists:contracts,id'],
            'measurement_number' => ['nullable', 'string', 'max:50', 'unique:measurements,measurement_number'],
            
            // Período
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            
            // Valores
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'retention_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            
            // Observações
            'observations' => ['nullable', 'string', 'max:2000'],
            
            // Itens
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_code' => ['nullable', 'string', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.specific_data' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'period_end.after_or_equal' => 'A data de fim deve ser posterior ou igual à data de início.',
            'items.required' => 'O auto de medição deve ter pelo menos um item.',
            'items.min' => 'O auto de medição deve ter pelo menos um item.',
        ];
    }
}