<?php

namespace Database\Seeders;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Models\ContractType;
use App\Domain\Entities\Models\Entity;
use App\Support\Enums\ContractStatus;
use App\Support\Enums\Currency;
use App\Support\Enums\PaymentModel;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = [
            // Empreitada de Obras
            [
                'contract_number' => 'EMP/2026/00001',
                'contract_type_code' => 'WORKS',
                'counterparty_nif' => '5417123456', // Omateque
                'title' => 'Construção do Edifício Sede - Talatona',
                'description' => 'Construção de edifício de 10 pisos para sede da empresa',
                'object' => 'Execução da obra de construção civil conforme projeto aprovado',
                'currency' => Currency::AOA,
                'total_amount' => 850000000.00, // 850 milhões Kz
                'vat_rate' => 14.00,
                'withholding_tax_rate' => 2.00,
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(9),
                'duration_months' => 12,
                'payment_model' => PaymentModel::MEASUREMENT,
                'requires_bna_registration' => false,
                'specific_data' => [
                    'prazo_execucao_meses' => 12,
                    'prazo_garantia_obra_meses' => 24,
                    'fiscalizacao_obrigatoria' => true,
                ],
                'status' => ContractStatus::ACTIVE,
                'payment_schedules' => [
                    ['milestone_name' => 'Adiantamento 20%', 'percentage' => 20, 'due_date' => now()->subMonths(3)],
                    ['milestone_name' => 'Fase 1 - Estrutura', 'percentage' => 30, 'due_date' => now()->addMonths(2)],
                    ['milestone_name' => 'Fase 2 - Acabamentos', 'percentage' => 30, 'due_date' => now()->addMonths(5)],
                    ['milestone_name' => 'Receção Definitiva', 'percentage' => 20, 'due_date' => now()->addMonths(9)],
                ],
            ],

            // Prestação de Serviços
            [
                'contract_number' => 'SER/2026/00002',
                'contract_type_code' => 'SERVICE',
                'counterparty_nif' => '5417456789', // Deloitte
                'title' => 'Auditoria Financeira Anual 2026',
                'description' => 'Serviços de auditoria financeira e fiscal',
                'object' => 'Auditoria às demonstrações financeiras do exercício 2026',
                'currency' => Currency::AOA,
                'total_amount' => 45000000.00, // 45 milhões Kz
                'vat_rate' => 14.00,
                'withholding_tax_rate' => 6.50,
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(5),
                'duration_months' => 6,
                'payment_model' => PaymentModel::INSTALLMENT,
                'requires_bna_registration' => false,
                'specific_data' => [
                    'horas_mensais' => 160,
                    'slas' => ['relatorio_mensal' => true, 'reuniao_trimestral' => true],
                    'confidencialidade' => true,
                ],
                'status' => ContractStatus::ACTIVE,
                'payment_schedules' => [
                    ['milestone_name' => 'Início dos Trabalhos', 'percentage' => 30, 'due_date' => now()->subMonths(1)],
                    ['milestone_name' => 'Relatório Intermédio', 'percentage' => 40, 'due_date' => now()->addMonths(2)],
                    ['milestone_name' => 'Relatório Final', 'percentage' => 30, 'due_date' => now()->addMonths(5)],
                ],
            ],

            // Fornecimento de Bens
            [
                'contract_number' => 'FOR/2026/00003',
                'contract_type_code' => 'SUPPLY',
                'counterparty_nif' => '5417345678', // DAM
                'title' => 'Fornecimento de Materiais de Construção',
                'description' => 'Fornecimento de cimento, ferro e outros materiais',
                'object' => 'Fornecimento de materiais de construção para obra em Talatona',
                'currency' => Currency::AOA,
                'total_amount' => 120000000.00, // 120 milhões Kz
                'vat_rate' => 14.00,
                'withholding_tax_rate' => 0.00,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(4),
                'duration_months' => 6,
                'payment_model' => PaymentModel::CONSIGNMENT,
                'requires_bna_registration' => false,
                'specific_data' => [
                    'entregas_parciais' => true,
                    'prazo_entrega_dias' => 15,
                    'garantia_equipamento_meses' => 12,
                ],
                'status' => ContractStatus::ACTIVE,
                'payment_schedules' => [
                    ['milestone_name' => 'Adiantamento', 'percentage' => 30, 'due_date' => now()->subMonths(2)],
                    ['milestone_name' => 'Entrega 1', 'percentage' => 25, 'due_date' => now()->subMonths(1)],
                    ['milestone_name' => 'Entrega 2', 'percentage' => 25, 'due_date' => now()],
                    ['milestone_name' => 'Entrega Final', 'percentage' => 20, 'due_date' => now()->addMonths(2)],
                ],
            ],

            // Contrato em USD (requer registo BNA)
            [
                'contract_number' => 'CON/2026/00004',
                'contract_type_code' => 'CONSULTANCY',
                'counterparty_nif' => '5417987654', // China Geo
                'title' => 'Consultoria Técnica - Projeto Hidroelétrico',
                'description' => 'Consultoria especializada em engenharia hidroelétrica',
                'object' => 'Prestação de consultoria técnica para projeto de barragem',
                'currency' => Currency::USD,
                'total_amount' => 500000.00, // 500 mil USD
                'exchange_rate' => 830.50, // Taxa BNA
                'vat_rate' => 14.00,
                'withholding_tax_rate' => 6.50,
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addMonths(6),
                'duration_months' => 12,
                'payment_model' => PaymentModel::MILESTONE,
                'requires_bna_registration' => true,
                'bna_registration_number' => 'BNA/REG/2026/12345',
                'bna_registration_date' => now()->subMonths(6),
                'specific_data' => [
                    'tipo_consultoria' => 'Engenharia Hidroelétrica',
                    'entregaveis' => ['Estudo de Viabilidade', 'Projeto Executivo', 'Acompanhamento de Obra'],
                ],
                'status' => ContractStatus::ACTIVE,
                'payment_schedules' => [
                    ['milestone_name' => 'Início', 'percentage' => 25, 'due_date' => now()->subMonths(6)],
                    ['milestone_name' => 'Estudo de Viabilidade', 'percentage' => 25, 'due_date' => now()->subMonths(3)],
                    ['milestone_name' => 'Projeto Executivo', 'percentage' => 30, 'due_date' => now()],
                    ['milestone_name' => 'Conclusão', 'percentage' => 20, 'due_date' => now()->addMonths(6)],
                ],
            ],

            // Contrato em Rascunho
            [
                'contract_number' => 'SER/2026/00005',
                'contract_type_code' => 'SERVICE',
                'counterparty_nif' => '5417567890', // Tech Angola
                'title' => 'Manutenção de Sistemas Informáticos',
                'description' => 'Contrato de manutenção preventiva e corretiva',
                'object' => 'Manutenção de infraestruturas IT',
                'currency' => Currency::AOA,
                'total_amount' => 18000000.00, // 18 milhões Kz
                'vat_rate' => 14.00,
                'withholding_tax_rate' => 6.50,
                'start_date' => now()->addMonths(1),
                'end_date' => now()->addMonths(13),
                'duration_months' => 12,
                'payment_model' => PaymentModel::INSTALLMENT,
                'requires_bna_registration' => false,
                'specific_data' => [
                    'horas_mensais' => 80,
                    'slas' => ['tempo_resposta_horas' => 4],
                    'confidencialidade' => true,
                ],
                'status' => ContractStatus::DRAFT,
                'payment_schedules' => [],
            ],
        ];

        foreach ($contracts as $contractData) {
            $contractType = ContractType::where('code', $contractData['contract_type_code'])->first();
            $counterparty = Entity::where('nif', $contractData['counterparty_nif'])->first();

            if (!$contractType || !$counterparty) {
                $this->command->warn("⚠️ Contrato {$contractData['contract_number']} não criado - tipo ou contraparte não encontrados");
                continue;
            }

            $paymentSchedules = $contractData['payment_schedules'];
            unset($contractData['contract_type_code'], $contractData['counterparty_nif'], $contractData['payment_schedules']);

            $contract = Contract::firstOrCreate(
                ['contract_number' => $contractData['contract_number']],
                array_merge($contractData, [
                    'contract_type_id' => $contractType->id,
                    'counterparty_id' => $counterparty->id,
                ])
            );

            // Criar payment schedules
            foreach ($paymentSchedules as $index => $schedule) {
                $contract->paymentSchedules()->firstOrCreate(
                    ['milestone_name' => $schedule['milestone_name']],
                    array_merge($schedule, ['sequence_order' => $index + 1])
                );
            }
        }

        $this->command->info('✅ ' . count($contracts) . ' contratos criados com sucesso!');
    }
}