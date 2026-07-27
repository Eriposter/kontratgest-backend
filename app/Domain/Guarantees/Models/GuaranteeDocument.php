<?php

declare(strict_types=1);

namespace App\Domain\Guarantees\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuaranteeDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'guarantee_id',
        'document_type',
        'title',
        'description',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'issued_at',
        'expires_at',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    public function guarantee(): BelongsTo
    {
        return $this->belongsTo(Guarantee::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }
}