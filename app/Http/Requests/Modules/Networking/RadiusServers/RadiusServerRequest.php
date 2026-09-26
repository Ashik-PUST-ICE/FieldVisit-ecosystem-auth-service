<?php

namespace App\Http\Requests\Modules\Networking\RadiusServers;

use Illuminate\Foundation\Http\FormRequest;

class RadiusServerRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'host' => 'required|ip',
            'secret' => 'required|string',
            'port' => 'nullable|integer|min:1|max:65535',
            'acct_port' => 'nullable|integer|min:1|max:65535',
            'description' => 'nullable|string',
        ];
    }
}
