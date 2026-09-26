<?php

namespace App\Http\Resources\Modules\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'unique_id' => $this->unique_id,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at?->toDateTimeString(),
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'permissions' => $this->whenLoaded('permissions', fn() => $this->permissions->pluck('name')),
        ];
    }
}
