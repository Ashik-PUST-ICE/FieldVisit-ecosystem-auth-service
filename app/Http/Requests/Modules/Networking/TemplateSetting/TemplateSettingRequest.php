<?php

namespace App\Http\Requests\Modules\Networking\TemplateSetting;

use Illuminate\Foundation\Http\FormRequest;

class TemplateSettingRequest extends FormRequest
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
            'type' => 'required|string|max:255',
            'operational_branch_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'template_title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
