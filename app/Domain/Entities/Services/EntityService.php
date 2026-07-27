<?php

declare(strict_types=1);

namespace App\Domain\Entities\Services;

use App\Domain\Entities\Models\Entity;
use App\Support\Enums\EntityType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EntityService
{
    /**
     * Listar entidades com filtros e paginação.
     */
    public function list(
        ?EntityType $type = null,
        ?string $search = null,
        ?string $status = 'active',
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Entity::query()
            ->with('documents')
            ->orderByDesc('created_at');

        if ($type) {
            $query->ofType($type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nif', 'like', "%{$search}%")
                  ->orWhere('legal_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Criar uma nova entidade.
     */
    public function create(array $data): Entity
    {
        // Garantir que apenas um bank_account tem is_default = true
        if (!empty($data['bank_accounts'])) {
            $data['bank_accounts'] = $this->normalizeBankAccounts($data['bank_accounts']);
        }

        return Entity::create($data);
    }

    /**
     * Atualizar uma entidade.
     */
    public function update(Entity $entity, array $data): Entity
    {
        if (!empty($data['bank_accounts'])) {
            $data['bank_accounts'] = $this->normalizeBankAccounts($data['bank_accounts']);
        }

        $entity->update($data);

        return $entity->fresh();
    }

    /**
     * Suspender entidade (ex: certidões expiradas).
     */
    public function suspend(Entity $entity, string $reason = ''): Entity
    {
        $entity->update([
            'status' => 'suspended',
            'notes' => trim($entity->notes . "\n[Suspenso em " . now()->format('d/m/Y') . "] {$reason}"),
        ]);

        return $entity;
    }

    /**
     * Verificar entidades com certificados a expirar.
     */
    public function getEntitiesWithExpiringCertificates(int $days = 30): Collection
    {
        return Entity::active()
            ->withCertificatesExpiringIn($days)
            ->get();
    }

    /**
     * Normalizar contas bancárias (garantir apenas 1 default).
     */
    private function normalizeBankAccounts(array $accounts): array
    {
        // Se nenhuma conta estiver marcada como default, a primeira será
        $hasDefault = collect($accounts)->contains('is_default', true);

        return array_map(function ($account, $index) use ($hasDefault) {
            $account['is_default'] = $hasDefault
                ? ($account['is_default'] ?? false)
                : ($index === 0);

            return $account;
        }, $accounts, array_keys($accounts));
    }
}