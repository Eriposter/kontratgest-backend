<?php

declare(strict_types=1);

namespace App\Domain\Contracts\Services;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Models\ContractType;
use App\Domain\Contracts\Models\PaymentSchedule;
use App\Support\Enums\ContractStatus;
use App\Support\Enums\Currency;
use App\Support\Enums\PaymentModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContractService
{
    /**
     * Listar contratos com filtros.
     */
    public function list(
        ?string $typeCode = null,
        ?string $status = null,
        ?string $search = null,
        ?string $counterpartyId = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Contract::query()
            ->with(['type', 'counterparty', 'paymentSchedules'])
            ->orderByDesc('created_at');

        if ($typeCode) {
            $query->ofType($typeCode);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($counterpartyId) {
            $query->where('counterparty_id', $counterpartyId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Criar novo contrato.
     */
    public function create(array $data): Contract
    {
        return DB::transaction(function () use ($data) {
            // Gerar número de contrato automático
            if (empty($data['contract_number'])) {
                $data['contract_number'] = $this->generateContractNumber($data['contract_type_id']);
            }

            // Calcular taxa de câmbio se necessário
            if (isset($data['currency']) && $data['currency'] !== Currency::AOA) {
                $data['requires_bna_registration'] = true;
                // Aqui podes integrar com API do BNA para taxa oficial
                // $data['exchange_rate'] = $this->getBnaExchangeRate($data['currency']);
            }

            $contract = Contract::create($data);

            // Criar plano de pagamentos se fornecido
            if (!empty($data['payment_schedules'])) {
                $this->createPaymentSchedules($contract, $data['payment_schedules']);
            }

            return $contract->load(['type', 'counterparty', 'paymentSchedules']);
        });
    }

    /**
     * Atualizar contrato.
     */
    public function update(Contract $contract, array $data): Contract
    {
        return DB::transaction(function () use ($contract, $data) {
            $contract->update($data);

            // Atualizar plano de pagamentos se fornecido
            if (isset($data['payment_schedules'])) {
                $contract->paymentSchedules()->delete();
                $this->createPaymentSchedules($contract, $data['payment_schedules']);
            }

            return $contract->fresh(['type', 'counterparty', 'paymentSchedules']);
        });
    }

    /**
     * Submeter contrato para aprovação.
     */
    public function submitForApproval(Contract $contract): Contract
    {
        if (!$contract->status->canTransitionTo(ContractStatus::PENDING_APPROVAL)) {
            throw new \InvalidArgumentException('Contrato não pode ser submetido para aprovação neste estado.');
        }

        $contract->update(['status' => ContractStatus::PENDING_APPROVAL]);

        // Aqui podes disparar notificações para aprovadores
        // event(new ContractSubmittedForApproval($contract));

        return $contract;
    }

    /**
     * Aprovar contrato.
     */
    public function approve(Contract $contract, string $approvedBy): Contract
    {
        if (!$contract->status->canTransitionTo(ContractStatus::APPROVED)) {
            throw new \InvalidArgumentException('Contrato não pode ser aprovado neste estado.');
        }

        $contract->update([
            'status' => ContractStatus::APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $contract;
    }

    /**
     * Ativar contrato (após assinatura).
     */
    public function activate(Contract $contract): Contract
    {
        if (!$contract->status->canTransitionTo(ContractStatus::ACTIVE)) {
            throw new \InvalidArgumentException('Contrato não pode ser ativado neste estado.');
        }

        $contract->update([
            'status' => ContractStatus::ACTIVE,
            'signature_date' => $contract->signature_date ?? now(),
        ]);

        return $contract;
    }

    /**
     * Suspender contrato.
     */
    public function suspend(Contract $contract, string $reason = ''): Contract
    {
        if (!$contract->status->canTransitionTo(ContractStatus::SUSPENDED)) {
            throw new \InvalidArgumentException('Contrato não pode ser suspenso neste estado.');
        }

        $contract->update([
            'status' => ContractStatus::SUSPENDED,
            'internal_notes' => trim($contract->internal_notes . "\n[Suspenso em " . now()->format('d/m/Y') . "] {$reason}"),
        ]);

        return $contract;
    }

    /**
     * Rescindir contrato.
     */
    public function terminate(Contract $contract, string $reason): Contract
    {
        if (!$contract->status->canTransitionTo(ContractStatus::TERMINATED)) {
            throw new \InvalidArgumentException('Contrato não pode ser rescindido neste estado.');
        }

        $contract->update([
            'status' => ContractStatus::TERMINATED,
            'internal_notes' => trim($contract->internal_notes . "\n[Rescindido em " . now()->format('d/m/Y') . "] {$reason}"),
        ]);

        return $contract;
    }

    /**
     * Registar contrato no BNA (para moeda estrangeira).
     */
    public function registerAtBna(Contract $contract, string $registrationNumber): Contract
    {
        $contract->update([
            'bna_registration_number' => $registrationNumber,
            'bna_registration_date' => now(),
        ]);

        return $contract;
    }

    /**
     * Obter contratos a expirar.
     */
    public function getExpiringContracts(int $days = 30): Collection
    {
        return Contract::active()
            ->expiringIn($days)
            ->with(['type', 'counterparty'])
            ->get();
    }

    /**
     * Obter contratos em atraso.
     */
    public function getOverdueContracts(): Collection
    {
        return Contract::overdue()
            ->with(['type', 'counterparty'])
            ->get();
    }

    /**
     * Gerar número de contrato automático.
     */
    private function generateContractNumber(string $contractTypeId): string
    {
        $type = ContractType::find($contractTypeId);
        $prefix = strtoupper(Str::substr($type->code, 0, 3));
        $year = date('Y');

        // Contar contratos deste tipo neste ano
        $count = Contract::where('contract_type_id', $contractTypeId)
            ->whereYear('created_at', $year)
            ->count();

        return sprintf('%s/%s/%05d', $prefix, $year, $count + 1);
    }

    /**
     * Criar plano de pagamentos.
     */
    private function createPaymentSchedules(Contract $contract, array $schedules): void
    {
        foreach ($schedules as $index => $schedule) {
            PaymentSchedule::create([
                'contract_id' => $contract->id,
                'milestone_name' => $schedule['milestone_name'],
                'sequence_order' => $index + 1,
                'due_date' => $schedule['due_date'] ?? null,
                'percentage' => $schedule['percentage'] ?? null,
                'amount' => $schedule['amount'] ?? null,
                'is_conditional' => $schedule['is_conditional'] ?? false,
                'condition_type' => $schedule['condition_type'] ?? null,
                'condition_description' => $schedule['condition_description'] ?? null,
            ]);
        }
    }
}