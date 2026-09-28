<?php

declare(strict_types=1);

namespace App\Domain\PAC\Models;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Entities\Models\Entity;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractingProcedure extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'plan_need_id',
        'contract_id',
        'winning_entity_id',
        'procedure_start_date',
        'procedure_end_date',
        'confirmed_start_date',
        'confirmed_end_date',
        'status',
        'documents',
        'notes',
        'adjudication_notes',
        'created_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'procedure_start_date' => 'date',
        'procedure_end_date' => 'date',
        'confirmed_start_date' => 'date',
        'confirmed_end_date' => 'date',
        'documents' => 'array',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────

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

    // ─── Status Helpers ────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planned' => 'Planeado',
            'in_progress' => 'Em Curso',
            'evaluation' => 'Em Avaliação',
            'completed' => 'Finalizado',
            'cancelled' => 'Cancelado',
            'failed' => 'Deserto',
            default => $this->status,
        };
    }

    public function getCanBeCompletedAttribute(): bool
    {
        return in_array($this->status, ['in_progress', 'evaluation']);
    }

    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->procedure_start_date || !$this->procedure_end_date) {
            return null;
        }
        return (int) $this->procedure_start_date->diffInDays($this->procedure_end_date, false);
    }
}