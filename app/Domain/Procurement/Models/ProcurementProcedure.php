<?php

namespace App\Domain\Procurement\Models;

use App\Domain\Contracts\Models\Contract; 
use App\Domain\Entities\Models\Entity;
use App\Domain\PAC\Models\PlanNeed;
use App\Models\User;
use App\Support\Enums\ProcedureStatus;
use App\Support\Enums\ProcedureType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementProcedure extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'plan_need_id', 'contract_id', 'winning_entity_id', 'procedure_type',
        'procedure_start_date', 'procedure_end_date', 'confirmed_start_date',
        'confirmed_end_date', 'status', 'estimated_amount', 'contracted_amount',
        'documents', 'notes', 'adjudication_notes', 'created_by', 'completed_by', 'completed_at',
    ];

    protected $casts = [
        'procedure_type' => ProcedureType::class,
        'status' => ProcedureStatus::class,
        'procedure_start_date' => 'date',
        'procedure_end_date' => 'date',
        'confirmed_start_date' => 'date',
        'confirmed_end_date' => 'date',
        'documents' => 'array',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function need(): BelongsTo
    {
        return $this->belongsTo(PlanNeed::class, 'plan_need_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function winningEntity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'winning_entity_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProcurementPhase::class, 'procedure_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProcurementDocument::class, 'procedure_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ProcurementCandidate::class, 'procedure_id');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getProcedureTypeLabelAttribute(): string
    {
        return $this->procedure_type->label();
    }

    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->procedure_start_date || !$this->procedure_end_date) {
            return null;
        }
        return (int) $this->procedure_start_date->diffInDays($this->procedure_end_date, false);
    }

    public function getCompletedPhasesCountAttribute(): int
    {
        return $this->phases()->where('status', 'completed')->count();
    }

    public function getTotalPhasesCountAttribute(): int
    {
        return $this->phases()->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        $total = $this->total_phases_count;
        if ($total === 0) return 0;
        return round(($this->completed_phases_count / $total) * 100, 2);
    }
}