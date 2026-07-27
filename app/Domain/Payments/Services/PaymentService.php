<?php

declare(strict_types=1);

namespace App\Domain\Payments\Services;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Entities\Models\Entity;
use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\Payment;
use App\Domain\Tax\Services\TaxCalculationService;
use App\Support\Enums\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly TaxCalculationService $taxService,
        private readonly MeasurementService $measurementService,
    ) {}

    /**
     * Listar pagamentos.
     */
    public function list(
        ?string $contractId = null,
        ?string $status = null,
        ?string $type = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Payment::query()
            ->with(['contract.counterparty', 'schedule', 'measurement'])
            ->orderByDesc('created_at');

        if ($contractId) {
            $query->where('contract_id', $contractId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('payment_type', $type);
        }

        return $query->paginate($perPage);
    }

    /**
     * Criar pagamento a partir de um auto de medição.
     */
    public function createFromMeasurement(Measurement $measurement): Payment
    {
        return DB::transaction(function () use ($measurement) {
            $contract = $measurement->contract;
            $entity = $contract->counterparty;

            // Calcular impostos
            $taxes = $this->taxService->calculatePaymentTaxes(
                grossAmount: $measurement->net_amount,
                entity: $entity,
                currency: $contract->currency->value,
                contractType: $contract->type->code,
            );

            $payment = Payment::create([
                'contract_id' => $contract->id,
                'payment_number' => $this->generatePaymentNumber(),
                'payment_type' => 'measurement',
                'currency' => $contract->currency->value,
                'gross_amount' => $measurement->net_amount,
                'exchange_rate' => $contract->exchange_rate,
                'vat_rate' => $taxes['vat']['rate'],
                'vat_amount' => $taxes['vat']['amount'],
                'withholding_tax_rate' => $taxes['withholding_tax']['rate'],
                'withholding_tax_amount' => $taxes['withholding_tax']['amount'],
                'stamp_duty_rate' => $taxes['stamp_duty']['rate'],
                'stamp_duty_amount' => $taxes['stamp_duty']['amount'],
                'retention_amount' => $measurement->retention_amount,
                'net_amount' => $taxes['net_amount'],
                'due_date' => now()->addDays(30), // Padrão: 30 dias
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'requested_at' => now(),
            ]);

            // Atualizar auto de medição
            $this->measurementService->markAsPaid($measurement, $payment->id);

            return $payment->load(['contract.counterparty', 'measurement']);
        });
    }

    /**
     * Criar pagamento manual (adiantamento, marco, etc).
     */

    public function create(
    string $contract_id,
    ?string $measurement_id,  // ← ADICIONAR
    string $payment_type,
    float $gross_amount,
    float $vat_rate,
    float $withholding_tax_rate,
    float $stamp_duty_rate = 0,
    float $retention_amount = 0,
    ?string $due_date = null,
    ?string $invoice_date = null,
    ?string $invoice_number = null,
    ?string $notes = null
): Payment {
    $contract = Contract::findOrFail($contract_id);
    
    // Calcular valores
    $vat_amount = $gross_amount * ($vat_rate / 100);
    $withholding_amount = $gross_amount * ($withholding_tax_rate / 100);
    $stamp_duty_amount = $gross_amount * ($stamp_duty_rate / 100);
    $net_amount = $gross_amount + $vat_amount - $withholding_amount - $stamp_duty_amount - $retention_amount;

    return Payment::create([
        'company_id' => $contract->company_id,
        'contract_id' => $contract_id,
        'measurement_id' => $measurement_id,  // ← ADICIONAR
        'payment_number' => $this->generatePaymentNumber(),
        'payment_type' => $payment_type,
        'currency' => $contract->currency,
        'gross_amount' => $gross_amount,
        'vat_rate' => $vat_rate,
        'vat_amount' => $vat_amount,
        'withholding_tax_rate' => $withholding_tax_rate,
        'withholding_tax_amount' => $withholding_amount,
        'stamp_duty_rate' => $stamp_duty_rate,
        'stamp_duty_amount' => $stamp_duty_amount,
        'retention_amount' => $retention_amount,
        'net_amount' => $net_amount,
        'due_date' => $due_date,
        'invoice_date' => $invoice_date,
        'invoice_number' => $invoice_number,
        'status' => PaymentStatus::PENDING,
        'notes' => $notes,
        'requested_at' => now(),
    ]);
}

    public function createManual(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $contract = Contract::findOrFail($data['contract_id']);
            $entity = $contract->counterparty;

            // Calcular impostos
            $taxes = $this->taxService->calculatePaymentTaxes(
                grossAmount: $data['gross_amount'],
                entity: $entity,
                currency: $data['currency'] ?? $contract->currency->value,
                contractType: $contract->type->code,
            );

            $payment = Payment::create([
                'contract_id' => $contract->id,
                'payment_schedule_id' => $data['payment_schedule_id'] ?? null,
                'payment_number' => $this->generatePaymentNumber(),
                'payment_type' => $data['payment_type'],
                'currency' => $data['currency'] ?? $contract->currency->value,
                'gross_amount' => $data['gross_amount'],
                'exchange_rate' => $data['exchange_rate'] ?? $contract->exchange_rate,
                'vat_rate' => $taxes['vat']['rate'],
                'vat_amount' => $taxes['vat']['amount'],
                'withholding_tax_rate' => $taxes['withholding_tax']['rate'],
                'withholding_tax_amount' => $taxes['withholding_tax']['amount'],
                'stamp_duty_rate' => $taxes['stamp_duty']['rate'],
                'stamp_duty_amount' => $taxes['stamp_duty']['amount'],
                'retention_amount' => $data['retention_amount'] ?? 0,
                'net_amount' => $taxes['net_amount'],
                'due_date' => $data['due_date'] ?? now()->addDays(30),
                'invoice_date' => $data['invoice_date'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'payment_notes' => $data['payment_notes'] ?? null,
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'requested_at' => now(),
                'supporting_documents' => $data['supporting_documents'] ?? [],
            ]);

            return $payment->load(['contract.counterparty']);
        });
    }

    /**
     * Aprovar pagamento.
     */
    public function approve(Payment $payment, string $approvedBy): Payment
    {
        if (!$payment->can_be_approved) {
            throw new \InvalidArgumentException('Pagamento não pode ser aprovado neste estado.');
        }

        $payment->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $payment;
    }

    /**
     * Marcar pagamento como pago.
     */
    public function markAsPaid(
        Payment $payment,
        string $bankReference,
        string $paymentMethod,
        ?string $paymentDate = null,
    ): Payment {
        if (!$payment->can_be_paid) {
            throw new \InvalidArgumentException('Pagamento não pode ser marcado como pago neste estado.');
        }

        $payment->update([
            'status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
            'bank_reference' => $bankReference,
            'payment_method' => $paymentMethod,
        ]);

        // Atualizar schedule se existir
        if ($payment->payment_schedule_id) {
            $payment->schedule->update([
                'status' => 'paid',
                'paid_at' => $payment->payment_date,
            ]);
        }

        return $payment;
    }

    /**
     * Rejeitar pagamento.
     */
    public function reject(Payment $payment, string $notes): Payment
    {
        if ($payment->status !== 'pending') {
            throw new \InvalidArgumentException('Apenas pagamentos pendentes podem ser rejeitados.');
        }

        $payment->update([
            'status' => 'rejected',
            'payment_notes' => trim($payment->payment_notes . "\n[Rejeitado] {$notes}"),
        ]);

        return $payment;
    }

    /**
     * Cancelar pagamento.
     */
    public function cancel(Payment $payment, string $reason): Payment
    {
        if ($payment->status === 'paid') {
            throw new \InvalidArgumentException('Pagamentos já efetuados não podem ser cancelados.');
        }

        $payment->update([
            'status' => 'cancelled',
            'payment_notes' => trim($payment->payment_notes . "\n[Cancelado em " . now()->format('d/m/Y') . "] {$reason}"),
        ]);

        return $payment;
    }

    /**
     * Obter pagamentos em atraso.
     */
    public function getOverduePayments(): Collection
    {
        return Payment::overdue()
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Obter pagamentos pendentes de aprovação.
     */
    public function getPendingPayments(): Collection
    {
        return Payment::pending()
            ->with(['contract.counterparty'])
            ->get();
    }

    /**
     * Resumo financeiro por contrato.
     */
    public function getFinancialSummary(string $contractId): array
    {
        $payments = Payment::where('contract_id', $contractId)->get();

        return [
            'total_payments' => $payments->count(),
            'pending' => $payments->where('status', 'pending')->count(),
            'approved' => $payments->where('status', 'approved')->count(),
            'paid' => $payments->where('status', 'paid')->count(),
            'overdue' => $payments->where('is_overdue', true)->count(),
            'total_gross' => $payments->sum('gross_amount'),
            'total_net' => $payments->where('status', 'paid')->sum('net_amount'),
            'total_vat' => $payments->sum('vat_amount'),
            'total_withholding' => $payments->sum('withholding_tax_amount'),
            'total_stamp_duty' => $payments->sum('stamp_duty_amount'),
        ];
    }

    /**
     * Gerar número de pagamento.
     */
    private function generatePaymentNumber(): string
    {
        $year = date('Y');
        $count = Payment::whereYear('created_at', $year)->count();

        return sprintf('PAG/%s/%05d', $year, $count + 1);
    }
}