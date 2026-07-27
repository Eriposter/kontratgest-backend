<?php

declare(strict_types=1);

namespace App\Domain\Payments\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeasurementItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'measurement_id',
        'item_code',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total_amount',
        'specific_data',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'specific_data' => 'array',
    ];

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }
}