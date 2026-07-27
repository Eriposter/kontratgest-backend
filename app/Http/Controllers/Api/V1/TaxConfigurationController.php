<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Tax\Models\TaxConfiguration;
use App\Domain\Tax\Services\TaxCalculationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tax\StoreTaxConfigurationRequest;
use App\Http\Resources\TaxConfigurationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaxConfigurationController extends Controller
{
    public function __construct(
        private readonly TaxCalculationService $taxService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', TaxConfiguration::class);

        $query = TaxConfiguration::query()
            ->orderByDesc('valid_from');

        if ($request->filled('type')) {
            $query->ofType($request->string('type')->value());
        }

        if ($request->boolean('active_only', true)) {
            $query->active()->validAt();
        }

        $taxes = $query->paginate($request->integer('per_page', 20));

        return TaxConfigurationResource::collection($taxes);
    }

    public function store(StoreTaxConfigurationRequest $request): JsonResponse
    {
        $tax = TaxConfiguration::create($request->validated());

        return (new TaxConfigurationResource($tax))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TaxConfiguration $taxConfiguration): TaxConfigurationResource
    {
        $this->authorize('view', $taxConfiguration);

        return new TaxConfigurationResource($taxConfiguration);
    }

    public function update(StoreTaxConfigurationRequest $request, TaxConfiguration $taxConfiguration): TaxConfigurationResource
    {
        $this->authorize('update', $taxConfiguration);

        $taxConfiguration->update($request->validated());

        return new TaxConfigurationResource($taxConfiguration);
    }

    public function destroy(TaxConfiguration $taxConfiguration): JsonResponse
    {
        $this->authorize('delete', $taxConfiguration);

        $taxConfiguration->delete();

        return response()->json(null, 204);
    }

    public function current(): JsonResponse
    {
        $this->authorize('viewAny', TaxConfiguration::class);

        $taxes = $this->taxService->getCurrentTaxConfigurations();

        return response()->json([
            'data' => TaxConfigurationResource::collection($taxes),
        ]);
    }
}