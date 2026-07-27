<?php

namespace Database\Seeders;

use App\Domain\Companies\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['nif' => '5416000000'],
            [
                'name' => 'Empresa Pública de Águas de Luanda',
                'legal_name' => 'Empresa Pública de Águas de Luanda, E.P.',
                'nif' => '5416000000',
                'company_type' => 'public',
                'sector' => 'water',
                'legal_nature' => 'EP',
                'email' => 'geral@epal.ao',
                'phone' => '+244 222 445 000',
                'address' => 'Avenida 4 de Fevereiro, 100',
                'city' => 'Luanda',
                'province' => 'luanda',
                'settings' => [
                    'default_currency' => 'AOA',
                    'fiscal_year_start' => '01-01',
                    'requires_tribunal_visto_above' => 50000000,
                    'approval_thresholds' => [
                        'director' => 10000000,
                        'council' => 50000000,
                        'minister' => 100000000,
                    ],
                ],
                'enabled_features' => [
                    'public_procedures',
                    'tribunal_contas',
                    'ura',
                    'fiscalizacao',
                    'publications',
                ],
                'is_active' => true,
            ]
        );

        // Associar todas as entidades e contratos existentes a esta empresa
        \App\Domain\Entities\Models\Entity::query()->update(['company_id' => $company->id]);
        \App\Domain\Contracts\Models\Contract::query()->update(['company_id' => $company->id]);
        \App\Domain\Guarantees\Models\Guarantee::query()->update(['company_id' => $company->id]);
        \App\Domain\Payments\Models\Measurement::query()->update(['company_id' => $company->id]);
        \App\Domain\Payments\Models\Payment::query()->update(['company_id' => $company->id]);

        $this->command->info('✅ Empresa default criada: ' . $company->name);
    }
}