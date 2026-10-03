<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'auth_id' => $this->id,
            'email' => $this->email,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'full_name' => $this->full_name,
            'unique_id' => $this->unique_id,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at ? $this->last_login_at?->toDateTimeString() : null,
        ];
    }
}
