<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorageSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', Rule::in(['local', 's3', 'gcs', 'azure'])],
            'credentials' => ['nullable', 'array'],
            'credentials.access_key' => ['nullable', 'string', 'max:255'],
            'credentials.secret_key' => ['nullable', 'string', 'max:2048'],
            'credentials.region' => ['nullable', 'string', 'max:100'],
            'credentials.bucket' => ['nullable', 'string', 'max:255'],
            'credentials.endpoint' => ['nullable', 'url', 'max:1000'],
            'credentials.project_id' => ['nullable', 'string', 'max:255'],
            'credentials.service_account_json' => ['nullable', 'string', 'max:20000'],
            'credentials.container' => ['nullable', 'string', 'max:255'],
            'credentials.account_name' => ['nullable', 'string', 'max:255'],
            'credentials.account_key' => ['nullable', 'string', 'max:2048'],
            'root' => ['nullable', 'string', 'max:255'],
            'public_url' => ['nullable', 'url', 'max:1000'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
