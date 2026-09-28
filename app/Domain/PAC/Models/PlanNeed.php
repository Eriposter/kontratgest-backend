<?php

declare(strict_types=1);

namespace App\Domain\PAC\Models;

use App\Domain\Contracts\Models\Contract;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\PAC\Models\ContractingProcedure;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    'priority',
    'planned_quarter',
    'proposed_start_date', // ← ADICIONAR
    'proposed_end_date',   // ← ADICIONAR
    'status',
    'contract_id',
];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'executed_amount' => 'decimal:2',
        'planned_quarter' => 'integer',
        'procedure_start_date' => 'date',
        'procedure_end_date' => 'date',
    ];

   public function plan(): BelongsTo
{
    return $this->belongsTo(AnnualContractPlan::class, 'plan_id');
}

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    // ─── Mutators / Acessores ──────────────────────────────────

public function getContractTypeLabelAttribute(): string
{
    $labels = [
        'public_works' => 'Empreitada de obras públicas',
        'goods_acquisition' => 'Aquisição de bens móveis',
        'services_acquisition' => 'Aquisição de serviços',
        'consultancy' => 'Serviços de consultoria',
        'goods_rental' => 'Locação de bens móveis',
        'public_works_concession' => 'Concessão de obras públicas',
        'public_services_concession' => 'Concessão de serviços públicos',
        'other' => 'Outro',
    ];
    
    return $labels[$this->contract_type] ?? $this->contract_type;
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

        // ─── Mutators / Acessores ──────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'planned' => 'Planeada',
            'in_progress' => 'Em Curso',
            'contracted' => 'Contratada',
            'cancelled' => 'Cancelada',
        ];

        // Previne que um status null quebre a aplicação
        $status = $this->status ?? 'unknown';
        
        return $labels[$status] ?? 'Desconhecido';
    }

    public function getProcedureTypeLabelAttribute(): string
    {
        $labels = [
            'cp' => 'Concurso Público',
            'clpq' => 'Concurso Limitado por Prévia Qualificação',
            'clc' => 'Concurso Limitado por Convite',
            'cs' => 'Contratação Simplificada',
            'cde' => 'Procedimento Dinâmico Electrónico',
            'pce' => 'Procedimento de Contratação Emergencial',
        ];

        // Previne que um tipo null quebre a aplicação
        $type = $this->procedure_type ?? 'unknown';
        
        return $labels[$type] ?? 'Desconhecido';
    }

public function contractingProcedure(): HasOne
{
    return $this->hasOne(ContractingProcedure::class, 'plan_need_id');
}
}