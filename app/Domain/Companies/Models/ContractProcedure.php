<?php

declare(strict_types=1);

namespace App\Domain\Companies\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractProcedure extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'procedure_number',
        'procedure_type',
        'title',
        'description',
        'legal_basis',
        'justification',
        'estimated_value',
        'currency',
        'publication_date',
        'proposal_deadline',
        'adjudication_date',
        'status',
        'participants',
        'evaluation_criteria',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'publication_date' => 'date',
        'proposal_deadline' => 'date',
        'adjudication_date' => 'date',
        'participants' => 'array',
        'evaluation_criteria' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(\App\Domain\Contracts\Models\Contract::class, 'procedure_id');
    }
}