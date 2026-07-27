<?php

namespace Database\Seeders;

use App\Domain\Tax\Models\TaxConfiguration;
use Illuminate\Database\Seeder;

class TaxConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $taxes = [
            [
                'tax_type' => 'iva',
                'name' => 'IVA - Taxa Normal',
                'description' => 'Imposto sobre o Valor Acrescentado - Taxa normal de 14%',
                'rate' => 14.00,
                'applicable_rules' => [
                    'is_exempt' => false,
                    'applies_to' => ['goods', 'services'],
                ],
                'valid_from' => now()->subYear(),
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'tax_type' => 'iva',
                'name' => 'IVA - Taxa Reduzida',
                'description' => 'IVA para bens essenciais (6%)',
                'rate' => 6.00,
                'applicable_rules' => [
                    'is_exempt' => false,
                    'applies_to' => ['essential_goods'],
                ],
                'valid_from' => now()->subYear(),
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'tax_type' => 'withholding',
                'name' => 'Retenção na Fonte - Serviços',
                'description' => 'Retenção de 6.5% sobre serviços prestados',
                'rate' => 6.50,
                'applicable_rules' => [
                    'applies_to' => ['services', 'consultancy'],
                    'min_amount' => 200000,
                    'entity_type' => 'collective',
                ],
                'valid_from' => now()->subYear(),
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'tax_type' => 'withholding',
                'name' => 'Retenção na Fonte - Empreitadas',
                'description' => 'Retenção de 2% sobre empreitadas',
                'rate' => 2.00,
                'applicable_rules' => [
                    'applies_to' => ['works'],
                    'min_amount' => 500000,
                ],
                'valid_from' => now()->subYear(),
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'tax_type' => 'stamp_duty',
                'name' => 'Imposto de Selo - Contratos',
                'description' => 'Imposto de Selo sobre contratos superiores a 1.000.000 AOA',
                'rate' => 0.50,
                'applicable_rules' => [
                    'applies_to' => ['contracts'],
                    'min_amount' => 1000000,
                ],
                'valid_from' => now()->subYear(),
                'valid_to' => null,
                'is_active' => true,
            ],
        ];

        foreach ($taxes as $taxData) {
            TaxConfiguration::firstOrCreate(
                [
                    'tax_type' => $taxData['tax_type'],
                    'rate' => $taxData['rate'],
                    'valid_from' => $taxData['valid_from'],
                ],
                $taxData
            );
        }

        $this->command->info('✅ ' . count($taxes) . ' configurações fiscais criadas com sucesso!');
    }
}