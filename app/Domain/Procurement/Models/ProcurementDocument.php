<?php

namespace App\Domain\Procurement\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'procedure_id', 'phase_id', 'document_type', 'title', 'file_name',
        'file_path', 'mime_type', 'file_size', 'issued_at', 'expires_at', 'uploaded_by',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(ProcurementProcedure::class, 'procedure_id');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProcurementPhase::class, 'phase_id');
    }
}