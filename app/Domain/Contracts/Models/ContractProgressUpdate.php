<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractProgressUpdate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_id',
        'progress_percentage',
        'update_type',
        'notes',
        'evidence',
        'updated_by',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'evidence' => 'array',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}