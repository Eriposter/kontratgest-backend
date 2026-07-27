<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Guarantees\Models\Guarantee;
use App\Domain\Guarantees\Services\GuaranteeService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guarantees\StoreGuaranteeRequest;
use App\Http\Requests\Guarantees\UpdateGuaranteeRequest;
use App\Http\Resources\GuaranteeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GuaranteeController extends Controller
{
    public function __construct(
        private readonly GuaranteeService $guaranteeService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Guarantee::class);

        $guarantees = $this->guaranteeService->list(
            contractId: $request->string('contract_id')->value(),
            status: $request->string('status')->value(),
            type: $request->string('type')->value(),
            purpose: $request->string('purpose')->value(),
            perPage: $request->integer('per_page', 20),
        );

        return GuaranteeResource::collection($guarantees);
    }

    public function store(StoreGuaranteeRequest $request): JsonResponse
    {
        $guarantee = $this->guaranteeService->create($request->validated());

        return (new GuaranteeResource($guarantee))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Guarantee $guarantee): GuaranteeResource
    {
        $this->authorize('view', $guarantee);

        $guarantee->load(['contract.counterparty', 'documents']);

        return new GuaranteeResource($guarantee);
    }

    public function update(UpdateGuaranteeRequest $request, Guarantee $guarantee): GuaranteeResource
    {
        $guarantee = $this->guaranteeService->update($guarantee, $request->validated());

        return new GuaranteeResource($guarantee);
    }

    public function destroy(Guarantee $guarantee): JsonResponse
    {
        $this->authorize('delete', $guarantee);

        $guarantee->delete();

        return response()->json(null, 204);
    }

    public function release(Request $request, Guarantee $guarantee): GuaranteeResource
    {
        $this->authorize('release', $guarantee);

        $guarantee = $this->guaranteeService->release(
            $guarantee,
            auth()->id(),
            $request->string('notes')->value() ?? '',
        );

        return new GuaranteeResource($guarantee);
    }

    public function execute(Request $request, Guarantee $guarantee): GuaranteeResource
    {
        $this->authorize('execute', $guarantee);

        $guarantee = $this->guaranteeService->execute(
            $guarantee,
            $request->input('amount'),
            $request->string('reason')->value(),
        );

        return new GuaranteeResource($guarantee);
    }

    public function cancel(Request $request, Guarantee $guarantee): GuaranteeResource
    {
        $this->authorize('update', $guarantee);

        $guarantee = $this->guaranteeService->cancel(
            $guarantee,
            $request->string('reason')->value() ?? '',
        );

        return new GuaranteeResource($guarantee);
    }

    public function expiring(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Guarantee::class);

        $days = $request->integer('days', 30);
        $guarantees = $this->guaranteeService->getExpiringGuarantees($days);

        return response()->json([
            'data' => GuaranteeResource::collection($guarantees),
            'meta' => ['total' => $guarantees->count(), 'alert_days' => $days],
        ]);
    }

    public function expired(): JsonResponse
    {
        $this->authorize('viewAny', Guarantee::class);

        $guarantees = $this->guaranteeService->getExpiredGuarantees();

        return response()->json([
            'data' => GuaranteeResource::collection($guarantees),
            'meta' => ['total' => $guarantees->count()],
        ]);
    }

    public function summary(string $contractId): JsonResponse
    {
        $this->authorize('viewAny', Guarantee::class);

        $summary = $this->guaranteeService->getSummaryByContract($contractId);

        return response()->json(['data' => $summary]);
    }

    public function markExpired(): JsonResponse
    {
        $this->authorize('update', Guarantee::class);

        $count = $this->guaranteeService->markExpiredGuarantees();

        return response()->json([
            'message' => "{$count} cauções marcadas como expiradas.",
            'count' => $count,
        ]);
    }
}