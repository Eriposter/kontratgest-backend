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
            'document' => 'required|file|max:10240',
            'document_type' => 'required|in:agt_certificate,inss_certificate,commercial_registration,id_document,power_of_attorney,other',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:issued_at',
        ]);

        $file = $request->file('document');
        $path = $file->store("entities/{$entity->id}/documents", 'public');

        $document = EntityDocument::create([
            'entity_id' => $entity->id,
            'document_type' => $request->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'issued_at' => $request->issued_at,
            'expires_at' => $request->expires_at,
            'is_current' => true,
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'data' => new \App\Http\Resources\EntityDocumentResource($document)
        ], 201);
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
 * Upload de documento de pagamento
 * POST /api/v1/documents/payments/{payment}/upload
 */
public function uploadPaymentDocument(Request $request, string $paymentId): JsonResponse
{
    $request->validate([
        'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        'document_type' => 'required|string|in:payment_proof,invoice,receipt,contract,other',
    ]);

    $payment = \App\Domain\Payments\Models\Payment::findOrFail($paymentId);
    
    $file = $request->file('document');
    $documentType = $request->input('document_type');
    
    $fileName = $documentType . '_' . time() . '_' . Str::slug($payment->payment_number) . '.' . $file->getClientOriginalExtension();
    
    // 1. Forçar o disco 'public' ao guardar
    $path = $file->storeAs('payments/documents', $fileName, 'public');
    
    // 2. 🔥 CORREÇÃO: Forçar o disco 'public' ao gerar o URL
    $url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);

    $documents = $payment->payment_documents ?? [];
    $documents[] = [
        'id' => (string) Str::uuid(),
        'type' => $documentType,
        'title' => $file->getClientOriginalName(),
        'file_name' => $fileName,
        'file_path' => $path,
        'file_url' => $url,
        'file_size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
        'uploaded_at' => now()->toISOString(),
        'uploaded_by' => auth()->id(),
        'uploaded_by_name' => auth()->user()?->name,
    ];

    $payment->update(['payment_documents' => $documents]);

    if ($documentType === 'payment_proof') {
        $payment->update(['payment_proof_path' => $path]);
    }

    return response()->json(['data' => $documents], 201);
}

/**
 * Listar documentos de um pagamento
 * GET /api/v1/documents/payments/{payment}
 */
public function listPaymentDocuments(string $paymentId): JsonResponse
{
    $payment = \App\Domain\Payments\Models\Payment::findOrFail($paymentId);
    
    $documents = $payment->payment_documents ?? [];
    
    return response()->json([
        'data' => $documents,
    ]);
}

/**
 * Eliminar documento de pagamento
 * DELETE /api/v1/documents/payments/{payment}/{documentId}
 */
public function deletePaymentDocument(string $paymentId, string $documentId): JsonResponse
{
    $payment = \App\Domain\Payments\Models\Payment::findOrFail($paymentId);
    
    $documents = $payment->payment_documents ?? [];
    
    // 1. Encontrar o índice do documento pelo ID (UUID)
    $documentIndex = array_search($documentId, array_column($documents, 'id'));
    
    if ($documentIndex === false) {
        return response()->json(['message' => 'Documento não encontrado'], 404);
    }

    $document = $documents[$documentIndex];
    
    // 2. Eliminar o ficheiro físico (usar 'file_path' em vez de 'path')
    if (isset($document['file_path']) && Storage::disk('public')->exists($document['file_path'])) {
        Storage::disk('public')->delete($document['file_path']);
    }

    // 3. Remover do array
    array_splice($documents, $documentIndex, 1);
    
    $payment->update(['payment_documents' => $documents]);

    // 4. Se era o comprovativo principal, limpar o campo dedicado
    if (($document['type'] ?? '') === 'payment_proof' && ($payment->payment_proof_path ?? null) === $document['file_path']) {
        $payment->update(['payment_proof_path' => null]);
    }

    return response()->json(['message' => 'Documento eliminado com sucesso']);
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