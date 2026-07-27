<?php

namespace Database\Seeders;

use App\Domain\Companies\Models\Company;
use App\Domain\Tax\Models\TaxConfiguration;
use Illuminate\Database\Seeder;

class TaxConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->warn('⚠️ Nenhuma empresa encontrada. Corre o CompanySeeder primeiro.');
            return;
        }

        $taxes = [
            [
                'tax_type' => 'iva',
                'name' => 'IVA Geral',
                'description' => 'Imposto sobre o Valor Acrescentado - Taxa Geral',
                'rate' => 14.00,
                'applicable_rules' => ['default' => true],
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
                'applies_to' => ['goods', 'services'],
            ],
            [
                'tax_type' => 'iva',
                'name' => 'IVA Reduzido',
                'description' => 'Imposto sobre o Valor Acrescentado - Taxa Reduzida',
                'rate' => 6.00,
                'applicable_rules' => ['default' => false],
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
                'applies_to' => ['essential_goods'],
            ],
            [
                'tax_type' => 'iit',
                'name' => 'IIT - Serviços (2%)',
                'description' => 'Imposto Industrial sobre o Trabalho - Prestação de Serviços',
                'rate' => 2.00,
                'applicable_rules' => ['default' => true],
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
                'applies_to' => ['services'],
            ],
            [
                'tax_type' => 'iit',
                'name' => 'IIT - Serviços (6.5%)',
                'description' => 'Imposto Industrial sobre o Trabalho - Serviços sem contabilidade organizada',
                'rate' => 6.50,
                'applicable_rules' => ['default' => false],
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
                'applies_to' => ['services'],
            ],
            [
                'tax_type' => 'iit',
                'name' => 'IIT - Alugueres (10%)',
                'description' => 'Imposto Industrial sobre o Trabalho - Rendas e Alugueres',
                'rate' => 10.00,
                'applicable_rules' => ['default' => false],
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
                'applies_to' => ['rentals'],
            ],
            [
                'tax_type' => 'stamp_duty',
                'name' => 'Imposto de Selo (1%)',
                'description' => 'Imposto de Selo sobre contratos e documentos',
                'rate' => 1.00,
                'applicable_rules' => ['default' => true],
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
                'applies_to' => ['contracts'],
            ],
            [
                'tax_type' => 'stamp_duty',
                'name' => 'Imposto de Selo (2%)',
                'description' => 'Imposto de Selo sobre outros atos',
                'rate' => 2.00,
                'applicable_rules' => ['default' => false],
                'valid_from' => now()->startOfYear(),
                'valid_to' => null,
                'is_active' => true,
                'applies_to' => ['other'],
            ],
        ];

        foreach ($taxes as $taxData) {
            TaxConfiguration::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $taxData['name'],
                ],
                array_merge($taxData, ['company_id' => $company->id])
            );
        }

        $this->command->info('✅ Configurações fiscais criadas com sucesso para: ' . $company->name);
    }
}