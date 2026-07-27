<?php

namespace Database\Seeders;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\Payment;
use App\Support\Enums\Currency;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $contract = Contract::where('contract_number', 'EMP/2026/00001')->first();
        $measurement1 = Measurement::where('measurement_number', 'AM/2026/00001')->first();

        if (!$contract || !$measurement1) {
            $this->command->warn('⚠️ Contrato ou medição não encontrados');
            return;
        }

        $payments = [
            // Pagamento do primeiro auto de medição (já pago)
            [
                'contract_id' => $contract->id,
                'payment_number' => 'PAG/2026/00001',
                'payment_type' => 'measurement',
                'currency' => Currency::AOA,
                'gross_amount' => 135000000.00, // 150M - 15M retenção
                'vat_rate' => 14.00,
                'vat_amount' => 18900000.00,
                'withholding_tax_rate' => 2.00,
                'withholding_tax_amount' => 2700000.00,
                'stamp_duty_rate' => 0.00,
                'stamp_duty_amount' => 0.00,
                'retention_amount' => 15000000.00,
                'net_amount' => 136200000.00, // 135M + 18.9M IVA - 2.7M IIT - 15M retenção
                'due_date' => now()->subMonth()->subDays(20),
                'payment_date' => now()->subMonth()->subDays(20),
                'bank_reference' => 'TRF/2026/001234',
                'payment_method' => 'transfer',
                'status' => 'paid',
                'requested_at' => now()->subMonths(2),
                'approved_at' => now()->subMonths(1)->subDays(25),
                'invoice_number' => 'FT OMT/2026/001',
            ],

            // Pagamento pendente do segundo auto (aprovado mas não pago)
            [
                'contract_id' => $contract->id,
                'payment_number' => 'PAG/2026/00002',
                'payment_type' => 'measurement',
                'currency' => Currency::AOA,
                'gross_amount' => 162000000.00, // 180M - 18M retenção
                'vat_rate' => 14.00,
                'vat_amount' => 22680000.00,
                'withholding_tax_rate' => 2.00,
                'withholding_tax_amount' => 3240000.00,
                'stamp_duty_rate' => 0.00,
                'stamp_duty_amount' => 0.00,
                'retention_amount' => 18000000.00,
                'net_amount' => 163440000.00,
                'due_date' => now()->addDays(10), // A vencer em 10 dias
                'status' => 'approved',
                'requested_at' => now()->subMonth(),
                'approved_at' => now()->subDays(15),
                'invoice_number' => 'FT OMT/2026/002',
            ],

            // Pagamento em atraso (teste)
            [
                'contract_id' => $contract->id,
                'payment_number' => 'PAG/2026/00003',
                'payment_type' => 'advance',
                'currency' => Currency::AOA,
                'gross_amount' => 170000000.00, // Adiantamento 20%
                'vat_rate' => 14.00,
                'vat_amount' => 23800000.00,
                'withholding_tax_rate' => 2.00,
                'withholding_tax_amount' => 3400000.00,
                'stamp_duty_rate' => 0.00,
                'stamp_duty_amount' => 0.00,
                'retention_amount' => 0.00,
                'net_amount' => 190400000.00,
                'due_date' => now()->subDays(5), // Venceu há 5 dias!
                'status' => 'approved',
                'requested_at' => now()->subMonths(3),
                'approved_at' => now()->subMonths(2)->subDays(25),
                'invoice_number' => 'FT OMT/2026/003',
            ],
        ];

        foreach ($payments as $paymentData) {
            Payment::firstOrCreate(
                ['payment_number' => $paymentData['payment_number']],
                $paymentData
            );
        }

        $this->command->info('✅ ' . count($payments) . ' pagamentos criados com sucesso!');
    }
}