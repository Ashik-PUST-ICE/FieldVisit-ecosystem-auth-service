<?php

namespace App\Http\Resources\Microservices\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'unique_id' => $this->unique_id,
            'mobile' => $this->mobile,
            'mobile_verified_at' => $this->mobile_verified_at,
            'whatsapp' => $this->whatsapp,
            'whatsapp_verified_at' => $this->whatsapp_verified_at,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'status' => $this->status,
            'is_employee' => $this->is_employee,
        ];
    }
}
