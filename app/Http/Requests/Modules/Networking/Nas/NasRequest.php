<?php

namespace App\Http\Requests\Modules\Networking\Nas;

use Illuminate\Foundation\Http\FormRequest;

class NasRequest extends FormRequest
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
            'nasname' => 'required|string|max:128',
            'shortname' => 'nullable|string|max:32',
            'type' => 'nullable|string|max:30',
            'ports' => 'nullable|integer',
            'secret' => 'nullable|string|max:60',
            'server' => 'nullable|string|max:64',
            'community' => 'nullable|string|max:50',
            'api_port' => 'nullable|integer',
            'api_username' => 'nullable|string|max:64',
            'api_password' => 'nullable|string|max:64',
            'status' => 'boolean',
            'description' => 'nullable|string',
        ];
    }
}
