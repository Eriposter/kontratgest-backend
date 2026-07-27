<?php

declare(strict_types=1);

namespace App\Domain\Payments\Models;

use App\Domain\Contracts\Models\Contract;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Measurement extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'contract_id',
        'measurement_number',
        'sequence_number',
        'period_start',
        'period_end',
        'total_amount',
        'cumulative_amount',
        'retention_percentage',
        'retention_amount',
        'status',
        'observations',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'payment_id',
        'paid_at',
    ];

    protected $casts = [
        'sequence_number' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'cumulative_amount' => 'decimal:2',
        'retention_percentage' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MeasurementItem::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // ─── Computed Properties ─────────────────────────────────

    public function getNetAmountAttribute(): float
    {
        return $this->total_amount - $this->retention_amount;
    }

    public function getCanBeSubmittedAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getCanBeApprovedAttribute(): bool
    {
        return $this->status === 'submitted';
    }

    public function getCanBePaidAttribute(): bool
    {
        return $this->status === 'approved' && !$this->payment_id;
    }

    // ─── Audit Logging ───────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Auto de Medição {$this->measurement_number} foi {$eventName}");
    }
}