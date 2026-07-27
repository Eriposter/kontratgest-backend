<?php

namespace Database\Seeders;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\MeasurementItem;
use Illuminate\Database\Seeder;

class MeasurementSeeder extends Seeder
{
    public function run(): void
    {
        $contract = Contract::where('contract_number', 'EMP/2026/00001')->first();

        if (!$contract) {
            $this->command->warn('⚠️ Contrato de empreitada não encontrado');
            return;
        }

        $measurements = [
            [
                'measurement_number' => 'AM/2026/00001',
                'sequence_number' => 1,
                'period_start' => now()->subMonths(3),
                'period_end' => now()->subMonths(2),
                'total_amount' => 150000000.00, // 150 milhões Kz
                'cumulative_amount' => 150000000.00,
                'retention_percentage' => 10.00,
                'retention_amount' => 15000000.00,
                'status' => 'paid',
                'observations' => 'Primeiro auto - Trabalhos preliminares e fundações',
                'submitted_at' => now()->subMonths(2)->subDays(5),
                'approved_at' => now()->subMonths(2),
                'paid_at' => now()->subMonths(1)->subDays(20),
                'items' => [
                    [
                        'item_code' => '01.01',
                        'description' => 'Instalação de estaleiro',
                        'unit' => 'un',
                        'quantity' => 1,
                        'unit_price' => 25000000.00,
                    ],
                    [
                        'item_code' => '02.01',
                        'description' => 'Terraplenagens',
                        'unit' => 'm³',
                        'quantity' => 5000,
                        'unit_price' => 15000.00,
                    ],
                    [
                        'item_code' => '03.01',
                        'description' => 'Fundações - Estacas',
                        'unit' => 'ml',
                        'quantity' => 800,
                        'unit_price' => 31250.00,
                    ],
                ],
            ],
            [
                'measurement_number' => 'AM/2026/00002',
                'sequence_number' => 2,
                'period_start' => now()->subMonths(2),
                'period_end' => now()->subMonth(),
                'total_amount' => 180000000.00, // 180 milhões Kz
                'cumulative_amount' => 330000000.00,
                'retention_percentage' => 10.00,
                'retention_amount' => 18000000.00,
                'status' => 'approved',
                'observations' => 'Segundo auto - Estrutura em betão armado',
                'submitted_at' => now()->subMonth()->subDays(5),
                'approved_at' => now()->subMonth(),
                'items' => [
                    [
                        'item_code' => '04.01',
                        'description' => 'Estrutura - Pilares P1',
                        'unit' => 'm³',
                        'quantity' => 1200,
                        'unit_price' => 75000.00,
                    ],
                    [
                        'item_code' => '04.02',
                        'description' => 'Estrutura - Lajes L1',
                        'unit' => 'm²',
                        'quantity' => 3000,
                        'unit_price' => 30000.00,
                    ],
                ],
            ],
            [
                'measurement_number' => 'AM/2026/00003',
                'sequence_number' => 3,
                'period_start' => now()->subMonth(),
                'period_end' => now(),
                'total_amount' => 120000000.00, // 120 milhões Kz
                'cumulative_amount' => 450000000.00,
                'retention_percentage' => 10.00,
                'retention_amount' => 12000000.00,
                'status' => 'submitted',
                'observations' => 'Terceiro auto - Estrutura concluída, início alvenarias',
                'submitted_at' => now()->subDays(3),
                'items' => [
                    [
                        'item_code' => '04.03',
                        'description' => 'Estrutura - Vigas V1',
                        'unit' => 'm³',
                        'quantity' => 800,
                        'unit_price' => 80000.00,
                    ],
                    [
                        'item_code' => '05.01',
                        'description' => 'Alvenarias - Paredes exteriores',
                        'unit' => 'm²',
                        'quantity' => 2000,
                        'unit_price' => 20000.00,
                    ],
                ],
            ],
        ];

        foreach ($measurements as $measurementData) {
            $items = $measurementData['items'];
            unset($measurementData['items']);

            $measurement = Measurement::firstOrCreate(
                ['measurement_number' => $measurementData['measurement_number']],
                array_merge($measurementData, ['contract_id' => $contract->id])
            );

            foreach ($items as $itemData) {
                MeasurementItem::firstOrCreate(
                    [
                        'measurement_id' => $measurement->id,
                        'item_code' => $itemData['item_code'],
                    ],
                    array_merge($itemData, [
                        'measurement_id' => $measurement->id,
                        'total_amount' => $itemData['quantity'] * $itemData['unit_price'],
                    ])
                );
            }
        }

        $this->command->info('✅ ' . count($measurements) . ' autos de medição criados com sucesso!');
    }
}