<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeasurementItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_code' => $this->item_code,
            'description' => $this->description,
            'unit' => $this->unit,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_amount' => (float) $this->total_amount,
            'specific_data' => $this->specific_data,
        ];
    }
}