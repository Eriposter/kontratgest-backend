<?php

declare(strict_types=1);

namespace App\Domain\Tax\Models;

use App\Domain\Companies\Models\Company;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxConfiguration extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'tax_type',
        'name',
        'description',
        'rate',
        'applicable_rules',
        'valid_from',
        'valid_to',
        'is_active',
        'applies_to',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'applicable_rules' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
        'applies_to' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}