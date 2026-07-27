<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuaranteeDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'title' => $this->title,
            'description' => $this->description,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'issued_at' => $this->issued_at?->toDateString(),
            'expires_at' => $this->expires_at?->toDateString(),
            'is_expired' => $this->is_expired,
            'uploaded_at' => $this->created_at->toISOString(),
        ];
    }
}