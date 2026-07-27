<?php

namespace Database\Seeders;

use App\Domain\Contracts\Models\ContractType;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'WORKS',
                'name' => 'Empreitada de Obras',
                'description' => 'Contratos para execução de obras públicas ou privadas',
                'default_payment_terms' => [
                    'advance_payment_percentage' => 20,
                    'retention_percentage' => 10,
                    'payment_frequency' => 'monthly',
                ],
                'required_guarantees' => ['performance', 'advance_payment', 'warranty'],
                'specific_fields_schema' => [
                    'prazo_execucao_meses' => 'integer',
                    'prazo_garantia_obra_meses' => 'integer',
                    'fiscalizacao_obrigatoria' => 'boolean',
                ],
            ],
            [
                'code' => 'SERVICE',
                'name' => 'Prestação de Serviços',
                'description' => 'Contratos para prestação de serviços especializados',
                'default_payment_terms' => [
                    'payment_frequency' => 'monthly',
                    'payment_days' => 30,
                ],
                'required_guarantees' => [],
                'specific_fields_schema' => [
                    'horas_mensais' => 'integer',
                    'slas' => 'array',
                    'confidencialidade' => 'boolean',
                ],
            ],
            [
                'code' => 'SUPPLY',
                'name' => 'Fornecimento de Bens',
                'description' => 'Contratos para fornecimento de materiais e equipamentos',
                'default_payment_terms' => [
                    'advance_payment_percentage' => 30,
                    'payment_on_delivery_percentage' => 70,
                ],
                'required_guarantees' => ['performance'],
                'specific_fields_schema' => [
                    'entregas_parciais' => 'boolean',
                    'prazo_entrega_dias' => 'integer',
                    'garantia_equipamento_meses' => 'integer',
                ],
            ],
            [
                'code' => 'CONSULTANCY',
                'name' => 'Consultoria',
                'description' => 'Contratos para serviços de consultoria especializada',
                'default_payment_terms' => [
                    'payment_frequency' => 'milestone',
                ],
                'required_guarantees' => [],
                'specific_fields_schema' => [
                    'tipo_consultoria' => 'string',
                    'entregaveis' => 'array',
                ],
            ],
            [
                'code' => 'LEASE',
                'name' => 'Arrendamento',
                'description' => 'Contratos de arrendamento de bens imóveis ou equipamentos',
                'default_payment_terms' => [
                    'payment_frequency' => 'monthly',
                    'caucao_meses' => 2,
                ],
                'required_guarantees' => [],
                'specific_fields_schema' => [
                    'tipo_bem' => 'string',
                    'localizacao' => 'string',
                    'atualizacao_anual' => 'boolean',
                ],
            ],
        ];

        foreach ($types as $typeData) {
            ContractType::firstOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        $this->command->info('✅ ' . count($types) . ' tipos de contrato criados com sucesso!');
    }
}