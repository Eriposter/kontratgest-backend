<?php

namespace Database\Seeders;

use App\Domain\Companies\Models\Company;
use App\Domain\Companies\Models\Ura;
use Illuminate\Database\Seeder;

class UraSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->warn('⚠️ Nenhuma empresa encontrada. Corre o CompanySeeder primeiro.');
            return;
        }

        $uras = [
            [
                'name' => 'URA de Obras Públicas',
                'description' => 'Unidade de Realização de Contratos para obras de infraestrutura',
                'department' => 'Direção de Engenharia',
                'members' => [
                    ['name' => 'Eng. João Silva', 'role' => 'presidente'],
                    ['name' => 'Arq. Maria Santos', 'role' => 'vogal'],
                    ['name' => 'Eng. Pedro Costa', 'role' => 'secretário'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'URA de Aquisições',
                'description' => 'Unidade de Realização de Contratos para aquisição de bens e serviços',
                'department' => 'Direção de Compras',
                'members' => [
                    ['name' => 'Dra. Ana Luísa', 'role' => 'presidente'],
                    ['name' => 'Lic. Carlos Mendes', 'role' => 'vogal'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'URA de Consultoria',
                'description' => 'Unidade de Realização de Contratos para serviços de consultoria',
                'department' => 'Direção de Planeamento',
                'members' => [
                    ['name' => 'Dr. Fernando Costa', 'role' => 'presidente'],
                    ['name' => 'Eng.ª Fernanda Lopes', 'role' => 'vogal'],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($uras as $uraData) {
            Ura::firstOrCreate(
                ['name' => $uraData['name'], 'company_id' => $company->id],
                array_merge($uraData, ['company_id' => $company->id])
            );
        }

        $this->command->info('✅ ' . count($uras) . ' URAs criadas com sucesso!');
    }
}