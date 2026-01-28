<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SppdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'spt_number' => $this->spt_number,
            'spd_number' => $this->spd_number,
            'destination' => $this->destination,
            'purpose' => $this->purpose,
            'departure_date' => $this->departure_date?->format('Y-m-d'),
            'return_date' => $this->return_date?->format('Y-m-d'),
            'duration' => $this->duration,
            'transport_type' => $this->transport_type,
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'invitation_number' => $this->invitation_number,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relationships
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'nip' => $this->employee->nip,
                'position' => $this->employee->position,
                'rank' => $this->employee->rank,
            ]),
            'unit' => $this->whenLoaded('unit', fn() => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'code' => $this->unit->code,
            ]),
            'budget' => $this->whenLoaded('budget', fn() => [
                'id' => $this->budget->id,
                'name' => $this->budget->name,
                'amount' => $this->budget->amount,
                'used' => $this->budget->used,
            ]),
            'costs' => $this->whenLoaded('costs', fn() => $this->costs->map(fn($cost) => [
                'id' => $cost->id,
                'category' => $cost->category,
                'description' => $cost->description,
                'amount' => $cost->amount,
            ])),
            'approvals' => $this->whenLoaded('approvals', fn() => $this->approvals->map(fn($approval) => [
                'id' => $approval->id,
                'level' => $approval->level,
                'status' => $approval->status,
                'approved_at' => $approval->approved_at?->toISOString(),
                'notes' => $approval->notes,
            ])),
        ];
    }
}
