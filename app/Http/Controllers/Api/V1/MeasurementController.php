<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payments\Models\Measurement;
use App\Domain\Payments\Services\MeasurementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\StoreMeasurementRequest;
use App\Http\Requests\Payments\UpdateMeasurementRequest;
use App\Http\Resources\MeasurementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeasurementController extends Controller
{
    public function __construct(
        private readonly MeasurementService $measurementService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Measurement::class);

        $measurements = $this->measurementService->list(
            contractId: $request->string('contract_id')->value(),
            status: $request->string('status')->value(),
            perPage: $request->integer('per_page', 20),
        );

        return MeasurementResource::collection($measurements);
    }

    public function store(StoreMeasurementRequest $request): JsonResponse
    {
        $measurement = $this->measurementService->create($request->validated());

        return (new MeasurementResource($measurement))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Measurement $measurement): MeasurementResource
    {
        $this->authorize('view', $measurement);

        $measurement->load(['contract.counterparty', 'items']);

        return new MeasurementResource($measurement);
    }

    public function update(UpdateMeasurementRequest $request, Measurement $measurement): MeasurementResource
    {
        $measurement = $this->measurementService->update($measurement, $request->validated());

        return new MeasurementResource($measurement);
    }

    public function destroy(Measurement $measurement): JsonResponse
    {
        $this->authorize('delete', $measurement);

        $measurement->delete();

        return response()->json(null, 204);
    }

    public function submit(Measurement $measurement): MeasurementResource
    {
        $this->authorize('update', $measurement);

        $measurement = $this->measurementService->submit($measurement, auth()->id());

        return new MeasurementResource($measurement);
    }

    public function approve(Request $request, Measurement $measurement): MeasurementResource
    {
        $this->authorize('approve', $measurement);

        $measurement = $this->measurementService->approve(
            $measurement,
            auth()->id(),
            $request->string('notes')->value() ?? '',
        );

        return new MeasurementResource($measurement);
    }

    public function reject(Request $request, Measurement $measurement): MeasurementResource
    {
        $this->authorize('approve', $measurement);

        $measurement = $this->measurementService->reject(
            $measurement,
            $request->string('notes')->value(),
        );

        return new MeasurementResource($measurement);
    }

    public function pending(): JsonResponse
    {
        $this->authorize('viewAny', Measurement::class);

        $measurements = $this->measurementService->getPendingMeasurements();

        return response()->json([
            'data' => MeasurementResource::collection($measurements),
            'meta' => ['total' => $measurements->count()],
        ]);
    }

    public function approvedUnpaid(): JsonResponse
    {
        $this->authorize('viewAny', Measurement::class);

        $measurements = $this->measurementService->getApprovedButUnpaidMeasurements();

        return response()->json([
            'data' => MeasurementResource::collection($measurements),
            'meta' => ['total' => $measurements->count()],
        ]);
    }
}