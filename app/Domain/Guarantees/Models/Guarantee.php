<?php

declare(strict_types=1);

namespace App\Domain\Guarantees\Models;

use App\Domain\Contracts\Models\Contract;
use App\Support\Enums\Currency;
use App\Support\Enums\GuaranteePurpose;
use App\Support\Enums\GuaranteeType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Guarantee extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'contract_id',
        'guarantee_number',
        'guarantee_type',
        'purpose',
        'issuing_entity',
        'issuing_entity_nif',
        'issuing_entity_contact',
        'currency',
        'amount',
        'exchange_rate',
        'issue_date',
        'expiry_date',
        'validity_days',
        'release_conditions',
        'release_date',
        'released_by',
        'release_notes',
        'was_executed',
        'executed_amount',
        'executed_at',
        'execution_reason',
        'status',
        'document_reference',
        'notes',
    ];

    protected $casts = [
        'guarantee_type' => GuaranteeType::class,
        'purpose' => GuaranteePurpose::class,
        'currency' => Currency::class,
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'validity_days' => 'integer',
        'release_date' => 'date',
        'was_executed' => 'boolean',
        'executed_amount' => 'decimal:2',
        'executed_at' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function documents(): HasMany
{
    // Se usares um modelo específico GuaranteeDocument:
    return $this->hasMany(GuaranteeDocument::class);
    
    // OU, se usares uma tabela polimórfica genérica 'documents':
    // return $this->morphMany(Document::class, 'documentable');
}

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('guarantee_type', $type);
    }

    public function scopeForPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeExpiringIn($query, int $days = 30)
    {
        $deadline = now()->addDays($days);

        return $query->where('status', 'active')
                     ->whereBetween('expiry_date', [now(), $deadline]);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
                     ->where('expiry_date', '<', now());
    }

    // ─── Computed Properties ─────────────────────────────────

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date?->isPast() ?? false;
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return (int) now()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = $this->days_until_expiry;
        return $days !== null && $days <= 30 && $days > 0;
    }

    public function getAmountInAoaAttribute(): float
    {
        if ($this->currency === Currency::AOA) {
            return (float) $this->amount;
        }

        return $this->amount * ($this->exchange_rate ?? 1);
    }

    public function getCanReleaseAttribute(): bool
    {
        return $this->status === 'active' && !$this->was_executed;
    }

    public function getCanExecuteAttribute(): bool
    {
        return $this->status === 'active' && !$this->was_executed;
    }

    // ─── Boot ────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        // Calcular validity_days automaticamente
        static::creating(function ($guarantee) {
            if ($guarantee->issue_date && $guarantee->expiry_date) {
                $guarantee->validity_days = (int) $guarantee->issue_date->diffInDays($guarantee->expiry_date);
            }
        });

        // Atualizar status para expired automaticamente
        static::updating(function ($guarantee) {
            if ($guarantee->isExpired && $guarantee->status === 'active') {
                $guarantee->status = 'expired';
            }
        });
    }

    // ─── Audit Logging ───────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Caução {$this->guarantee_number} foi {$eventName}");
    }
}
