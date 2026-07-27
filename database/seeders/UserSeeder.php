<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador do Sistema',
                'email' => 'admin@kontratgest.ao',
                'password' => Hash::make('Admin@2026'),
                'phone' => '+244 923 456 789',
                'department' => 'TI',
                'position' => 'Administrador',
                'role' => 'super-admin',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@kontratgest.ao',
                'password' => Hash::make('Password@2026'),
                'phone' => '+244 923 111 222',
                'department' => 'Financeiro',
                'position' => 'Diretora Financeira',
                'role' => 'diretor-financeiro',
            ],
            [
                'name' => 'João Pedro',
                'email' => 'joao.pedro@kontratgest.ao',
                'password' => Hash::make('Password@2026'),
                'phone' => '+244 923 333 444',
                'department' => 'Contratos',
                'position' => 'Gestor de Contratos Sénior',
                'role' => 'gestor-contratos',
            ],
            [
                'name' => 'Ana Luísa',
                'email' => 'ana.luisa@kontratgest.ao',
                'password' => Hash::make('Password@2026'),
                'phone' => '+244 923 555 666',
                'department' => 'Operações',
                'position' => 'Gestora de Projeto',
                'role' => 'gestor-projeto',
            ],
            [
                'name' => 'Carlos Mendes',
                'email' => 'carlos.mendes@kontratgest.ao',
                'password' => Hash::make('Password@2026'),
                'phone' => '+244 923 777 888',
                'department' => 'Jurídico',
                'position' => 'Jurista Sénior',
                'role' => 'juridico',
            ],
            [
                'name' => 'Fernanda Costa',
                'email' => 'fernanda.costa@kontratgest.ao',
                'password' => Hash::make('Password@2026'),
                'phone' => '+244 923 999 000',
                'department' => 'Direção Geral',
                'position' => 'Assistente de Direção',
                'role' => 'visualizador',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->assignRole($role);
        }

        $this->command->info('✅ ' . count($users) . ' utilizadores criados com sucesso!');
    }
}