<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Models;

use App\Domain\Entities\Models\Entity;
use App\Support\Enums\ContractStatus;
use App\Support\Enums\Currency;
use App\Support\Enums\PaymentModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contract extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
    'contract_number',
    'contract_type_id',
    'counterparty_id',
    'company_id',
    'title',
    'description',
    'object',
    'currency',
    'total_amount',
    'vat_rate',
    'withholding_tax_rate',
    'exchange_rate',
    'start_date',
    'end_date',
    'signature_date',
    'duration_months',
    'payment_model',
    'requires_bna_registration',
    'bna_registration_number',
    'bna_registration_date',
    'tribunal_de_contas_visto',
    'tribunal_visto_number',
    'tribunal_visto_date',
    'specific_data',
    'status',
    'created_by',
    'approved_by',
    'approved_at',
    'internal_notes',
    'pac_need_id',
    
    // ─── NOVOS CAMPOS DE PROGRESSO ──────────────────────────
    'current_progress',              // ← ADICIONAR
    'progress_last_updated_at',      // ← ADICIONAR
    
    // Campos públicos (setor público)
    'procedure_id',
    'ura_id',
    'procedure_type',
    'procedure_number',
    'publication_date',
    'diary_series',
    'publication_number',
    'tribunal_visto_status',
    'provisional_receipt_date',
    'definitive_receipt_date',
];

    protected $casts = [
        'currency' => Currency::class,
        'payment_model' => PaymentModel::class,
        'status' => ContractStatus::class,
        'total_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'withholding_tax_rate' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'start_date' => 'date',
        'end_date' => 'date',
        'signature_date' => 'date',
        'bna_registration_date' => 'date',
        'tribunal_visto_date' => 'date',
        'specific_data' => 'array',
        'requires_bna_registration' => 'boolean',
        'tribunal_de_contas_visto' => 'boolean',
        'approved_at' => 'datetime',
        'current_progress' => 'decimal:2',
        'progress_last_updated_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'counterparty_id');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class)->orderBy('sequence_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', ContractStatus::ACTIVE);
    }

    public function scopeOfType($query, string $typeCode)
    {
        return $query->whereHas('type', fn ($q) => $q->where('code', $typeCode));
    }

    public function scopeExpiringIn($query, int $days = 30)
    {
        $deadline = now()->addDays($days);

        return $query->whereNotNull('end_date')
                     ->whereBetween('end_date', [now(), $deadline]);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('end_date')
                     ->where('end_date', '<', now())
                     ->whereIn('status', [ContractStatus::ACTIVE, ContractStatus::APPROVED]);
    }

    // ─── Computed Properties ─────────────────────────────────

    public function getVatAmountAttribute(): float
    {
        return $this->total_amount * ($this->vat_rate / 100);
    }

    public function getWithholdingTaxAmountAttribute(): float
    {
        return $this->total_amount * ($this->withholding_tax_rate / 100);
    }

    public function getNetAmountAttribute(): float
    {
        return $this->total_amount + $this->vat_amount - $this->withholding_tax_amount;
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->paymentSchedules()
                    ->where('status', 'paid')
                    ->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->total_paid;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date?->isPast() ?? false;
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return (int) now()->diffInDays($this->end_date, false);
    }

    public function getRequiresBnaRegistrationAttribute(): bool
    {
        return $this->currency !== Currency::AOA;
    }

    // ─── Progress ───────────────────────────────────────────

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ContractProgressUpdate::class)->orderByDesc('created_at');
    }

    public function calculateTimeBasedProgress(): float
{
    if (!$this->start_date || !$this->end_date) {
        return 0.0;
    }

    $start = $this->start_date->getTimestamp();
    $end = $this->end_date->getTimestamp();
    $now = now()->getTimestamp();

    if ($now < $start) return 0.0;
    if ($now > $end) return 100.0;

    return round((($now - $start) / ($end - $start)) * 100, 2);
}

public function pacNeed(): BelongsTo
{
    return $this->belongsTo(\App\Domain\PAC\Models\PlanNeed::class, 'pac_need_id');
}

    // ─── Audit Logging ───────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Contrato {$this->contract_number} foi {$eventName}");
    }
}
