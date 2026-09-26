<?php

namespace App\Http\Resources\Modules\Clients\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientListResource extends JsonResource
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
            'user_id' => $this->user_id,
            'network_id' => $this->network_id,
            'full_name' => $this->user?->full_name ?? $this->full_name,
            'email' => $this->user?->email ?? $this->email,
            'mobile' => $this->user?->mobile ?? $this->mobile,
            'image' => isset($this->user?->image) ? asset($this->user?->image) : null,
            'profile_percentage' => $this->user?->profile_percentage ?? null,
            'cid' => $this->cid,
            'pppoe_username' => $this->pppoe_username,
            'ip_address' => $this->ip_address,
            'package' => $this->package_name,
            'billing_amount' => $this->billing_amount,
            'next_cycle' => $this->next_cycle?->format('F j, Y'),
            'is_enabled_vat' => $this->is_enabled_vat,
            'is_auto_suspend' => $this->is_auto_suspend,
            'mikrotik_title' => $this->mikrotik['shortname'] ?? null,
            'mikrotik_ip' => $this->mikrotik['nasname'] ?? null,
            'status' => $this->network_status?->label() ?? '',
            'network_status' => $this->network_status?->value ?? null,
        ];
    }
}
