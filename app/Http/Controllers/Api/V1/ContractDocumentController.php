<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Models\ContractDocument;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContractDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractDocumentController extends Controller
{
    /**
     * GET /api/v1/contracts/{contract}/documents
     */
    public function index(Contract $contract): AnonymousResourceCollection
    {
        $this->authorize('view', $contract);

        $documents = $contract->documents()
            ->orderByDesc('is_current')
            ->orderByDesc('version')
            ->orderByDesc('created_at')
            ->get();

        return ContractDocumentResource::collection($documents);
    }

    /**
     * GET /api/v1/contracts/{contract}/documents/{document}
     */
    public function show(Contract $contract, ContractDocument $document): ContractDocumentResource
    {
        $this->authorize('view', $contract);

        if ($document->contract_id !== $contract->id) {
            abort(404);
        }

        return new ContractDocumentResource($document);
    }

    /**
     * DELETE /api/v1/contracts/{contract}/documents/{document}
     */
    public function destroy(Contract $contract, ContractDocument $document): JsonResponse
    {
        $this->authorize('update', $contract);

        if ($document->contract_id !== $contract->id) {
            abort(404);
        }

        if ($document->file_path) {
            \Illuminate\Support\Facades\Storage::delete($document->file_path);
        }

        $document->delete();

        return response()->json(null, 204);
    }
}