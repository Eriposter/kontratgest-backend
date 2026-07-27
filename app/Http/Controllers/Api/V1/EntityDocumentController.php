<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Entities\Models\Entity;
use App\Domain\Entities\Models\EntityDocument;
use App\Http\Controllers\Controller;
use App\Http\Resources\EntityDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EntityDocumentController extends Controller
{
    /**
     * GET /api/v1/entities/{entity}/documents
     */
    public function index(Entity $entity): AnonymousResourceCollection
    {
        $this->authorize('view', $entity);

        $documents = $entity->documents()
            ->orderByDesc('is_current')
            ->orderByDesc('created_at')
            ->get();

        return EntityDocumentResource::collection($documents);
    }

    /**
     * GET /api/v1/entities/{entity}/documents/{document}
     */
    public function show(Entity $entity, EntityDocument $document): EntityDocumentResource
    {
        $this->authorize('view', $entity);

        if ($document->entity_id !== $entity->id) {
            abort(404);
        }

        return new EntityDocumentResource($document);
    }

    /**
     * DELETE /api/v1/entities/{entity}/documents/{document}
     */
    public function destroy(Entity $entity, EntityDocument $document): JsonResponse
    {
        $this->authorize('update', $entity);

        if ($document->entity_id !== $entity->id) {
            abort(404);
        }

        // Apagar ficheiro do storage (se existir)
        if ($document->file_path) {
            \Illuminate\Support\Facades\Storage::delete($document->file_path);
        }

        $document->delete();

        return response()->json(null, 204);
    }
}