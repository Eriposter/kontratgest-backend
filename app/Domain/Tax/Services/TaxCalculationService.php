<?php

declare(strict_types=1);

namespace App\Domain\Tax\Services;

use App\Domain\Entities\Models\Entity;
use App\Domain\Tax\Models\TaxConfiguration;
use Illuminate\Support\Collection;

class TaxCalculationService
{
    /**
     * Calcular todos os impostos para um pagamento.
     */
    public function calculatePaymentTaxes(
        float $grossAmount,
        Entity $entity,
        string $currency = 'AOA',
        ?string $contractType = null,
    ): array {
        $taxes = [
            'gross_amount' => $grossAmount,
            'vat' => $this->calculateVat($grossAmount, $entity),
            'withholding_tax' => $this->calculateWithholdingTax($grossAmount, $entity, $contractType),
            'stamp_duty' => $this->calculateStampDuty($grossAmount),
        ];

        $taxes['net_amount'] = $grossAmount 
            + $taxes['vat']['amount'] 
            - $taxes['withholding_tax']['amount'] 
            - $taxes['stamp_duty']['amount'];

        return $taxes;
    }

    /**
     * Calcular IVA (14% em Angola, com exceções).
     */
    public function calculateVat(float $amount, Entity $entity): array
    {
        // Entidades isentas de IVA
        if ($entity->is_tax_exempt) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
                'is_exempt' => true,
                'reason' => 'Entidade isenta de IVA',
            ];
        }

        $rate = TaxConfiguration::getCurrentRate('iva') ?? 14.00;
        $vatAmount = $amount * ($rate / 100);

        return [
            'rate' => $rate,
            'amount' => round($vatAmount, 2),
            'is_exempt' => false,
        ];
    }

    /**
     * Calcular retenção na fonte (IIT/IRS).
     * Em Angola, varia conforme o tipo de serviço e entidade.
     */
    public function calculateWithholdingTax(
        float $amount,
        Entity $entity,
        ?string $contractType = null,
    ): array {
        // Entidades públicas geralmente têm retenção
        $config = TaxConfiguration::active()
            ->ofType('withholding')
            ->validAt()
            ->first();

        if (!$config) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
                'applied' => false,
            ];
        }

        // Verificar regras de aplicação
        $rules = $config->applicable_rules;
        
        // Exemplo: Se o contrato é de serviços e a entidade é individual
        if (isset($rules['applies_to']) && !in_array($contractType, $rules['applies_to'])) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
                'applied' => false,
                'reason' => 'Tipo de contrato não sujeito a retenção',
            ];
        }

        // Verificar valor mínimo
        if (isset($rules['min_amount']) && $amount < $rules['min_amount']) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
                'applied' => false,
                'reason' => 'Valor abaixo do mínimo para retenção',
            ];
        }

        $rate = $config->rate;
        $taxAmount = $amount * ($rate / 100);

        return [
            'rate' => $rate,
            'amount' => round($taxAmount, 2),
            'applied' => true,
        ];
    }

    /**
     * Calcular Imposto de Selo.
     * Aplicável a contratos acima de certos valores.
     */
    public function calculateStampDuty(float $amount): array
    {
        $config = TaxConfiguration::active()
            ->ofType('stamp_duty')
            ->validAt()
            ->first();

        if (!$config) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
                'applied' => false,
            ];
        }

        $rules = $config->applicable_rules;

        // Verificar valor mínimo
        if (isset($rules['min_amount']) && $amount < $rules['min_amount']) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
                'applied' => false,
                'reason' => 'Valor abaixo do mínimo para imposto de selo',
            ];
        }

        $rate = $config->rate;
        $taxAmount = $amount * ($rate / 100);

        return [
            'rate' => $rate,
            'amount' => round($taxAmount, 2),
            'applied' => true,
        ];
    }

    /**
     * Obter todas as configurações fiscais vigentes.
     */
    public function getCurrentTaxConfigurations(): Collection
    {
        return TaxConfiguration::active()
            ->validAt()
            ->get();
    }
}