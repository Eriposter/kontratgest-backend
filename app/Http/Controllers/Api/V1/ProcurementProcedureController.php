<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Procurement\Models\ProcurementProcedure;
use App\Domain\Procurement\Services\ProcurementProcedureService;
use App\Domain\PAC\Models\PlanNeed;
use App\Http\Controllers\Controller;
use App\Http\Resources\Procurement\ProcurementProcedureResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProcurementProcedureController extends Controller
{
    public function __construct(
        private readonly ProcurementProcedureService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $procedures = $this->service->list(
            status: $request->string('status')->value(),
            type: $request->string('type')->value(),
            perPage: $request->integer('per_page', 20)
        );

        return ProcurementProcedureResource::collection($procedures);
    }

    public function show(string $id): ProcurementProcedureResource
    {
        $procedure = $this->service->find($id);
        return new ProcurementProcedureResource($procedure);
    }

    public function store(Request $request): ProcurementProcedureResource
    {
        $validated = $request->validate([
            'plan_need_id' => 'required|uuid|exists:plan_needs,id',
            'procedure_type' => 'required|in:cp,clpq,clc,pcs,pde,pce',
            'procedure_start_date' => 'required|date',
            'procedure_end_date' => 'required|date|after:procedure_start_date',
            'confirmed_start_date' => 'nullable|date',
            'confirmed_end_date' => 'nullable|date|after:confirmed_start_date',
            'notes' => 'nullable|string',
        ]);

        $need = PlanNeed::findOrFail($validated['plan_need_id']);

        if ($this->service->findByNeed($need->id)) {
            throw new \Exception('Esta necessidade já possui um procedimento de contratação.');
        }

        $procedure = $this->service->create($need, $validated);

        return new ProcurementProcedureResource($procedure);
    }

    public function start(string $id): ProcurementProcedureResource
    {
        $procedure = ProcurementProcedure::findOrFail($id);
        return new ProcurementProcedureResource($this->service->start($procedure));
    }

    public function completePhase(Request $request, string $id, string $phaseId): JsonResponse
    {
        $procedure = ProcurementProcedure::findOrFail($id);

        $validated = $request->validate([
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        $phase = $this->service->completePhase($procedure, $phaseId, $validated);

        return response()->json([
            'data' => $phase,
            'message' => 'Fase concluída com sucesso'
        ]);
    }

    public function complete(Request $request, string $id): ProcurementProcedureResource
    {
        $procedure = ProcurementProcedure::findOrFail($id);

        $validated = $request->validate([
            'winning_entity_id' => 'required|uuid|exists:entities,id',
            'adjudication_notes' => 'nullable|string',
        ]);

        $procedure = $this->service->complete(
            $procedure,
            $validated['winning_entity_id'],
            $validated['adjudication_notes'] ?? null
        );

        return new ProcurementProcedureResource($procedure);
    }

    public function cancel(Request $request, string $id): ProcurementProcedureResource
    {
        $procedure = ProcurementProcedure::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $procedure = $this->service->cancel($procedure, $validated['reason']);

        return new ProcurementProcedureResource($procedure);
    }
}