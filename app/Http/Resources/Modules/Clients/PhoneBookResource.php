<?php

namespace App\Http\Resources\Modules\Clients;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhoneBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'network_id' => $this->network_id,
            'type' => $this->type,
            'country_code' => $this->country_code,
            'phone_number' => $this->phone_number,
            'phone_verified_at' => $this->phone_verified_at,
            'description' => $this->description,
            'status' => $this->status,
            'is_default' => (bool) $this->is_default,
            'verified_at' => $this->verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
