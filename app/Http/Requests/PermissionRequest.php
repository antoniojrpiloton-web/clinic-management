<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('permission') ? 'permissions.edit' : 'permissions.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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

    /**
     * @return array<string, string>
     */
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
