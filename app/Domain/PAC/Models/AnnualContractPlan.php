<?php

declare(strict_types=1);

namespace App\Domain\PAC\Models;

use App\Domain\Companies\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualContractPlan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'year',
        'title',
        'description',
        'total_planned_amount',
        'total_executed_amount',
        'status',
        'approved_by',
        'approved_at',
        'created_by', 
    ];

    protected $casts = [
        'year' => 'integer',
        'total_planned_amount' => 'decimal:2',
        'total_executed_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function needs(): HasMany
{
    return $this->hasMany(PlanNeed::class, 'plan_id');
}

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Mutators
    public function getExecutionPercentageAttribute(): float
    {
        if ($this->total_planned_amount <= 0) return 0;
        return round(($this->total_executed_amount / $this->total_planned_amount) * 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => 'Rascunho',
            'submitted' => 'Submetido',
            'approved' => 'Aprovado',
            'in_progress' => 'Em Execução',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}