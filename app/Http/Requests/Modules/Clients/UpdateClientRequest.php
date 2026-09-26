<?php

namespace App\Http\Requests\Modules\Clients;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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

            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'email_verified_at' => ['required', 'date'],
            'mobile' => ['required', 'string', 'max:20'],
            'mobile_verified_at' => ['required', 'date'],
            'image' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'whatsapp_verified_at' => ['nullable', 'date'],
            'dob' => ['nullable', 'date', 'before:today'],
            'user_group_id' => ['nullable', 'integer', 'exists:user_groups,id'],

        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'mobile_verified_at' => data_get($this, 'mobile_verified_at') ? now() : null,
            'email_verified_at' => data_get($this, 'email_verified_at') ? now() : null,
            'whatsapp_verified_at' => data_get($this, 'whatsapp_verified_at') ? now() : null,
        ]);
    }
}
