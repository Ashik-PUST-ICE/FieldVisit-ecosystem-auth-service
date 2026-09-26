<?php

namespace App\Http\Resources\Modules\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'short_code' => $this->short_code,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
        ];
    }
}
