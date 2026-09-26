<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class AddressBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $verifiedRule = $this->isMethod('POST') ? 'nullable' : 'required';
        return [
            'type' => 'nullable|string|in:personal,business,home,office,other',
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
            'subzone_id' => ['required', 'integer', 'exists:subzones,id'],
            'flat_no' => ['required', 'string', 'max:50'],
            'house_no' => ['required', 'string', 'max:50'],
            'road_no' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'verified_at' => [$verifiedRule, 'date'],
            'network_id' => ['nullable', 'integer'],
            'verified_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'verified_by' => isset($this->verified_at) ? authId() : null,
        ]);
    }
}
