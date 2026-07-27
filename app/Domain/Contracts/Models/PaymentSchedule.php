<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSchedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_id',
        'milestone_name',
        'sequence_order',
        'due_date',
        'percentage',
        'amount',
        'is_conditional',
        'condition_type',
        'condition_description',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'due_date' => 'date',
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'is_conditional' => 'boolean',
        'paid_at' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function getCalculatedAmountAttribute(): float
    {
        if ($this->amount) {
            return (float) $this->amount;
        }

        if ($this->percentage) {
            return $this->contract->total_amount * ($this->percentage / 100);
        }

        return 0.0;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date?->isPast() && $this->status === 'pending';
    }
}