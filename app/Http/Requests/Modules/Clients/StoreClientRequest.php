<?php

namespace App\Http\Requests\Modules\Clients;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'basicInfo' => ['required', 'array'],
            'basicInfo.first_name' => ['required', 'string', 'max:255'],
            'basicInfo.last_name' => ['nullable', 'string', 'max:255'],
            'basicInfo.email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'basicInfo.email_verified_at' => ['nullable', 'date'],
            'basicInfo.mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'basicInfo.mobile_verified_at' => ['nullable', 'date'],
            'basicInfo.password' => ['required', 'string', 'min:6', 'confirmed'],
            'basicInfo.password_confirmation' => ['required_with:basicInfo.password', 'same:basicInfo.password'],
            'basicInfo.image' => ['nullable', 'string'],
            'basicInfo.whatsapp' => ['nullable', 'string', 'max:50'],
            'basicInfo.whatsapp_verified_at' => ['nullable', 'date'],
            'basicInfo.dob' => ['required', 'date', 'before:today'],
            'basicInfo.client_group_id' => ['required', 'integer', 'exists:user_groups,id'],

            'phones' => ['nullable', 'array'],
            'phones.*.title' => ['nullable', 'string', 'max:255'],
            'phones.*.phone_no' => ['nullable', 'string', 'max:20'],
            'phones.*.type' => ['nullable', 'string', 'in:personal,work,other,mfs,emergency'],

            'address' => ['required', 'array'],
            'address.branch_id' => ['required', 'integer', 'exists:branches,id'],
            'address.zone_id' => ['required', 'integer', 'exists:zones,id'],
            'address.subzone_id' => ['required', 'integer', 'exists:subzones,id'],
            'address.flat_no' => ['required', 'string', 'max:50'],
            'address.house_no' => ['required', 'string', 'max:50'],
            'address.road_no' => ['required', 'string', 'max:50'],
            'address.address' => ['required', 'string', 'max:255'],
            'address.postal_code' => ['required', 'string', 'max:20'],
            'address.country_id' => ['required', 'integer', 'exists:countries,id'],
            'address.state_id' => ['required', 'integer', 'exists:states,id'],
            'address.city_id' => ['required', 'integer', 'exists:cities,id'],
            'address.latitude' => ['nullable', 'numeric', 'max:50'],
            'address.longitude' => ['nullable', 'numeric', 'max:50'],

            'identification' => ['required', 'array'],
            'identification.identity_type' => ['required', 'string', 'max:100'],
            'identification.identity_number' => ['required', 'string', 'max:100'],
            'identification.document_1' => ['required', 'array'],
            'identification.document_1.url' => ['required', 'string', 'max:2048'],
            'identification.document_1.name' => ['nullable', 'string', 'max:255'],
            'identification.document_1.id' => ['nullable', 'string', 'max:255'],
            'identification.document_2' => ['nullable', 'array'],
            'identification.document_2.url' => ['nullable', 'string', 'max:2048'],
            'identification.document_2.name' => ['nullable', 'string', 'max:255'],
            'identification.document_2.id' => ['nullable', 'string', 'max:255'],

            'reference' => ['nullable', 'array'],
            'reference.reference_by' => ['nullable', 'string', 'max:255'],
            'reference.reference_advisor' => ['nullable', 'string', 'max:255'],
            'reference.client_note' => ['nullable', 'string'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'basicInfo.mobile_verified_at' => data_get($this->basicInfo, 'mobile_verified_at') ? now() : null,
            'basicInfo.email_verified_at' => data_get($this->basicInfo, 'email_verified_at') ? now() : null,
            'basicInfo.whatsapp_verified_at' => data_get($this->basicInfo, 'whatsapp_verified_at') ? now() : null,
            'basicInfo.dob' => data_get($this->basicInfo, 'dob') ? date('Y-m-d', strtotime($this->basicInfo['dob'])) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'basicInfo.mobile_verified_at.required' => 'The mobile number must be verified.',
            'basicInfo.mobile_verified_at.date' => 'The mobile verification date is not a valid date.',
        ];
    }
}
