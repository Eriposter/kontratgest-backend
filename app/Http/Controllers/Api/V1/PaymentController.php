<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Payment::class);

        $payments = $this->paymentService->list(
            contractId: $request->string('contract_id')->value(),
            status: $request->string('status')->value(),
            type: $request->string('type')->value(),
            perPage: $request->integer('per_page', 20),
        );

        return PaymentResource::collection($payments);
    }

    public function store(StorePaymentRequest $request): PaymentResource
{
    $this->authorize('create', Payment::class);

    $payment = $this->paymentService->create(
        contract_id: $request->contract_id,
        measurement_id: $request->measurement_id,  // ← ADICIONAR
        payment_type: $request->payment_type,
        gross_amount: $request->gross_amount,
        vat_rate: $request->vat_rate,
        withholding_tax_rate: $request->withholding_tax_rate,
        stamp_duty_rate: $request->stamp_duty_rate ?? 0,
        retention_amount: $request->retention_amount ?? 0,
        due_date: $request->due_date,
        invoice_date: $request->invoice_date,
        invoice_number: $request->invoice_number,
        payment_notes: $request->payment_notes
    );

    return new PaymentResource($payment);
}

    public function show(Payment $payment): PaymentResource
    {
        $this->authorize('view', $payment);

        $payment->load(['contract.counterparty', 'schedule', 'measurement']);

        return new PaymentResource($payment);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/measurements/{measurement}/create-payment
     * Criar pagamento a partir de auto de medição.
     */
    public function createFromMeasurement(Measurement $measurement): PaymentResource
    {
        $this->authorize('create', Payment::class);

        $payment = $this->paymentService->createFromMeasurement($measurement);

        return new PaymentResource($payment);
    }

    public function approve(Payment $payment): PaymentResource
    {
        $this->authorize('approve', $payment);

        $payment = $this->paymentService->approve($payment, auth()->id());

        return new PaymentResource($payment);
    }

    public function markAsPaid(Request $request, Payment $payment): PaymentResource
    {
        $this->authorize('update', $payment);

        $payment = $this->paymentService->markAsPaid(
            $payment,
            $request->string('bank_reference')->value(),
            $request->string('payment_method')->value(),
            $request->string('payment_date')->value(),
        );

        return new PaymentResource($payment);
    }

    public function reject(Request $request, Payment $payment): PaymentResource
    {
        $this->authorize('approve', $payment);

        $payment = $this->paymentService->reject(
            $payment,
            $request->string('payment_notes')->value(),
        );

        return new PaymentResource($payment);
    }

    public function cancel(Request $request, Payment $payment): PaymentResource
    {
        $this->authorize('update', $payment);

        $payment = $this->paymentService->cancel(
            $payment,
            $request->string('reason')->value(),
        );

        return new PaymentResource($payment);
    }

    public function overdue(): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $payments = $this->paymentService->getOverduePayments();

        return response()->json([
            'data' => PaymentResource::collection($payments),
            'meta' => ['total' => $payments->count()],
        ]);
    }

    public function pending(): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $payments = $this->paymentService->getPendingPayments();

        return response()->json([
            'data' => PaymentResource::collection($payments),
            'meta' => ['total' => $payments->count()],
        ]);
    }

    public function financialSummary(string $contractId): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $summary = $this->paymentService->getFinancialSummary($contractId);

        return response()->json(['data' => $summary]);
    }
}