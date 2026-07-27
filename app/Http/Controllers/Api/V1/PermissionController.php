<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\Services\PermissionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = $this->permissionService->list();

        return PermissionResource::collection($permissions);
    }

    public function grouped(): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $grouped = $this->permissionService->getGrouped();

        return response()->json(['data' => $grouped]);
    }

    public function createDefaults(): JsonResponse
    {
        $this->authorize('create', Permission::class);

        $this->permissionService->createDefaultPermissions();

        return response()->json([
            'message' => 'Permissões padrão criadas com sucesso.',
        ]);
    }
}