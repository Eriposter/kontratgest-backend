<?php

namespace App\Domain\Procurement\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementPhase extends Model
{
    use HasUuids;

    protected $fillable = [
        'procedure_id', 'phase_name', 'sequence_order', 'status',
        'start_date', 'end_date', 'documents', 'notes', 'observations',
        'completed_by', 'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'documents' => 'array',
        'completed_at' => 'datetime',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(ProcurementProcedure::class, 'procedure_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'ongoing' => 'Em Curso',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
            default => $this->status,
        };
    }
}