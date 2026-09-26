<?php

namespace App\Http\Resources\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressBookResource extends JsonResource
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
            'network_id' => $this->network_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'flat_no' => $this->flat_no,
            'house_no' => $this->house_no,
            'road_no' => $this->road_no,
            'address' => $this->address,
            'police_station' => $this->police_station,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'verified_at' => $this->verified_at,
        ];
    }
}
