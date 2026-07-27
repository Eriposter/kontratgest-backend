<?php

namespace Database\Seeders;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Guarantees\Models\Guarantee;
use App\Support\Enums\Currency;
use App\Support\Enums\GuaranteePurpose;
use App\Support\Enums\GuaranteeType;
use Illuminate\Database\Seeder;

class GuaranteeSeeder extends Seeder
{
    public function run(): void
    {
        $guarantees = [
            // Caução de Boa Execução - Empreitada Omateque
            [
                'contract_number' => 'EMP/2026/00001',
                'guarantee_number' => 'GB/2026/00001',
                'guarantee_type' => GuaranteeType::BANK_GUARANTEE,
                'purpose' => GuaranteePurpose::PERFORMANCE,
                'issuing_entity' => 'Banco BIC',
                'issuing_entity_nif' => '5416000003',
                'currency' => Currency::AOA,
                'amount' => 85000000.00, // 10% do contrato
                'issue_date' => now()->subMonths(3),
                'expiry_date' => now()->addMonths(21), // 12 meses obra + 9 meses garantia
                'release_conditions' => 'Após receção definitiva da obra',
                'status' => 'active',
            ],

            // Caução de Adiantamento - Empreitada Omateque
            [
                'contract_number' => 'EMP/2026/00001',
                'guarantee_number' => 'GB/2026/00002',
                'guarantee_type' => GuaranteeType::BANK_GUARANTEE,
                'purpose' => GuaranteePurpose::ADVANCE_PAYMENT,
                'issuing_entity' => 'Banco BIC',
                'issuing_entity_nif' => '5416000003',
                'currency' => Currency::AOA,
                'amount' => 170000000.00, // 20% do contrato
                'issue_date' => now()->subMonths(3),
                'expiry_date' => now()->addMonths(9),
                'release_conditions' => 'Amortização progressiva com os autos de medição',
                'status' => 'active',
            ],

            // Caução de Boa Execução - Fornecimento DAM
            [
                'contract_number' => 'FOR/2026/00003',
                'guarantee_number' => 'GB/2026/00003',
                'guarantee_type' => GuaranteeType::INSURANCE,
                'purpose' => GuaranteePurpose::PERFORMANCE,
                'issuing_entity' => 'Seguradora Angola',
                'issuing_entity_nif' => '5416000004',
                'currency' => Currency::AOA,
                'amount' => 12000000.00, // 10% do contrato
                'issue_date' => now()->subMonths(2),
                'expiry_date' => now()->addMonths(10),
                'release_conditions' => 'Após entrega final e aceitação dos materiais',
                'status' => 'active',
            ],

            // Caução em USD - Consultoria China Geo
            [
                'contract_number' => 'CON/2026/00004',
                'guarantee_number' => 'GB/2026/00004',
                'guarantee_type' => GuaranteeType::BANK_GUARANTEE,
                'purpose' => GuaranteePurpose::PERFORMANCE,
                'issuing_entity' => 'Banco BFA',
                'issuing_entity_nif' => '5416543210',
                'currency' => Currency::USD,
                'amount' => 50000.00, // 10% do contrato
                'exchange_rate' => 830.50,
                'issue_date' => now()->subMonths(6),
                'expiry_date' => now()->addMonths(12),
                'release_conditions' => 'Após conclusão do projeto',
                'status' => 'active',
            ],

            // Caução a expirar em breve (para testar alertas)
            [
                'contract_number' => 'FOR/2026/00003',
                'guarantee_number' => 'GB/2026/00005',
                'guarantee_type' => GuaranteeType::BANK_GUARANTEE,
                'purpose' => GuaranteePurpose::BID,
                'issuing_entity' => 'Banco BAI',
                'issuing_entity_nif' => '5416881284',
                'currency' => Currency::AOA,
                'amount' => 5000000.00,
                'issue_date' => now()->subMonths(10),
                'expiry_date' => now()->addDays(15), // A expirar em 15 dias!
                'release_conditions' => 'Após adjudicação do contrato',
                'status' => 'active',
            ],

            // Caução já libertada
            [
                'contract_number' => 'SER/2026/00002',
                'guarantee_number' => 'GB/2026/00006',
                'guarantee_type' => GuaranteeType::CASH_DEPOSIT,
                'purpose' => GuaranteePurpose::RETENTION,
                'issuing_entity' => 'Depósito Interno',
                'currency' => Currency::AOA,
                'amount' => 4500000.00,
                'issue_date' => now()->subMonths(1),
                'expiry_date' => now()->addMonths(5),
                'release_date' => now()->subDays(10),
                'release_notes' => 'Libertada após aprovação do relatório intermédio',
                'status' => 'released',
            ],
        ];

        foreach ($guarantees as $guaranteeData) {
            $contract = Contract::where('contract_number', $guaranteeData['contract_number'])->first();

            if (!$contract) {
                $this->command->warn("⚠️ Caução {$guaranteeData['guarantee_number']} não criada - contrato não encontrado");
                continue;
            }

            $contractNumber = $guaranteeData['contract_number'];
            unset($guaranteeData['contract_number']);

            Guarantee::firstOrCreate(
                ['guarantee_number' => $guaranteeData['guarantee_number']],
                array_merge($guaranteeData, ['contract_id' => $contract->id])
            );
        }

        $this->command->info('✅ ' . count($guarantees) . ' cauções criadas com sucesso!');
    }
}