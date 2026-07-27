<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password,
            $request->device_name,
        );

        return response()->json([
            'user' => new UserResource($result['user']->load(['roles.permissions'])),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logout efetuado com sucesso.']);
    }

    /**
     * POST /api/v1/auth/logout-all
     */
    public function logoutFromAllDevices(Request $request): JsonResponse
    {
        $count = $this->authService->logoutFromAllDevices($request->user());

        return response()->json([
            'message' => "Logout efetuado em {$count} dispositivos.",
            'count' => $count,
        ]);
    }

    /**
     * POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return (new UserResource($user->load(['roles.permissions'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): UserResource
    {
        $user = $this->authService->getCurrentUserWithDetails($request->user());

        return new UserResource($user);
    }

    /**
     * PUT /api/v1/auth/password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->authService->updatePassword(
            $request->user(),
            $request->current_password,
            $request->password,
        );

        return response()->json([
            'message' => 'Password atualizada com sucesso. Por favor, faça login novamente.',
        ]);
    }
}