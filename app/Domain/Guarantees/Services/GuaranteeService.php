<?php

declare(strict_types=1);

namespace App\Domain\Guarantees\Services;

use App\Domain\Guarantees\Models\Guarantee;
use App\Support\Enums\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuaranteeService
{
    /**
     * Listar cauções com filtros.
     */
    public function list(
        ?string $contractId = null,
        ?string $status = null,
        ?string $type = null,
        ?string $purpose = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Guarantee::query()
            ->with(['contract.counterparty', 'documents'])
            ->orderByDesc('created_at');

        if ($contractId) {
            $query->where('contract_id', $contractId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->ofType($type);
        }

        if ($purpose) {
            $query->forPurpose($purpose);
        }

        return $query->paginate($perPage);
    }

    /**
     * Criar nova caução.
     */
    public function create(array $data): Guarantee
    {
        return DB::transaction(function () use ($data) {
            // Gerar número de caução automático
            if (empty($data['guarantee_number'])) {
                $data['guarantee_number'] = $this->generateGuaranteeNumber();
            }

            // Calcular taxa de câmbio se necessário
            if (isset($data['currency']) && $data['currency'] !== Currency::AOA) {
                // Aqui podes integrar com API do BNA
                // $data['exchange_rate'] = $this->getBnaExchangeRate($data['currency']);
            }

            $guarantee = Guarantee::create($data);

            return $guarantee->load(['contract.counterparty', 'documents']);
        });
    }

    /**
     * Atualizar caução.
     */
    public function update(Guarantee $guarantee, array $data): Guarantee
    {
        $guarantee->update($data);

        return $guarantee->fresh(['contract.counterparty', 'documents']);
    }

    /**
     * Libertar caução.
     */
    public function release(Guarantee $guarantee, string $releasedBy, string $notes = ''): Guarantee
    {
        if (!$guarantee->can_release) {
            throw new \InvalidArgumentException('Caução não pode ser libertada neste estado.');
        }

        $guarantee->update([
            'status' => 'released',
            'release_date' => now(),
            'released_by' => $releasedBy,
            'release_notes' => $notes,
        ]);

        return $guarantee;
    }

    /**
     * Executar caução (quando há incumprimento).
     */
    public function execute(Guarantee $guarantee, float $amount, string $reason): Guarantee
    {
        if (!$guarantee->can_execute) {
            throw new \InvalidArgumentException('Caução não pode ser executada neste estado.');
        }

        if ($amount > $guarantee->amount) {
            throw new \InvalidArgumentException('Valor executado não pode exceder o valor da caução.');
        }

        $guarantee->update([
            'status' => 'executed',
            'was_executed' => true,
            'executed_amount' => $amount,
            'executed_at' => now(),
            'execution_reason' => $reason,
        ]);

        return $guarantee;
    }

    /**
     * Cancelar caução.
     */
    public function cancel(Guarantee $guarantee, string $reason = ''): Guarantee
    {
        if ($guarantee->status !== 'active') {
            throw new \InvalidArgumentException('Apenas cauções ativas podem ser canceladas.');
        }

        $guarantee->update([
            'status' => 'cancelled',
            'notes' => trim($guarantee->notes . "\n[Cancelada em " . now()->format('d/m/Y') . "] {$reason}"),
        ]);

        return $guarantee;
    }

    /**
     * Obter cauções a expirar.
     */
    public function getExpiringGuarantees(int $days = 30): Collection
    {
        return Guarantee::active()
            ->expiringIn($days)
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Obter cauções expiradas (que ainda estão marcadas como active).
     */
    public function getExpiredGuarantees(): Collection
    {
        return Guarantee::expired()
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Atualizar automaticamente cauções expiradas.
     */
    public function markExpiredGuarantees(): int
    {
        return Guarantee::where('status', 'active')
            ->where('expiry_date', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Resumo de cauções por contrato.
     */
    public function getSummaryByContract(string $contractId): array
    {
        $guarantees = Guarantee::where('contract_id', $contractId)->get();

        return [
            'total' => $guarantees->count(),
            'active' => $guarantees->where('status', 'active')->count(),
            'released' => $guarantees->where('status', 'released')->count(),
            'expired' => $guarantees->where('status', 'expired')->count(),
            'executed' => $guarantees->where('status', 'executed')->count(),
            'total_amount' => $guarantees->where('status', 'active')->sum('amount'),
            'expiring_soon' => $guarantees->filter(fn ($g) => $g->is_expiring_soon)->count(),
        ];
    }

    /**
     * Gerar número de caução automático.
     */
    private function generateGuaranteeNumber(): string
    {
        $year = date('Y');
        $count = Guarantee::whereYear('created_at', $year)->count();

        return sprintf('GB/%s/%05d', $year, $count + 1);
    }
}