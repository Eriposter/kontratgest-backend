<?php

namespace Database\Seeders;

use App\Domain\PAC\Models\AnnualContractPlan;
use App\Domain\PAC\Models\PlanNeed;
use Illuminate\Database\Seeder;

class PACSeeder extends Seeder
{
    public function run(): void
    {
        $plan = AnnualContractPlan::create([
            'company_id' => \App\Domain\Companies\Models\Company::first()->id,
            'year' => 2026,
            'title' => 'Plano Anual de Contratação 2026',
            'description' => 'Plano de contratações previstas para o ano fiscal de 2026',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $needs = [
            [
                'contract_type' => 'works',
                'procedure_type' => 'dynamic_electronic',
                'title' => 'Reabilitação da Rede de Distribuição de Água - Zona Norte',
                'description' => 'Substituição de 15km de condutas antigas por novas tubagens em PEAD',
                'justification' => 'Redução de perdas e melhoria da qualidade da água',
                'estimated_amount' => 450_000_000,
                'priority' => 'high',
                'planned_quarter' => 1,
                'status' => 'contracted',
            ],
            [
                'contract_type' => 'goods',
                'procedure_type' => 'invitation',
                'title' => 'Aquisição de Contadores Inteligentes',
                'description' => 'Compra de 5000 contadores de água com telemetria',
                'justification' => 'Modernização da rede de medição',
                'estimated_amount' => 180_000_000,
                'priority' => 'high',
                'planned_quarter' => 2,
                'status' => 'in_progress',
            ],
            [
                'contract_type' => 'services',
                'procedure_type' => 'limited_tender',
                'title' => 'Serviço de Manutenção de Estações de Bombagem',
                'description' => 'Manutenção preventiva e corretiva de 12 estações',
                'justification' => 'Garantir continuidade do serviço',
                'estimated_amount' => 95_000_000,
                'priority' => 'medium',
                'planned_quarter' => 1,
                'status' => 'planned',
            ],
        ];

        foreach ($needs as $needData) {
            $plan->needs()->create($needData);
        }

        $this->command->info('✅ PAC 2026 criado com sucesso!');
    }
}