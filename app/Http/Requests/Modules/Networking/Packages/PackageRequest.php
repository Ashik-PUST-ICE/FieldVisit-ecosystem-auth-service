<?php

namespace App\Http\Requests\Modules\Networking\Packages;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:128',
            'type' => 'required|string|max:64',
            'bandwidth' => 'required|integer|min:1',
            'bandwidth_unit' => 'required|string|in:Mbps,Gbps,Kbps',
            'is_available_portal' => 'boolean',
            'is_available_change_request' => 'boolean',
            'router_profile_name' => 'required|string|max:128',
            'local_address' => 'nullable|ip',
            'remote_address' => 'nullable|ip',
            'rate_limit' => 'nullable|string|max:64',
            'price' => 'required|numeric|min:0',
            'vat' => 'nullable|numeric|min:0|max:100',
            'status' => 'boolean',
            'details' => 'nullable|array',
            'details.*.description' => 'required|array',
            'details.*.description.*' => 'required|string|max:500',
            'details.*.position' => 'nullable|integer|min:0',
        ];
    }
}
