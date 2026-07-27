<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxConfigurationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tax_type' => $this->tax_type,
            'name' => $this->name,
            'description' => $this->description,
            'rate' => (float) $this->rate,
            'applicable_rules' => $this->applicable_rules,
            'valid_from' => $this->valid_from->toDateString(),
            'valid_to' => $this->valid_to?->toDateString(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}