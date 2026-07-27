<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Models\Permission; // ← MUDAR: usar o nosso model
use Illuminate\Support\Collection;

class PermissionService
{
    public function list(?string $group = null): Collection
    {
        $query = Permission::query()->orderBy('name');

        if ($group) {
            $query->where('group', $group);
        }

        return $query->get();
    }

    public function create(string $name, ?string $guardName = 'sanctum', ?string $group = null): Permission
    {
        return Permission::create([
            'name' => $name,
            'guard_name' => $guardName,
            'group' => $group,
        ]);
    }

    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }

    public function getGrouped(): Collection
    {
        return Permission::all()
            ->groupBy('group')
            ->map(function ($permissions) {
                return $permissions->pluck('name');
            });
    }

    public function createDefaultPermissions(): void
    {
        $permissions = [
            // Entidades
            ['name' => 'entities.view', 'group' => 'entities'],
            ['name' => 'entities.create', 'group' => 'entities'],
            ['name' => 'entities.update', 'group' => 'entities'],
            ['name' => 'entities.delete', 'group' => 'entities'],
            
            // Contratos
            ['name' => 'contracts.view', 'group' => 'contracts'],
            ['name' => 'contracts.create', 'group' => 'contracts'],
            ['name' => 'contracts.update', 'group' => 'contracts'],
            ['name' => 'contracts.delete', 'group' => 'contracts'],
            ['name' => 'contracts.approve', 'group' => 'contracts'],
            ['name' => 'contracts.terminate', 'group' => 'contracts'],
            
            // Cauções
            ['name' => 'guarantees.view', 'group' => 'guarantees'],
            ['name' => 'guarantees.create', 'group' => 'guarantees'],
            ['name' => 'guarantees.update', 'group' => 'guarantees'],
            ['name' => 'guarantees.delete', 'group' => 'guarantees'],
            ['name' => 'guarantees.release', 'group' => 'guarantees'],
            ['name' => 'guarantees.execute', 'group' => 'guarantees'],
            
            // Autos de Medição
            ['name' => 'measurements.view', 'group' => 'measurements'],
            ['name' => 'measurements.create', 'group' => 'measurements'],
            ['name' => 'measurements.update', 'group' => 'measurements'],
            ['name' => 'measurements.delete', 'group' => 'measurements'],
            ['name' => 'measurements.approve', 'group' => 'measurements'],
            
            // Pagamentos
            ['name' => 'payments.view', 'group' => 'payments'],
            ['name' => 'payments.create', 'group' => 'payments'],
            ['name' => 'payments.update', 'group' => 'payments'],
            ['name' => 'payments.delete', 'group' => 'payments'],
            ['name' => 'payments.approve', 'group' => 'payments'],
            
            // Configurações Fiscais
            ['name' => 'tax.view', 'group' => 'tax'],
            ['name' => 'tax.create', 'group' => 'tax'],
            ['name' => 'tax.update', 'group' => 'tax'],
            ['name' => 'tax.delete', 'group' => 'tax'],
            
            // Utilizadores
            ['name' => 'users.view', 'group' => 'users'],
            ['name' => 'users.create', 'group' => 'users'],
            ['name' => 'users.update', 'group' => 'users'],
            ['name' => 'users.delete', 'group' => 'users'],
            
            // Roles e Permissões
            ['name' => 'roles.view', 'group' => 'roles'],
            ['name' => 'roles.create', 'group' => 'roles'],
            ['name' => 'roles.update', 'group' => 'roles'],
            ['name' => 'roles.delete', 'group' => 'roles'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'sanctum'],
                ['group' => $permission['group']]
            );
        }
    }
}