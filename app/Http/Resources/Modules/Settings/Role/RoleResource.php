<?php

namespace App\Http\Resources\Modules\Settings\Role;

use App\Enums\Applications\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissionValue = $request->get('permission_value', 'title');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'status' => StatusEnum::fromValue($this->status)?->label(),
            'permissions' => $this->whenLoaded('permissions', function () use ($permissionValue) {
                return $this->permissions->pluck($permissionValue);
            }),
        ];
    }
}
