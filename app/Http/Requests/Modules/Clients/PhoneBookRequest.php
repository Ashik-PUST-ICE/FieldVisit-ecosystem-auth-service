<?php

namespace App\Http\Requests\Modules\Clients;

use Illuminate\Foundation\Http\FormRequest;

class PhoneBookRequest extends FormRequest
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
            'phone_books' => ['nullable', 'array'],
            'phone_books.*.network_id' => ['nullable', 'integer'],

            'phone_books.*.type' => ['nullable', 'string', 'in:personal,work,other,mfs'],
            'phone_books.*.country_code' => ['nullable', 'string', 'max:5'],
            'phone_books.*.phone_number' => ['nullable', 'string', 'max:20'],
            'phone_books.*.phone_verified_at' => ['nullable', 'date'],
            'phone_books.*.description' => ['nullable', 'string', 'max:255'],
            'phone_books.*.status' => ['nullable', 'integer', 'in:0,1'],
            'phone_books.*.is_default' => ['nullable', 'boolean'],
            'phone_books.*.verified_at' => ['nullable', 'date'],
        ];
    }
}
