<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Entities\Models\Entity;
use App\Domain\Entities\Models\EntityDocument;
use App\Domain\Contracts\Models\Contract;
use App\Domain\Contracts\Models\ContractDocument;
use App\Domain\Guarantees\Models\Guarantee;
use App\Domain\Guarantees\Models\GuaranteeDocument;
use App\Http\Controllers\Controller;
use App\Http\Resources\EntityDocumentResource;
use App\Http\Resources\ContractDocumentResource;
use App\Http\Resources\GuaranteeDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUploadController extends Controller
{
    /**
     * POST /api/v1/entities/{entity}/documents/upload
     */
    public function uploadEntityDocument(Request $request, Entity $entity): JsonResponse
    {
        $this->authorize('update', $entity);

        $request->validate([
            'document' => 'required|file|max:10240', // 10MB
            'document_type' => 'required|in:agt_certificate,inss_certificate,commercial_registration,id_document,power_of_attorney,other',
            'title' => 'nullable|string|max:255',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:issued_at',
        ]);

        $file = $request->file('document');
        $path = $file->store("entities/{$entity->id}/documents", 'public');

        $document = EntityDocument::create([
            'entity_id' => $entity->id,
            'document_type' => $request->document_type,
            'title' => $request->title ?? $file->getClientOriginalName(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'issued_at' => $request->issued_at,
            'expires_at' => $request->expires_at,
            'is_current' => true,
            'uploaded_by' => auth()->id(),
        ]);

        return (new EntityDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * POST /api/v1/contracts/{contract}/documents/upload
     */
    public function uploadContractDocument(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $request->validate([
            'document' => 'required|file|max:10240',
            'document_type' => 'required|in:contract_draft,signed_contract,annex,technical_spec,measurement,delivery_note,amendment,termination',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('document');
        $path = $file->store("contracts/{$contract->id}/documents", 'public');

        $document = ContractDocument::create([
            'contract_id' => $contract->id,
            'document_type' => $request->document_type,
            'title' => $request->title ?? $file->getClientOriginalName(),
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'version' => 1,
            'is_current' => true,
            'uploaded_by' => auth()->id(),
        ]);

        return (new ContractDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * POST /api/v1/guarantees/{guarantee}/documents/upload
     */
    public function uploadGuaranteeDocument(Request $request, Guarantee $guarantee): JsonResponse
    {
        $this->authorize('update', $guarantee);

        $request->validate([
            'document' => 'required|file|max:10240',
            'document_type' => 'required|in:guarantee_certificate,bank_letter,insurance_policy,release_document,execution_document',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:issued_at',
        ]);

        $file = $request->file('document');
        $path = $file->store("guarantees/{$guarantee->id}/documents", 'public');

        $document = GuaranteeDocument::create([
            'guarantee_id' => $guarantee->id,
            'document_type' => $request->document_type,
            'title' => $request->title ?? $file->getClientOriginalName(),
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'issued_at' => $request->issued_at,
            'expires_at' => $request->expires_at,
            'uploaded_by' => auth()->id(),
        ]);

        return (new GuaranteeDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/documents/{type}/{id}/download
     * Download de qualquer documento
     */
    public function download(string $type, string $id)
    {
        $document = match($type) {
            'entity' => EntityDocument::findOrFail($id),
            'contract' => ContractDocument::findOrFail($id),
            'guarantee' => GuaranteeDocument::findOrFail($id),
            default => abort(404),
        };

        // Verificar permissões
        $parent = match($type) {
            'entity' => $document->entity,
            'contract' => $document->contract,
            'guarantee' => $document->guarantee,
        };

        $this->authorize('view', $parent);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Ficheiro não encontrado');
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->file_name
        );
    }
}