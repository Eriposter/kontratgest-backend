<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Login de utilizador.
     */
    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('Credenciais inválidas.');
        }

        if (!$user->can_login) {
            throw new AuthenticationException('Utilizador inativo ou sem password definida.');
        }

        // Criar token
        $token = $user->createToken(
            $deviceName ?? request()->userAgent() ?? 'api-token'
        );

        // Atualizar último login
        $user->update(['last_login_at' => now()]);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    /**
     * Logout (revogar token atual).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Logout de todos os dispositivos.
     */
    public function logoutFromAllDevices(User $user): int
    {
        return $user->tokens()->delete();
    }

    /**
     * Registar novo utilizador.
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
        ]);

        // Atribuir role padrão se fornecido
        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }

    /**
     * Atualizar password.
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \InvalidArgumentException('Password atual incorreta.');
        }

        $user->update(['password' => $newPassword]);

        // Revogar todos os tokens por segurança
        $user->tokens()->delete();

        return true;
    }

    /**
     * Obter utilizador autenticado com todas as relações.
     */
    public function getCurrentUserWithDetails(User $user): User
    {
        return $user->load(['roles.permissions']);
    }
}
