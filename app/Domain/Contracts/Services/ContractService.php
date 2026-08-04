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
    // 1. Extrair os planos de pagamento dos dados
    $paymentSchedules = $data['payment_schedules'] ?? [];
    unset($data['payment_schedules']); // ← REMOVE do array para evitar MassAssignmentException

    // 2. Definir valores padrão
    $data['created_by'] = auth()->id();
    $data['company_id'] = current_company()->id;
    
    // Define o status inicial (ajusta conforme o teu Enum, ex: ContractStatus::DRAFT)
    $data['status'] = \App\Support\Enums\ContractStatus::DRAFT; 

    // 3. Gerar número do contrato automaticamente se não for enviado
    if (empty($data['contract_number'])) {
        $year = date('Y');
        $count = Contract::whereYear('created_at', $year)->count() + 1;
        $data['contract_number'] = "CT/{$year}/" . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    // 4. Criar o contrato (agora sem o payment_schedules, não dará erro)
    $contract = Contract::create($data);

    // 5. Criar os planos de pagamento associados
    if (!empty($paymentSchedules)) {
        foreach ($paymentSchedules as $index => $schedule) {
            $contract->paymentSchedules()->create([
                'sequence_order' => $index + 1,
                'milestone_name' => $schedule['milestone_name'],
                'due_date' => $schedule['due_date'] ?? null,
                'percentage' => $schedule['percentage'] ?? 0,
                'amount' => $schedule['amount'] ?? 0,
                'is_conditional' => $schedule['is_conditional'] ?? false,
                'status' => 'pending', // Ajusta ao status padrão da tua tabela payment_schedules
            ]);
        }
    }

    return $contract->load('paymentSchedules', 'type', 'counterparty');
}

    /**
     * Atualizar contrato.
     */
    public function update(Contract $contract, array $data): Contract
{
    // 1. Extrair os planos de pagamento
    $paymentSchedules = $data['payment_schedules'] ?? null;
    unset($data['payment_schedules']); // ← REMOVE do array

    // 2. Atualizar os dados principais do contrato
    $contract->update($data);

    // 3. Sincronizar os planos de pagamento (apaga os antigos e cria os novos)
    if ($paymentSchedules !== null) {
        $contract->paymentSchedules()->delete(); // Limpa os antigos
        
        foreach ($paymentSchedules as $index => $schedule) {
            $contract->paymentSchedules()->create([
                'sequence_order' => $index + 1,
                'milestone_name' => $schedule['milestone_name'],
                'due_date' => $schedule['due_date'] ?? null,
                'percentage' => $schedule['percentage'] ?? 0,
                'amount' => $schedule['amount'] ?? 0,
                'is_conditional' => $schedule['is_conditional'] ?? false,
                'status' => 'pending',
            ]);
        }
    }

    return $contract->fresh()->load('paymentSchedules', 'type', 'counterparty');
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

    private function syncPACNeedStatus(Contract $contract): void
    {
        if (!$contract->pac_need_id) return;

        $need = PlanNeed::find($contract->pac_need_id);
        if (!$need) return;

        // Mapear estado do contrato para estado da necessidade
        $statusMap = [
            'draft' => 'contracted',
            'pending_approval' => 'contracted',
            'approved' => 'contracted',
            'active' => 'contracted',
            'suspended' => 'suspended',
            'terminated' => 'cancelled',
            'expired' => 'completed',
        ];

        $newNeedStatus = $statusMap[$contract->status] ?? 'contracted';

        $need->update([
            'status' => $newNeedStatus,
            'executed_amount' => $contract->total_amount,
        ]);

        // Recalcular totais do PAC
        $plan = $need->plan;
        $plan->update([
            'total_executed_amount' => $plan->needs()
                ->whereNotNull('executed_amount')
                ->sum('executed_amount'),
        ]);
    }

    public function approve(Contract $contract, string $approvedBy): Contract
    {
        $contract->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        // Sincronizar com PAC
        $this->syncPACNeedStatus($contract);

        return $contract;
    }

    /**
     * Ativar contrato (com sincronização PAC)
     */
    public function activate(Contract $contract): Contract
    {
        $contract->update([
            'status' => 'active',
        ]);

        // Sincronizar com PAC
        $this->syncPACNeedStatus($contract);

        return $contract;
    }

    /**
     * Suspender contrato (com sincronização PAC)
     */
    public function suspend(Contract $contract, string $reason): Contract
    {
        $contract->update([
            'status' => 'suspended',
        ]);

        // Sincronizar com PAC
        $this->syncPACNeedStatus($contract);

        return $contract;
    }

    /**
     * Rescindir contrato (com sincronização PAC)
     */
    public function terminate(Contract $contract, string $reason): Contract
    {
        $contract->update([
            'status' => 'terminated',
        ]);

        // Sincronizar com PAC
        $this->syncPACNeedStatus($contract);

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