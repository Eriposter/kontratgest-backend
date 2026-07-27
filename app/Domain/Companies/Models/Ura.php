<?php

declare(strict_types=1);

namespace App\Domain\Companies\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ura extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'department',
        'members',
        'is_active',
    ];

    protected $casts = [
        'members' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(\App\Domain\Contracts\Models\Contract::class, 'ura_id');
    }
}