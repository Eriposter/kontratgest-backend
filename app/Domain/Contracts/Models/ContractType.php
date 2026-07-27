<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_payment_terms',
        'required_guarantees',
        'specific_fields_schema',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_payment_terms' => 'array',
        'required_guarantees' => 'array',
        'specific_fields_schema' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
