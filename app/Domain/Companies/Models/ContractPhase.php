<?php

declare(strict_types=1);

namespace App\Domain\Companies\Models;

use App\Domain\Contracts\Models\Contract;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractPhase extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_id',
        'phase_type',
        'phase_name',
        'description',
        'status',
        'start_date',
        'end_date',
        'deadline',
        'visto_number',
        'visto_date',
        'visto_observations',
        'publication_number',
        'publication_date',
        'diary_series',
        'receipt_date',
        'receipt_observations',
        'receipt_defects',
        'responsible_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'date',
        'visto_date' => 'date',
        'publication_date' => 'date',
        'receipt_date' => 'date',
        'receipt_defects' => 'array',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}