<?php

declare(strict_types=1);

namespace App\Domain\PAC\Models;

use App\Domain\Contracts\Models\Contract;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanNeed extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'plan_id',
        'contract_type',
        'procedure_type',
        'title',
        'description',
        'justification',
        'estimated_amount',
        'executed_amount',
        'contract_id',
        'priority',
        'planned_quarter',
        'status',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'executed_amount' => 'decimal:2',
        'planned_quarter' => 'integer',
    ];

   public function plan(): BelongsTo
{
    return $this->belongsTo(AnnualContractPlan::class, 'plan_id');
}

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    // Mutators
    public function getContractTypeLabelAttribute(): string
    {
        $labels = [
            'works' => 'Empreitada',
            'goods' => 'Aquisição de Bens Móveis',
            'services' => 'Prestação de Serviços',
            'consultancy' => 'Consultoria',
        ];
        return $labels[$this->contract_type] ?? $this->contract_type;
    }

    public function getProcedureTypeLabelAttribute(): string
    {
        $labels = [
            'dynamic_electronic' => 'Dinâmico Eletrónico',
            'invitation' => 'Convite',
            'limited_tender' => 'Concurso Limitado',
            'direct_award' => 'Ajuste Direto',
        ];
        return $labels[$this->procedure_type] ?? $this->procedure_type;
    }

    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            'high' => 'Alta',
            'medium' => 'Média',
            'low' => 'Baixa',
        ];
        return $labels[$this->priority] ?? $this->priority;
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'planned' => 'Planeada',
            'in_progress' => 'Em Curso',
            'contracted' => 'Contratada',
            'cancelled' => 'Cancelada',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}