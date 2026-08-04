<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\PAC\Models\AnnualContractPlan;
use App\Domain\PAC\Models\PlanNeed;
use App\Domain\PAC\Services\PACService;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnnualContractPlanResource;
use App\Http\Resources\PlanNeedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PACController extends Controller
{
    public function __construct(
        private readonly PACService $pacService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $plans = $this->pacService->list(
            filters: $request->only(['year', 'status', 'search']),
            perPage: $request->integer('per_page', 20)
        );

        return AnnualContractPlanResource::collection($plans);
    }

    public function show(string $id): AnnualContractPlanResource
{
    $plan = $this->pacService->find($id);
    
    // Carregar contratos das necessidades
    $plan->load(['needs.contract']);
    
    return new AnnualContractPlanResource($plan);
}

    public function store(Request $request): AnnualContractPlanResource
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $plan = $this->pacService->create($validated);
        return new AnnualContractPlanResource($plan);
    }

    public function update(Request $request, string $id): AnnualContractPlanResource
    {
        $plan = $this->pacService->find($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $plan = $this->pacService->update($plan, $validated);
        return new AnnualContractPlanResource($plan);
    }

    public function submit(string $id): AnnualContractPlanResource
    {
        $plan = $this->pacService->find($id);
        $plan = $this->pacService->submit($plan);
        return new AnnualContractPlanResource($plan);
    }

    public function approve(string $id): AnnualContractPlanResource
    {
        $plan = $this->pacService->find($id);
        $plan = $this->pacService->approve($plan);
        return new AnnualContractPlanResource($plan);
    }

    public function cancel(string $id): AnnualContractPlanResource
    {
        $plan = $this->pacService->find($id);
        $plan = $this->pacService->cancel($plan);
        return new AnnualContractPlanResource($plan);
    }

    // ─── Necessidades ──────────────────────────────────────

    public function addNeed(Request $request, string $planId): PlanNeedResource
    {
        $plan = $this->pacService->find($planId);

        $validated = $request->validate([
            'contract_type' => 'required|in:works,goods,services,consultancy',
            'procedure_type' => 'required|in:dynamic_electronic,invitation,limited_tender,direct_award',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'justification' => 'nullable|string',
            'estimated_amount' => 'required|numeric|min:0',
            'priority' => 'required|in:high,medium,low',
            'planned_quarter' => 'nullable|integer|min:1|max:4',
        ]);

        $need = $this->pacService->addNeed($plan, $validated);
        return new PlanNeedResource($need);
    }

    public function updateNeed(Request $request, string $needId): PlanNeedResource
    {
        $need = PlanNeed::findOrFail($needId);

        $validated = $request->validate([
            'contract_type' => 'sometimes|in:works,goods,services,consultancy',
            'procedure_type' => 'sometimes|in:dynamic_electronic,invitation,limited_tender,direct_award',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'justification' => 'nullable|string',
            'estimated_amount' => 'sometimes|numeric|min:0',
            'priority' => 'sometimes|in:high,medium,low',
            'planned_quarter' => 'nullable|integer|min:1|max:4',
        ]);

        $need = $this->pacService->updateNeed($need, $validated);
        return new PlanNeedResource($need);
    }

    public function deleteNeed(string $needId): JsonResponse
    {
        $need = PlanNeed::findOrFail($needId);
        $this->pacService->deleteNeed($need);
        return response()->json(null, 204);
    }

        public function generateContract(PlanNeed $need, Request $request): JsonResponse
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'object' => 'nullable|string',
        'contract_type' => 'required|string',
        'counterparty_id' => 'required|uuid|exists:entities,id',
        'total_amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'signature_date' => 'nullable|date',
        'vat_rate' => 'nullable|numeric|min:0|max:100',
        'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
        'payment_model' => 'nullable|string|in:single,installment,measurement,consignment,milestone',
        'notes' => 'nullable|string',
    ]);

    // 🔥 GARANTIR QUE PAYMENT_MODEL TEM VALOR PADRÃO
    if (empty($validated['payment_model'])) {
        $validated['payment_model'] = 'single';
    }

    $contract = $this->pacService->generateContract($need, $validated);

    return response()->json([
        'data' => [
            'id' => $contract->id,
            'contract_number' => $contract->contract_number,
            'title' => $contract->title,
        ]
    ], 201);
}

    public function getAvailableNeeds(): JsonResponse
    {
        $needs = PlanNeed::whereHas('plan', function ($query) {
            $query->where('company_id', current_company()->id)
                  ->where('status', 'approved');
        })
        ->where('status', 'planned')
        ->whereNull('contract_id')
        ->with('plan')
        ->get();

        return response()->json([
            'data' => $needs->map(function ($need) {
                return [
                    'id' => $need->id,
                    'title' => $need->title,
                    'description' => $need->description,
                    'estimated_amount' => (float) $need->estimated_amount,
                    'contract_type' => $need->contract_type,
                    'contract_type_label' => $need->contract_type_label,
                    'procedure_type' => $need->procedure_type,
                    'procedure_type_label' => $need->procedure_type_label,
                    'priority' => $need->priority,
                    'priority_label' => $need->priority_label,
                    'plan_year' => $need->plan->year,
                    'plan_title' => $need->plan->title,
                ];
            })
        ]);
    }
}