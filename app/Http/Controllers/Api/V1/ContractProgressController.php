<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Services\ContractProgressService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractProgressController extends Controller
{
    public function __construct(
        private readonly ContractProgressService $progressService
    ) {}

    /**
     * GET /api/v1/contracts/{contract}/progress
     */
    public function show(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $progress = $this->progressService->getCurrentProgress($contract);
        $history = $this->progressService->getProgressHistory($contract);

        return response()->json([
            'data' => [
                'current' => $progress,
                'history' => $history->map(function ($update) {
                    return [
                        'id' => $update->id,
                        'percentage' => (float) $update->progress_percentage,
                        'type' => $update->update_type,
                        'notes' => $update->notes,
                        'evidence' => $update->evidence,
                        'updated_at' => $update->created_at->toISOString(),
                        'updated_by' => $update->updated_by,
                    ];
                }),
            ],
        ]);
    }

    /**
     * POST /api/v1/contracts/{contract}/progress
     */
    public function update(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $request->validate([
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'url',
        ]);

        $update = $this->progressService->updateProgress(
            $contract,
            (float) $request->progress_percentage,
            $request->notes ?? '',
            $request->evidence ?? [],
            auth()->id(),
            'manual'
        );

        return response()->json([
            'message' => 'Progresso atualizado com sucesso!',
            'data' => [
                'id' => $update->id,
                'percentage' => (float) $update->progress_percentage,
                'notes' => $update->notes,
                'updated_at' => $update->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * POST /api/v1/contracts/{contract}/progress/calculate
     * Recalcular progresso automático baseado em pagamentos.
     */
    public function calculate(Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $progress = $this->progressService->calculateAutomaticProgress($contract);

        $update = $this->progressService->updateProgress(
            $contract,
            $progress,
            'Progresso recalculado automaticamente com base nos pagamentos.',
            [],
            auth()->id(),
            'automatic'
        );

        return response()->json([
            'message' => 'Progresso recalculado com sucesso!',
            'data' => [
                'percentage' => (float) $update->progress_percentage,
                'updated_at' => $update->created_at->toISOString(),
            ],
        ]);
    }
}