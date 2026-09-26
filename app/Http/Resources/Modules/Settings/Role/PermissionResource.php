<?php

namespace App\Http\Resources\Modules\Settings\Role;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PermissionResource extends JsonResource
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
            'title' => $this->title,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'updated_at' => $this->updated_at?->format('F j Y, g:i A'),
            'roles' => $this->whenLoaded('roles', function () {
                //  return $this->roles->pluck('name');
                return $this->roles->pluck('name')?->filter(fn ($role) => $role !== 'special-super-admin' && $role !== 'super-admin')
                    ->map(fn ($role) => $this->formatedRole($role));
            }),
        ];
    }

    private function formatedRole($name): string
    {
        return Str::of($name)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}
