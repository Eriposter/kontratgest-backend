<?php

declare(strict_types=1);

namespace App\Domain\Entities\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'entity_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'issued_at',
        'expires_at',
        'is_current',
        'uploaded_by',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'is_current' => 'boolean',
        'file_size' => 'integer',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) return false;
        return $this->expires_at->isPast();
    }
}