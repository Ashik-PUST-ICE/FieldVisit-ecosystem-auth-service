<?php

namespace App\Http\Resources\Modules\Clients\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientWithoutConnectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $address = $this->addressBooks->first();

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'image' => isset($this->image) ? asset($this->image) : null,
            'full_address' => $address ? $address->full_address : null,
            'profile_percentage' => $this->profile_percentage,
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
            'network_id' => $this->network_id,
            'package_id' => $this->package_id,
            'cid' => $this->cid,
            'next_cycle' => $this->next_cycle,
            'status' => $this->generateStatus(),
        ];
    }

    protected function generateStatus()
    {
        if ($this->cid && $this->next_cycle) {
            return 'Completed';
        } elseif ($this->cid && ! $this->next_cycle) {
            return 'Billing Incomplete';
        } elseif (! $this->cid && $this->next_cycle) {
            return 'Connection Incomplete';
        } else {
            return 'Incomplete';
        }
    }
}
