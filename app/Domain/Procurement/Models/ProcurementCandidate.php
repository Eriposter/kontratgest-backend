<?php

namespace App\Domain\Procurement\Models;

use App\Domain\Entities\Models\Entity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementCandidate extends Model
{
    use HasUuids;

    protected $fillable = [
        'procedure_id', 'entity_id', 'status', 'proposed_amount',
        'technical_score', 'financial_score', 'total_score', 'notes',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(ProcurementProcedure::class, 'procedure_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'qualified' => 'Qualificado',
            'disqualified' => 'Desqualificado',
            'winner' => 'Vencedor',
            'runner_up' => 'Segundo Classificado',
            default => $this->status,
        };
    }
}