<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Services\ContractService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contracts\StoreContractRequest;
use App\Http\Requests\Contracts\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    public function __construct(
        private readonly ContractService $contractService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = $this->contractService->list(
            typeCode: $request->string('type')->value(),
            status: $request->string('status')->value(),
            search: $request->string('search')->value(),
            counterpartyId: $request->string('counterparty_id')->value(),
            perPage: $request->integer('per_page', 20),
        );

        return ContractResource::collection($contracts);
    }

    public function store(StoreContractRequest $request): JsonResponse
    {
        $contract = $this->contractService->create($request->validated());

        return (new ContractResource($contract))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Contract $contract): ContractResource
    {
        $this->authorize('view', $contract);

        $contract->load(['type', 'counterparty', 'paymentSchedules', 'documents']);

        return new ContractResource($contract);
    }

    public function update(UpdateContractRequest $request, Contract $contract): ContractResource
    {
        $contract = $this->contractService->update($contract, $request->validated());

        return new ContractResource($contract);
    }

    public function destroy(Contract $contract): JsonResponse
    {
        $this->authorize('delete', $contract);

        $contract->delete();

        return response()->json(null, 204);
    }

    public function submit(Contract $contract): ContractResource
    {
        $this->authorize('update', $contract);

        $contract = $this->contractService->submitForApproval($contract);

        return new ContractResource($contract);
    }

    public function approve(Contract $contract): ContractResource
    {
        $this->authorize('approve', $contract);

        $contract = $this->contractService->approve($contract, auth()->id());

        return new ContractResource($contract);
    }

    public function activate(Contract $contract): ContractResource
    {
        $this->authorize('update', $contract);

        $contract = $this->contractService->activate($contract);

        return new ContractResource($contract);
    }

    public function suspend(Request $request, Contract $contract): ContractResource
    {
        $this->authorize('update', $contract);

        $contract = $this->contractService->suspend(
            $contract,
            $request->string('reason')->value() ?? '',
        );

        return new ContractResource($contract);
    }

    public function terminate(Request $request, Contract $contract): ContractResource
    {
        $this->authorize('terminate', $contract);

        $contract = $this->contractService->terminate(
            $contract,
            $request->string('reason')->value() ?? '',
        );

        return new ContractResource($contract);
    }

    public function registerBna(Request $request, Contract $contract): ContractResource
    {
        $this->authorize('update', $contract);

        $contract = $this->contractService->registerAtBna(
            $contract,
            $request->string('registration_number')->value(),
        );

        return new ContractResource($contract);
    }

    public function expiring(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contract::class);

        $days = $request->integer('days', 30);
        $contracts = $this->contractService->getExpiringContracts($days);

        return response()->json([
            'data' => ContractResource::collection($contracts),
            'meta' => ['total' => $contracts->count(), 'alert_days' => $days],
        ]);
    }

    public function overdue(): JsonResponse
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = $this->contractService->getOverdueContracts();

        return response()->json([
            'data' => ContractResource::collection($contracts),
            'meta' => ['total' => $contracts->count()],
        ]);
    }
}