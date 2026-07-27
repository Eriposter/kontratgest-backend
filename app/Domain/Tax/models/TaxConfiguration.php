<?php

declare(strict_types=1);

namespace App\Domain\Tax\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxConfiguration extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tax_type',
        'name',
        'description',
        'rate',
        'applicable_rules',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'applicable_rules' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeValidAt($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('valid_from', '<=', $date)
                     ->where(function ($q) use ($date) {
                         $q->whereNull('valid_to')
                           ->orWhere('valid_to', '>=', $date);
                     });
    }

    // ─── Static Methods ──────────────────────────────────────

    public static function getCurrentRate(string $type, $date = null): ?float
    {
        $config = self::active()
            ->ofType($type)
            ->validAt($date)
            ->orderByDesc('valid_from')
            ->first();

        return $config?->rate;
    }
}