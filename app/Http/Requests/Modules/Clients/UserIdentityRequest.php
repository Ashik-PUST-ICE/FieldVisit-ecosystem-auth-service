<?php

namespace App\Http\Requests\Modules\Clients;

use Illuminate\Foundation\Http\FormRequest;

class UserIdentityRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'identity_type' => 'nullable|string|max:100',
            'identity_number' => 'nullable|string|max:100',
            'document_1' => 'nullable|string|max:255',
            'document_2' => 'nullable|string|max:255',
            'status' => 'nullable|integer|in:0,1',
            'verified_at' => 'nullable|date',
            'verified_by' => 'nullable|integer|exists:users,id',
        ];
    }
}
