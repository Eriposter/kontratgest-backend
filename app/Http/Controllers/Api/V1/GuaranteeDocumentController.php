<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Guarantees\Models\Guarantee; // Ajusta o namespace se for diferente
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class GuaranteeDocumentController extends Controller
{
    /**
     * Listar documentos da caução
     */
    public function index(Guarantee $guarantee): JsonResponse
    {
        $this->authorize('view', $guarantee);

        // Assume que o modelo Guarantee tem uma relação 'documents'
        $documents = $guarantee->documents()->orderByDesc('created_at')->get();

        return response()->json([
            'data' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'title' => $doc->title ?? $doc->file_name,
                    'file_name' => $doc->file_name,
                    'file_size' => $doc->file_size,
                    'mime_type' => $doc->mime_type,
                    'issued_at' => $doc->issued_at?->toDateString(),
                    'expires_at' => $doc->expires_at?->toDateString(),
                    'is_current' => $doc->is_current ?? true,
                    'is_expired' => $doc->expires_at ? $doc->expires_at->isPast() : false,
                    'uploaded_at' => $doc->created_at->toISOString(),
                ];
            })
        ]);
    }

    /**
     * Fazer upload de um documento
     */
    public function store(Request $request, Guarantee $guarantee): JsonResponse
    {
        $this->authorize('update', $guarantee);

        $request->validate([
            'document' => 'required|file|max:10240', // 10MB
            'document_type' => 'required|string',
        ]);

        $file = $request->file('document');
        $path = $file->store("guarantees/{$guarantee->id}/documents", 'public');

        $document = $guarantee->documents()->create([
            'document_type' => $request->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'data' => [
                'id' => $document->id,
                'document_type' => $document->document_type,
                'title' => $document->file_name,
                'file_name' => $document->file_name,
                'file_size' => $document->file_size,
                'mime_type' => $document->mime_type,
                'uploaded_at' => $document->created_at->toISOString(),
            ]
        ], 201);
    }

    /**
     * Eliminar um documento
     */
    public function destroy(Guarantee $guarantee, string $documentId): JsonResponse
    {
        $this->authorize('update', $guarantee);

        $document = $guarantee->documents()->findOrFail($documentId);

        // Apagar o ficheiro do storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json(null, 204);
    }
}