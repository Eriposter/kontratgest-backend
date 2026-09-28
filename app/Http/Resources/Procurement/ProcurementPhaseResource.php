<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementPhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'procedure_id' => $this->procedure_id,
            'phase_name' => $this->phase_name,
            'sequence_order' => $this->sequence_order,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'documents' => $this->documents,
            'notes' => $this->notes,
            'observations' => $this->observations,
            'completed_by' => $this->completed_by,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}