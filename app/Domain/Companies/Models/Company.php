<?php

declare(strict_types=1);

namespace App\Domain\Companies\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'nif',
        'logo_path',
        'company_type',
        'sector',
        'legal_nature',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'settings',
        'enabled_features',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'enabled_features' => 'array',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function entities(): HasMany
    {
        return $this->hasMany(\App\Domain\Entities\Models\Entity::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(\App\Domain\Contracts\Models\Contract::class);
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(ContractProcedure::class);
    }

    public function uras(): HasMany
    {
        return $this->hasMany(Ura::class);
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function isPublic(): bool
    {
        return $this->company_type === 'public';
    }

    public function isPrivate(): bool
    {
        return $this->company_type === 'private';
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->enabled_features ?? [], true);
    }

    public function enableFeature(string $feature): void
    {
        $features = $this->enabled_features ?? [];
        if (!in_array($feature, $features, true)) {
            $features[] = $feature;
            $this->update(['enabled_features' => $features]);
        }
    }

    public function disableFeature(string $feature): void
    {
        $features = $this->enabled_features ?? [];
        $features = array_filter($features, fn($f) => $f !== $feature);
        $this->update(['enabled_features' => array_values($features)]);
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
}