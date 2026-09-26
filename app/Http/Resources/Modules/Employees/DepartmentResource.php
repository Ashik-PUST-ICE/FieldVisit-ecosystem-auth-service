<?php

namespace App\Http\Resources\Modules\Employees;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch'),
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
        ];
    }
}
