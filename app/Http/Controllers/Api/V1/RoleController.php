<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\Services\RoleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->roleService->list();

        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create(
            $request->name,
            'sanctum',
            $request->permissions ?? [],
        );

        return (new RoleResource($role))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Role $role): RoleResource
    {
        $this->authorize('view', $role);

        $role->load('permissions');

        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $role = $this->roleService->update(
            $role,
            $request->name,
            $request->permissions ?? [],
        );

        return new RoleResource($role);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        $this->roleService->delete($role);

        return response()->json(null, 204);
    }

    public function assignPermissions(StoreRoleRequest $request, Role $role): RoleResource
    {
        $this->authorize('update', $role);

        $role = $this->roleService->assignPermissions($role, $request->permissions);

        return new RoleResource($role);
    }

    public function users(Role $role): AnonymousResourceCollection
    {
        $this->authorize('view', $role);

        $users = $this->roleService->getUsersWithRole($role);

        return UserResource::collection($users);
    }
}