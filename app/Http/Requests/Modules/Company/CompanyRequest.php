<?php

namespace App\Http\Requests\Modules\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('company') instanceof Company;

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', $isUpdate ? 'unique:companies,slug,' . $this->route('company')->id : 'unique:companies,slug'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
