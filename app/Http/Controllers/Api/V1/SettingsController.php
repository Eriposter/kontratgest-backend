<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Companies\Models\Company;
use App\Domain\Tax\Models\TaxConfiguration;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;


class SettingsController extends Controller
{
    /**
     * GET /api/v1/settings/company
     */
    public function getCompany(): JsonResponse
    {
        $company = current_company();

        if (!$company) {
            return response()->json(['message' => 'Empresa não encontrada'], 404);
        }

        return response()->json(['data' => $company]);
    }

    /**
     * PUT /api/v1/settings/company
     */
    public function updateCompany(Request $request): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $company = current_company();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'settings' => 'sometimes|array',
            'settings.default_currency' => 'sometimes|string|in:AOA,USD,EUR',
            'settings.fiscal_year_start' => 'sometimes|string',
            'settings.requires_tribunal_visto_above' => 'sometimes|numeric|min:0',
            'settings.approval_thresholds' => 'sometimes|array',
        ]);

        $company->update($validated);

        return response()->json(['data' => $company->fresh()]);
    }

    /**
     * PUT /api/v1/settings/company/features
     */
    public function updateCompanyFeatures(Request $request): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $company = current_company();

        $validated = $request->validate([
            'features' => 'required|array',
            'features.*' => 'string',
        ]);

        $company->update(['enabled_features' => $validated['features']]);

        return response()->json(['data' => $company->fresh()]);
    }

    /**
     * GET /api/v1/settings/tax-configurations
     */
        public function getTaxConfigurations(): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        // Ordenar por 'name' em vez de 'code', pois 'code' pode não existir
        $configs = TaxConfiguration::where('company_id', current_company()->id)
            ->orderBy('name') 
            ->get();

        return response()->json(['data' => $configs]);
    }

    /**
     * PUT /api/v1/settings/tax-configurations/{id}
     */
    public function updateTaxConfiguration(Request $request, string $id): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $config = TaxConfiguration::findOrFail($id);

        $validated = $request->validate([
            'rate' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $config->update($validated);

        return response()->json(['data' => $config->fresh()]);
    }

    /**
     * GET /api/v1/settings/users
     */
    public function getUsers(): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $users = User::with('roles')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'department' => $user->department,
                    'position' => $user->position,
                    'is_active' => $user->is_active,
                    'roles' => $user->roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name]),
                    // ← CORREÇÃO AQUI: Usar Carbon::parse para garantir que é uma data válida
                    'last_login_at' => $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->toISOString() : null,
                    'created_at' => \Carbon\Carbon::parse($user->created_at)->toISOString(),
                ];
            });

        return response()->json(['data' => $users]);
    }

    /**
     * POST /api/v1/settings/users
     */
    public function createUser(Request $request): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'roles' => 'sometimes|array',
            'roles.*' => 'string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'position' => $validated['position'] ?? null,
            'is_active' => true,
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name]),
            ]
        ], 201);
    }

    /**
     * POST /api/v1/settings/users/{id}/toggle-status
     */
    public function toggleUserStatus(string $id): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Não pode desativar o seu próprio utilizador'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'is_active' => $user->is_active,
            ]
        ]);
    }

    /**
     * GET /api/v1/settings/roles
     */
    public function getRoles(): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $roles = Role::with('permissions')
            ->withCount('users')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permissions' => $role->permissions->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
                    'users_count' => $role->users_count,
                ];
            });

        return response()->json(['data' => $roles]);
    }

    /**
     * PUT /api/v1/settings/roles/{id}
     */
    public function updateRole(Request $request, string $id): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
            ]
        ]);
    }

        /**
     * POST /api/v1/settings/roles
     */
    public function storeRole(Request $request): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'sanctum']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
                'users_count' => 0,
            ]
        ], 201);
    }

    /**
     * DELETE /api/v1/settings/roles/{id}
     */
    public function destroyRole(string $id): JsonResponse
    {
        $this->authorize('manage-settings', Company::class);

        $role = Role::findOrFail($id);

        // Impedir eliminação de roles que têm utilizadores
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível eliminar uma role que está atribuída a utilizadores.'
            ], 422);
        }

        // Impedir eliminação da role super-admin
        if ($role->name === 'super-admin') {
            return response()->json([
                'message' => 'A role super-admin não pode ser eliminada.'
            ], 403);
        }

        $role->delete();

        return response()->json(['message' => 'Role eliminada com sucesso.']);
    }
}