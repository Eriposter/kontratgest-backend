<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Models\Role; // ← MUDAR: usar o nosso model
use Illuminate\Support\Collection;

class RoleService
{
    public function list(): Collection
    {
        return Role::with('permissions')
            ->orderBy('name')
            ->get();
    }

    public function create(string $name, ?string $guardName = 'sanctum', array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => $guardName,
        ]);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role->load('permissions');
    }

    public function update(Role $role, string $name, array $permissions = []): Role
    {
        $role->update(['name' => $name]);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role->fresh('permissions');
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    public function assignPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }

    public function getUsersWithRole(Role $role): Collection
    {
        return $role->users;
    }
}