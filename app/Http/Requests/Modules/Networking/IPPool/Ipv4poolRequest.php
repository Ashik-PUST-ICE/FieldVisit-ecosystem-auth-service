<?php

namespace App\Http\Requests\Modules\Networking\IPPool;

use Illuminate\Foundation\Http\FormRequest;

class Ipv4poolRequest extends FormRequest
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
            'mikrotik_id' => 'required|integer',
            'name' => 'required|string|max:128',
            'range' => 'required|string',
            'length' => 'nullable|integer',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}
