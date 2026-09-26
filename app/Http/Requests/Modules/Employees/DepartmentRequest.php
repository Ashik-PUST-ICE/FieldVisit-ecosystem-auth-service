<?php

namespace App\Http\Requests\Modules\Employees;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $this->route('department')],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
