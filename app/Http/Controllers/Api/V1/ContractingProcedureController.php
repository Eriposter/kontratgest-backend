<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\PAC\Models\ContractingProcedure;
use App\Domain\PAC\Models\PlanNeed;
use App\Domain\PAC\Services\ContractingProcedureService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContractingProcedureResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractingProcedureController extends Controller
{
    public function __construct(
        private readonly ContractingProcedureService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $procedures = $this->service->list(
            status: $request->string('status')->value(),
            perPage: $request->integer('per_page', 20)
        );

        return ContractingProcedureResource::collection($procedures);
    }

    public function show(string $id): ContractingProcedureResource
    {
        $procedure = ContractingProcedure::with([
            'need', 'need.plan', 'contract', 'winningEntity', 'createdBy'
        ])->findOrFail($id);

        return new ContractingProcedureResource($procedure);
    }

    public function store(Request $request): ContractingProcedureResource
    {
        $validated = $request->validate([
            'plan_need_id' => 'required|uuid|exists:plan_needs,id',
            'procedure_start_date' => 'required|date',
            'procedure_end_date' => 'required|date|after:procedure_start_date',
            'confirmed_start_date' => 'nullable|date',
            'confirmed_end_date' => 'nullable|date|after:confirmed_start_date',
            'notes' => 'nullable|string',
        ]);

        $need = PlanNeed::findOrFail($validated['plan_need_id']);

        // Verificar se já existe procedimento para esta necessidade
        if ($this->service->findByNeed($need->id)) {
            throw new \Exception('Esta necessidade já possui um procedimento de contratação.');
        }

        $procedure = $this->service->create($need, $validated);

        return new ContractingProcedureResource($procedure);
    }

    public function update(Request $request, string $id): ContractingProcedureResource
    {
        $procedure = ContractingProcedure::findOrFail($id);

        $validated = $request->validate([
            'procedure_start_date' => 'sometimes|date',
            'procedure_end_date' => 'sometimes|date|after:procedure_start_date',
            'confirmed_start_date' => 'nullable|date',
            'confirmed_end_date' => 'nullable|date|after:confirmed_start_date',
            'notes' => 'nullable|string',
        ]);

        $procedure = $this->service->update($procedure, $validated);

        return new ContractingProcedureResource($procedure);
    }

    public function start(string $id): ContractingProcedureResource
    {
        $procedure = ContractingProcedure::findOrFail($id);
        return new ContractingProcedureResource($this->service->start($procedure));
    }

    public function moveToEvaluation(string $id): ContractingProcedureResource
    {
        $procedure = ContractingProcedure::findOrFail($id);
        return new ContractingProcedureResource($this->service->moveToEvaluation($procedure));
    }

    public function complete(Request $request, string $id): ContractingProcedureResource
    {
        $procedure = ContractingProcedure::findOrFail($id);

        $validated = $request->validate([
            'winning_entity_id' => 'required|uuid|exists:entities,id',
            'adjudication_notes' => 'nullable|string',
        ]);

        $procedure = $this->service->complete(
            $procedure,
            $validated['winning_entity_id'],
            $validated['adjudication_notes'] ?? null
        );

        return new ContractingProcedureResource($procedure);
    }

    public function cancel(Request $request, string $id): ContractingProcedureResource
    {
        $procedure = ContractingProcedure::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $procedure = $this->service->cancel($procedure, $validated['reason']);

        return new ContractingProcedureResource($procedure);
    }
}