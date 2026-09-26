<?php

namespace App\Http\Requests\Modules\Settings\Role;

use App\Enums\Applications\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:roles,name,'.$this->route('role'),
            'guard_name' => 'required|string|max:255',
            'status' => ['required', 'integer', Rule::enum(StatusEnum::class)],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'guard_name' => $this->guard_name ?? 'api',
            'status' => $this->status ?? StatusEnum::ACTIVE->value,
        ]);
    }
}
