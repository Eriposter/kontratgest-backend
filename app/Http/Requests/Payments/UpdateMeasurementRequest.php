<?php

declare(strict_types=1);

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeasurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('measurement'));
    }

    public function rules(): array
    {
        $measurementId = $this->route('measurement')?->id;

        return [
            'measurement_number' => [
                'sometimes',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('measurements', 'measurement_number')->ignore($measurementId),
            ],
            'period_start' => ['sometimes', 'date'],
            'period_end' => ['sometimes', 'date', 'after_or_equal:period_start'],
            'total_amount' => ['sometimes', 'numeric', 'min:0.01'],
            'retention_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.item_code' => ['nullable', 'string', 'max:50'],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.unit' => ['required_with:items', 'string', 'max:30'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.specific_data' => ['sometimes', 'array'],
        ];
    }
}