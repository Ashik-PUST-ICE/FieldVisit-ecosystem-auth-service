<?php

namespace App\Http\Resources\Modules\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubzoneResource extends JsonResource
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
            'zone_id' => $this->zone_id,
            'title' => $this->title,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
            'zone' => $this->whenLoaded('zone', function () {
                return [
                    'id' => $this->zone?->id,
                    'title' => $this->zone?->title,
                ];
            }),
        ];
    }
}
