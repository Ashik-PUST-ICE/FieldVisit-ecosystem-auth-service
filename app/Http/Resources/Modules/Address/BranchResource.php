<?php

namespace App\Http\Resources\Modules\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
            'name' => $this->name,
            'city_id' => $this->city_id,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
            'city' => $this->whenLoaded('city', function () {
                return new CityResource($this->city);
            }),
        ];
    }
}
