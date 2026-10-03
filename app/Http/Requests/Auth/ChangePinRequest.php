<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['nullable', 'digits_between:4,6'],
            'new_pin' => ['required', 'digits_between:4,6', 'different:current_pin'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ];
    }
}
