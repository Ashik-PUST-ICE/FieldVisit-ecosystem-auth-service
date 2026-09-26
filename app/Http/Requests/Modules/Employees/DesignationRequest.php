<?php

namespace App\Http\Requests\Modules\Employees;

use Illuminate\Foundation\Http\FormRequest;

class DesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:designations,name,' . $this->route('designation')],
            'short_name' => ['nullable', 'string', 'max:50'],
          
        ];
    }
}
