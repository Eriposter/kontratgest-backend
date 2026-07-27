<?php

declare(strict_types=1);

namespace App\Domain\Payments\Services;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\MeasurementItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeasurementService
{
    /**
     * Listar autos de medição.
     */
    public function list(
        ?string $contractId = null,
        ?string $status = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Measurement::query()
            ->with(['contract.counterparty', 'items'])
            ->orderByDesc('created_at');

        if ($contractId) {
            $query->where('contract_id', $contractId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Criar novo auto de medição.
     */
    public function create(array $data): Measurement
    {
        return DB::transaction(function () use ($data) {
            $contract = Contract::findOrFail($data['contract_id']);

            // Gerar número automático
            if (empty($data['measurement_number'])) {
                $data['measurement_number'] = $this->generateMeasurementNumber($contract);
            }

            // Calcular sequência
            $data['sequence_number'] = Measurement::where('contract_id', $contract->id)->count() + 1;

            // Calcular acumulado
            $previousCumulative = Measurement::where('contract_id', $contract->id)
                ->approved()
                ->sum('total_amount');

            $data['cumulative_amount'] = $previousCumulative + $data['total_amount'];

            // Calcular retenção
            if (isset($data['retention_percentage']) && $data['retention_percentage'] > 0) {
                $data['retention_amount'] = $data['total_amount'] * ($data['retention_percentage'] / 100);
            }

            $measurement = Measurement::create($data);

            // Criar itens
            if (!empty($data['items'])) {
                $this->createItems($measurement, $data['items']);
            }

            return $measurement->load(['contract.counterparty', 'items']);
        });
    }

    /**
     * Atualizar auto de medição.
     */
    public function update(Measurement $measurement, array $data): Measurement
    {
        return DB::transaction(function () use ($measurement, $data) {
            // Recalcular total se itens foram alterados
            if (isset($data['items'])) {
                $measurement->items()->delete();
                $this->createItems($measurement, $data['items']);
                
                $data['total_amount'] = $measurement->items()->sum('total_amount');
            }

            // Recalcular acumulado
            $previousCumulative = Measurement::where('contract_id', $measurement->contract_id)
                ->where('sequence_number', '<', $measurement->sequence_number)
                ->approved()
                ->sum('total_amount');

            $data['cumulative_amount'] = $previousCumulative + $data['total_amount'];

            // Recalcular retenção
            if (isset($data['retention_percentage'])) {
                $data['retention_amount'] = $data['total_amount'] * ($data['retention_percentage'] / 100);
            }

            $measurement->update($data);

            return $measurement->fresh(['contract.counterparty', 'items']);
        });
    }

    /**
     * Submeter auto de medição para aprovação.
     */
    public function submit(Measurement $measurement, string $submittedBy): Measurement
    {
        if (!$measurement->can_be_submitted) {
            throw new \InvalidArgumentException('Auto de medição não pode ser submetido neste estado.');
        }

        $measurement->update([
            'status' => 'submitted',
            'submitted_by' => $submittedBy,
            'submitted_at' => now(),
        ]);

        return $measurement;
    }

    /**
     * Aprovar auto de medição.
     */
    public function approve(Measurement $measurement, string $approvedBy, string $notes = ''): Measurement
    {
        if (!$measurement->can_be_approved) {
            throw new \InvalidArgumentException('Auto de medição não pode ser aprovado neste estado.');
        }

        $measurement->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        return $measurement;
    }

    /**
     * Rejeitar auto de medição.
     */
    public function reject(Measurement $measurement, string $notes): Measurement
    {
        if ($measurement->status !== 'submitted') {
            throw new \InvalidArgumentException('Apenas autos submetidos podem ser rejeitados.');
        }

        $measurement->update([
            'status' => 'rejected',
            'approval_notes' => $notes,
        ]);

        return $measurement;
    }

    /**
     * Marcar como pago (quando pagamento é processado).
     */
    public function markAsPaid(Measurement $measurement, string $paymentId): Measurement
    {
        $measurement->update([
            'status' => 'paid',
            'payment_id' => $paymentId,
            'paid_at' => now(),
        ]);

        return $measurement;
    }

    /**
     * Obter autos pendentes de aprovação.
     */
    public function getPendingMeasurements(): Collection
    {
        return Measurement::pending()
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Obter autos aprovados mas não pagos.
     */
    public function getApprovedButUnpaidMeasurements(): Collection
    {
        return Measurement::approved()
            ->whereNull('payment_id')
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Gerar número de auto de medição.
     */
    private function generateMeasurementNumber(Contract $contract): string
    {
        $year = date('Y');
        $count = Measurement::whereYear('created_at', $year)->count();

        return sprintf('AM/%s/%05d', $year, $count + 1);
    }

    /**
     * Criar itens do auto de medição.
     */
    private function createItems(Measurement $measurement, array $items): void
    {
        foreach ($items as $item) {
            // Calcular total automaticamente
            $totalAmount = $item['quantity'] * $item['unit_price'];

            MeasurementItem::create([
                'measurement_id' => $measurement->id,
                'item_code' => $item['item_code'] ?? null,
                'description' => $item['description'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_amount' => $totalAmount,
                'specific_data' => $item['specific_data'] ?? [],
            ]);
        }
    }
}