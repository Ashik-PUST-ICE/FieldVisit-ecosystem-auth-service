<?php

namespace App\Http\Requests\Modules\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('user') instanceof User;

        return [
            'first_name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [$isUpdate ? 'sometimes' : 'required', 'email', 'max:255', $isUpdate ? 'unique:users,email,' . $this->route('user')->id : 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'status' => ['nullable', 'in:0,1'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
