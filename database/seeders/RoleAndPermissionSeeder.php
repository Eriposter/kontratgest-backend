<?php

namespace Database\Seeders;

use App\Domain\Auth\Services\PermissionService;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Criar todas as permissões
        app(PermissionService::class)->createDefaultPermissions();

        // Role: Super Admin (acesso total)
        Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'sanctum']
        );

        // Role: Diretor Financeiro
        $directorFinanceiro = Role::firstOrCreate(
            ['name' => 'diretor-financeiro', 'guard_name' => 'sanctum']
        );
        $directorFinanceiro->syncPermissions([
            'contracts.view', 'contracts.approve',
            'payments.view', 'payments.create', 'payments.update', 'payments.approve',
            'guarantees.view', 'guarantees.release', 'guarantees.execute',
            'measurements.view', 'measurements.approve',
            'entities.view',
            'tax.view',
        ]);

        // Role: Gestor de Contratos
        $gestorContratos = Role::firstOrCreate(
            ['name' => 'gestor-contratos', 'guard_name' => 'sanctum']
        );
        $gestorContratos->syncPermissions([
            'contracts.view', 'contracts.create', 'contracts.update',
            'entities.view', 'entities.create', 'entities.update',
            'guarantees.view', 'guarantees.create',
            'measurements.view', 'measurements.create', 'measurements.update',
            'payments.view',
        ]);

        // Role: Gestor de Projeto (Operacional)
        $gestorProjeto = Role::firstOrCreate(
            ['name' => 'gestor-projeto', 'guard_name' => 'sanctum']
        );
        $gestorProjeto->syncPermissions([
            'contracts.view',
            'measurements.view', 'measurements.create',
            'entities.view',
        ]);

        // Role: Jurídico
        $juridico = Role::firstOrCreate(
            ['name' => 'juridico', 'guard_name' => 'sanctum']
        );
        $juridico->syncPermissions([
            'contracts.view', 'contracts.create', 'contracts.update',
            'entities.view',
            'guarantees.view',
        ]);

        // Role: Visualizador (apenas leitura)
        $visualizador = Role::firstOrCreate(
            ['name' => 'visualizador', 'guard_name' => 'sanctum']
        );
        $visualizador->syncPermissions([
            'contracts.view',
            'entities.view',
            'guarantees.view',
            'measurements.view',
            'payments.view',
        ]);

        $this->command->info('✅ Roles e permissões criados com sucesso!');
    }
}