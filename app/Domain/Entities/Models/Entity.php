<?php

declare(strict_types=1);

namespace App\Domain\Entities\Models;

use App\Support\Enums\EntityType;
use App\Support\Enums\Province;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Entity extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'entity_type',
        'name',
        'legal_name',
        'nif',
        'nif_type',
        'email',
        'phone',
        'phone_alt',
        'website',
        'address',
        'city',
        'province',
        'postal_code',
        'bank_accounts',
        'agt_certificate_expiry',
        'inss_certificate_expiry',
        'is_tax_exempt',
        'tax_regime',
        'activity_code',
        'notes',
        'status',
    ];

    protected $casts = [
        'entity_type' => EntityType::class,
        'province' => Province::class,
        'bank_accounts' => 'array',
        'agt_certificate_expiry' => 'date',
        'inss_certificate_expiry' => 'date',
        'is_tax_exempt' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function documents(): HasMany
    {
        return $this->hasMany(EntityDocument::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, EntityType $type)
    {
        return $query->where('entity_type', $type);
    }

    public function scopeWithExpiredCertificates($query)
    {
        return $query->where(function ($q) {
            $q->where('agt_certificate_expiry', '<', now())
              ->orWhere('inss_certificate_expiry', '<', now());
        });
    }

    public function scopeWithCertificatesExpiringIn($query, int $days = 30)
    {
        $deadline = now()->addDays($days);

        return $query->where(function ($q) use ($deadline) {
            $q->whereBetween('agt_certificate_expiry', [now(), $deadline])
              ->orWhereBetween('inss_certificate_expiry', [now(), $deadline]);
        });
    }

    // ─── Computed Properties ─────────────────────────────────

    public function getIsAgtCertificateValidAttribute(): bool
    {
        return $this->agt_certificate_expiry?->isFuture() ?? false;
    }

    public function getIsInssCertificateValidAttribute(): bool
    {
        return $this->inss_certificate_expiry?->isFuture() ?? false;
    }

    public function getIsCompliantAttribute(): bool
    {
        return $this->is_agt_certificate_valid && $this->is_inss_certificate_valid;
    }

    public function getDefaultBankAccountAttribute(): ?array
    {
        return collect($this->bank_accounts)->firstWhere('is_default', true)
            ?? $this->bank_accounts[0]
            ?? null;
    }

    // ─── Audit Logging ───────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Entidade {$this->name} foi {$eventName}");
    }
}