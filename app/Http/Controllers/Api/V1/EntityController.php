<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Entities\Models\Entity;
use App\Domain\Entities\Services\EntityService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Entities\StoreEntityRequest;
use App\Http\Requests\Entities\UpdateEntityRequest;
use App\Http\Resources\EntityResource;
use App\Support\Enums\EntityType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EntityController extends Controller
{
    public function __construct(
        private readonly EntityService $entityService,
    ) {}

    /**
     * GET /api/v1/entities
     * Lista paginada com filtros.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Entity::class);

        $type = $request->enum('type', EntityType::class);

        $entities = $this->entityService->list(
            type: $type,
            search: $request->string('search')->value(),
            status: $request->string('status', 'active')->value(),
            perPage: $request->integer('per_page', 20),
        );

        return EntityResource::collection($entities);
    }

    /**
     * POST /api/v1/entities
     */
    public function store(StoreEntityRequest $request): JsonResponse
    {
        $entity = $this->entityService->create($request->validated());

        return (new EntityResource($entity))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/entities/{entity}
     */
    public function show(Entity $entity): EntityResource
    {
        $this->authorize('view', $entity);

        $entity->load(['documents']);

        return new EntityResource($entity);
    }

    /**
     * PUT/PATCH /api/v1/entities/{entity}
     */
    public function update(UpdateEntityRequest $request, Entity $entity): EntityResource
    {
        $entity = $this->entityService->update($entity, $request->validated());

        return new EntityResource($entity);
    }

    /**
     * DELETE /api/v1/entities/{entity}
     */
    public function destroy(Entity $entity): JsonResponse
    {
        $this->authorize('delete', $entity);

        $entity->delete(); // Soft delete

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/entities/{entity}/suspend
     */
    public function suspend(Request $request, Entity $entity): EntityResource
    {
        $this->authorize('update', $entity);

        $entity = $this->entityService->suspend(
            $entity,
            $request->string('reason')->value() ?? '',
        );

        return new EntityResource($entity);
    }

    /**
     * POST /api/v1/entities/{entity}/reactivate
     */
    public function reactivate(Entity $entity): EntityResource
    {
        $this->authorize('update', $entity);

        $entity->update(['status' => 'active']);

        return new EntityResource($entity->fresh());
    }

    /**
     * GET /api/v1/entities/compliance/alerts
     * Entidades com certidões expiradas ou a expirar.
     */
    public function complianceAlerts(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Entity::class);

        $days = $request->integer('days', 30);
        $entities = $this->entityService->getEntitiesWithExpiringCertificates($days);

        return response()->json([
            'data' => EntityResource::collection($entities),
            'meta' => [
                'total' => $entities->count(),
                'alert_days' => $days,
            ],
        ]);
    }
}