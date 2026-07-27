<?php

namespace Database\Seeders;

use App\Domain\Companies\Models\Company;
use App\Domain\Companies\Models\ContractProcedure;
use Illuminate\Database\Seeder;

class ContractProcedureSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->warn('⚠️ Nenhuma empresa encontrada. Corre o CompanySeeder primeiro.');
            return;
        }

        $procedures = [
            [
                'procedure_number' => 'CP/2026/001',
                'procedure_type' => 'public_tender',
                'title' => 'Concurso Público para Empreitada de Construção do Edifício Sede',
                'description' => 'Concurso público para construção de edifício de 10 pisos em Talatona',
                'legal_basis' => 'Artigo 45º da Lei n.º 41/20 de 18 de Dezembro',
                'justification' => 'Necessidade de ampliação das instalações da empresa',
                'estimated_value' => 850000000.00,
                'currency' => 'AOA',
                'publication_date' => now()->subMonths(4),
                'proposal_deadline' => now()->subMonths(3)->addDays(30),
                'adjudication_date' => now()->subMonths(3),
                'status' => 'adjudicated',
                'participants' => [
                    ['name' => 'Omateque Engenharia, Lda', 'nif' => '5417123456', 'proposal_value' => 820000000],
                    ['name' => 'China Geo Engineering Angola', 'nif' => '5417987654', 'proposal_value' => 790000000],
                ],
                'evaluation_criteria' => [
                    'price' => 60,
                    'technical' => 30,
                    'experience' => 10,
                ],
            ],
            [
                'procedure_number' => 'CP/2026/002',
                'procedure_type' => 'limited_tender',
                'title' => 'Concurso Limitado para Serviços de Auditoria',
                'description' => 'Concurso limitado para auditoria financeira anual',
                'legal_basis' => 'Artigo 48º da Lei n.º 41/20',
                'justification' => 'Obrigação legal de auditoria externa',
                'estimated_value' => 45000000.00,
                'currency' => 'AOA',
                'publication_date' => now()->subMonths(2),
                'proposal_deadline' => now()->subMonth(),
                'adjudication_date' => now()->subMonth(),
                'status' => 'adjudicated',
                'participants' => [
                    ['name' => 'Deloitte Angola', 'nif' => '5417456789', 'proposal_value' => 42000000],
                    ['name' => 'KPMG Angola', 'nif' => '5417555555', 'proposal_value' => 48000000],
                ],
                'evaluation_criteria' => [
                    'price' => 50,
                    'technical' => 40,
                    'experience' => 10,
                ],
            ],
        ];

        foreach ($procedures as $procedureData) {
            ContractProcedure::firstOrCreate(
                ['procedure_number' => $procedureData['procedure_number'], 'company_id' => $company->id],
                array_merge($procedureData, ['company_id' => $company->id])
            );
        }

        $this->command->info('✅ ' . count($procedures) . ' procedimentos públicos criados com sucesso!');
    }
}