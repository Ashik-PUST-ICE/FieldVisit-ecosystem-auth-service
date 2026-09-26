<?php

namespace App\Http\Resources\Modules\Clients\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'mobile' => $this->mobile,
            'mobile_verified_at' => $this->mobile_verified_at,
            'whatsapp' => $this->whatsapp,
            'whatsapp_verified_at' => $this->whatsapp_verified_at,
            'image' => $this->image,
            'unique_id' => $this->unique_id,

            // client relation
            'client' => [
                'id' => $this->client->id,
                'dob' => $this->client->dob,
                'user_group_id' => $this->client->user_group_id,
                'note' => $this->client->note,
            ],

            // address relation
            'address' => $this->addressBooks->map(function ($address) {
                return [
                    'id' => $address->id,
                    'type' => $address->type,
                    'flat_no' => $address->flat_no,
                    'house_no' => $address->house_no,
                    'road_no' => $address->road_no,
                    'address' => $address->address,
                    'country_id' => $address->country_id,
                    'state_id' => $address->state_id,
                    'city_id' => $address->city_id,
                    'branch_id' => $address->branch_id,
                    'zone_id' => $address->zone_id,
                    'subzone_id' => $address->subzone_id,
                    'postal_code' => $address->postal_code,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                ];
            }),

            // identities relation
            'user_identities' => $this->userIdentities->map(function ($identity) {
                return [
                    'id' => $identity->id,
                    'identity_type' => $identity->identity_type,
                    'identity_number' => $identity->identity_number,
                    'document_1' => $identity->document_1,
                    'document_2' => $identity->document_2,
                    'verified_by' => $identity->verified_by,
                    'verified_at' => $identity->verified_at,
                    'status' => $identity->status,
                ];
            }),

            'phone_books' => $this->phoneBooks->map(function ($phone) {
                return [
                    'id' => $phone->id,
                    'user_id' => $phone->user_id,
                    'network_id' => $phone->network_id,
                    'type' => $phone->type,
                    'country_code' => $phone->country_code,
                    'phone_number' => $phone->phone_number,
                    'phone_verified_at' => $phone->phone_verified_at,
                    'description' => $phone->description,
                    'status' => $phone->status,
                    'is_default' => (bool) $phone->is_default,
                    'verified_at' => $phone->verified_at,
                    'created_at' => $phone->created_at,
                    'updated_at' => $phone->updated_at,
                ];
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
