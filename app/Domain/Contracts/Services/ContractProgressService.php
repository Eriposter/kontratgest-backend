<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Models\ContractProgressUpdate;
use Illuminate\Support\Collection;

class ContractProgressService
{
    /**
     * Atualizar progresso manualmente.
     */
    public function updateProgress(
        Contract $contract,
        float $percentage,
        string $notes = '',
        array $evidence = [],
        ?string $updatedBy = null,
        string $type = 'manual'
    ): ContractProgressUpdate {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('O progresso deve estar entre 0% e 100%.');
        }

        $update = ContractProgressUpdate::create([
            'contract_id' => $contract->id,
            'progress_percentage' => $percentage,
            'update_type' => $type,
            'notes' => $notes,
            'evidence' => $evidence,
            'updated_by' => $updatedBy,
        ]);

        // Atualizar o contrato
        $contract->update([
            'current_progress' => $percentage,
            'progress_last_updated_at' => now(),
        ]);

        return $update;
    }

    /**
     * Calcular progresso automático baseado em pagamentos.
     */
    public function calculateAutomaticProgress(Contract $contract): float
    {
        $totalAmount = (float) $contract->total_amount;
        
        if ($totalAmount <= 0) {
            return 0.0;
        }

        // Calcular baseado em pagamentos aprovados/pagos
        $totalPaid = $contract->paymentSchedules()
            ->whereIn('status', ['paid'])
            ->sum('amount');

        $progress = ($totalPaid / $totalAmount) * 100;

        return min(100.0, max(0.0, $progress));
    }

    /**
     * Obter histórico de atualizações de progresso.
     */
    public function getProgressHistory(Contract $contract): Collection
    {
        return ContractProgressUpdate::where('contract_id', $contract->id)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Obter progresso atual com metadados.
     */
    public function getCurrentProgress(Contract $contract): array
    {
        $lastUpdate = ContractProgressUpdate::where('contract_id', $contract->id)
            ->orderByDesc('created_at')
            ->first();

        return [
            'current_progress' => (float) $contract->current_progress,
            'time_based_progress' => $this->calculateTimeBasedProgress($contract),
            'payment_based_progress' => $this->calculateAutomaticProgress($contract),
            'last_update' => $lastUpdate ? [
                'percentage' => (float) $lastUpdate->progress_percentage,
                'type' => $lastUpdate->update_type,
                'notes' => $lastUpdate->notes,
                'updated_at' => $lastUpdate->created_at->toISOString(),
                'updated_by' => $lastUpdate->updated_by,
            ] : null,
        ];
    }

    /**
     * Calcular progresso baseado no tempo decorrido.
     */
    private function calculateTimeBasedProgress(Contract $contract): float
    {
        if (!$contract->start_date || !$contract->end_date) {
            return 0.0;
        }

        $start = $contract->start_date->getTimestamp();
        $end = $contract->end_date->getTimestamp();
        $now = now()->getTimestamp();

        if ($now < $start) return 0.0;
        if ($now > $end) return 100.0;

        return round((($now - $start) / ($end - $start)) * 100, 2);
    }
}