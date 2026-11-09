<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('permission') ? 'permissions.edit' : 'permissions.create');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9-]+\.[a-z0-9-]+$/',
                Rule::unique('permissions', 'name')->ignore($this->route('permission')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Use the "module.action" format with lowercase letters, numbers and dashes (e.g. patients.view).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => strtolower(trim((string) $this->input('name')))]);
    }
}
