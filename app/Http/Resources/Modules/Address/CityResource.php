<?php

namespace App\Http\Resources\Modules\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'title' => $this->title,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
            'country' => $this->whenLoaded('country', function () {
                return new CountryResource($this->country);
            }),
            'state' => $this->whenLoaded('state', function () {
                return new StateResource($this->state);
            }),
        ];
    }
}
