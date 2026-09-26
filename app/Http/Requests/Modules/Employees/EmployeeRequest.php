<?php

namespace App\Http\Requests\Modules\Employees;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $passwordRule = $this->isMethod('POST') ? 'required' : 'nullable';
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $this->route('employee')],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile,' . $this->route('employee')],
            'password' => [$passwordRule, 'string', 'min:6', 'confirmed'],
            'image' => ['nullable', 'string', 'max:2048'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'integer', 'exists:roles,id'],
            'is_employee' => ['nullable', 'boolean'],
            'unique_id' => ['required', 'string', 'max:20', 'unique:users,unique_id,' . $this->route('employee')],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'designation_id' => ['required', 'integer', 'exists:designations,id'],
            'is_employee' => ['required', 'boolean']
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_employee' => true,
        ]);
    }
}
