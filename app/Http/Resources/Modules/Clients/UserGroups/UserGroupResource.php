<?php

namespace App\Http\Resources\Modules\Clients\UserGroups;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGroupResource extends JsonResource
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
            'formatted_status' => $this->status?->label(),
            'status' => (bool) $this->status?->value,
            'clients_count' => $this->whenCounted('clients', function () {
                return $this->clients_count ?? 0;
            }),
        ];
    }
}
