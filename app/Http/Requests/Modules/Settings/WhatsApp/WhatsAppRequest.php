<?php

namespace App\Http\Requests\Modules\Settings\WhatsApp;

use Illuminate\Foundation\Http\FormRequest;

class WhatsAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'session' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'is_default' => 'sometimes|boolean',
            'status' => 'sometimes|integer|in:0,1',
        ];
    }
}
