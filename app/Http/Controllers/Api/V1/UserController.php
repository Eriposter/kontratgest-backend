<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->list(
            search: $request->string('search')->value(),
            department: $request->string('department')->value(),
            activeOnly: $request->boolean('active_only', true),
            perPage: $request->integer('per_page', 20),
        );

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        $user->load(['roles.permissions']);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user = $this->userService->update($user, $request->validated());

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(null, 204);
    }

    public function activate(User $user): UserResource
    {
        $this->authorize('update', $user);

        $user = $this->userService->activate($user);

        return new UserResource($user);
    }

    public function deactivate(User $user): UserResource
    {
        $this->authorize('update', $user);

        $user = $this->userService->deactivate($user);

        return new UserResource($user);
    }

    public function byDepartment(string $department): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->getByDepartment($department);

        return UserResource::collection($users);
    }

    public function stats(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $stats = $this->userService->getStats();

        return response()->json(['data' => $stats]);
    }
}