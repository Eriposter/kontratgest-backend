<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Listar utilizadores.
     */
    public function list(
        ?string $search = null,
        ?string $department = null,
        ?bool $activeOnly = true,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = User::query()
            ->with(['roles'])
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        if ($activeOnly) {
            $query->active();
        }

        return $query->paginate($perPage);
    }

    /**
     * Criar utilizador.
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'department' => $data['department'] ?? null,
                'position' => $data['position'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Atribuir roles
            if (!empty($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            // Atribuir permissões diretas
            if (!empty($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            return $user->load(['roles.permissions']);
        });
    }

    /**
     * Atualizar utilizador.
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? $user->phone,
                'department' => $data['department'] ?? $user->department,
                'position' => $data['position'] ?? $user->position,
                'is_active' => $data['is_active'] ?? $user->is_active,
            ]);

            // Atualizar password se fornecida
            if (!empty($data['password'])) {
                $user->update(['password' => $data['password']]);
            }

            // Sincronizar roles
            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            // Sincronizar permissões
            if (isset($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            return $user->fresh(['roles.permissions']);
        });
    }

    /**
     * Ativar utilizador.
     */
    public function activate(User $user): User
    {
        $user->update(['is_active' => true]);

        return $user;
    }

    /**
     * Desativar utilizador.
     */
    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);

        // Revogar todos os tokens
        $user->tokens()->delete();

        return $user;
    }

    /**
     * Obter utilizadores por departamento.
     */
    public function getByDepartment(string $department): Collection
    {
        return User::active()
            ->where('department', $department)
            ->with(['roles'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Obter estatísticas de utilizadores.
     */
    public function getStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::active()->count(),
            'inactive' => User::where('is_active', false)->count(),
            'by_department' => User::select('department')
                ->selectRaw('count(*) as count')
                ->groupBy('department')
                ->pluck('count', 'department')
                ->toArray(),
        ];
    }
}