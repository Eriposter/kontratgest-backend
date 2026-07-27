<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_id',
        'document_type',
        'title',
        'description',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'version',
        'is_current',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'is_current' => 'boolean',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}