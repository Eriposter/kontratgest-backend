<?php

declare(strict_types=1);

namespace App\Domain\Payments\Models;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Models\PaymentSchedule;
use App\Support\Enums\Currency;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'contract_id',
        'payment_schedule_id',
        'payment_number',
        'payment_type',
        'currency',
        'gross_amount',
        'exchange_rate',
        'vat_rate',
        'vat_amount',
        'withholding_tax_rate',
        'withholding_tax_amount',
        'stamp_duty_rate',
        'stamp_duty_amount',
        'retention_amount',
        'net_amount',
        'due_date',
        'invoice_date',
        'payment_date',
        'bank_reference',
        'payment_method',
        'status',
        'payment_notes',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'invoice_number',
        'supporting_documents',
    ];

    protected $casts = [
        'currency' => Currency::class,
        'gross_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'withholding_tax_rate' => 'decimal:2',
        'withholding_tax_amount' => 'decimal:2',
        'stamp_duty_rate' => 'decimal:2',
        'stamp_duty_amount' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'due_date' => 'date',
        'invoice_date' => 'date',
        'payment_date' => 'date',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'supporting_documents' => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PaymentSchedule::class, 'payment_schedule_id');
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'approved')
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now());
    }

    // ─── Computed Properties ─────────────────────────────────

    public function getTotalTaxAmountAttribute(): float
    {
        return $this->vat_amount 
             + $this->withholding_tax_amount 
             + $this->stamp_duty_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'approved' 
            && $this->due_date?->isPast() 
            && $this->status !== 'paid';
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return (int) now()->diffInDays($this->due_date, false);
    }

    public function getCanBeApprovedAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getCanBePaidAttribute(): bool
    {
        return $this->status === 'approved';
    }

    // ─── Audit Logging ───────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Pagamento {$this->payment_number} foi {$eventName}");
    }
}