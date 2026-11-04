<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'university_name' => ['nullable', 'string', 'max:150'],
            'university_short_name' => ['nullable', 'string', 'max:50'],
            'university_campus' => ['nullable', 'string', 'max:150'],
            'university_address' => ['nullable', 'string', 'max:500'],
            'university_president' => ['nullable', 'string', 'max:150'],
            'university_website' => ['nullable', 'url', 'max:255'],
            'clinic_name' => ['required', 'string', 'max:150'],
            'clinic_head' => ['nullable', 'string', 'max:150'],
            'clinic_license_no' => ['nullable', 'string', 'max:100'],
            'clinic_email' => ['nullable', 'email', 'max:255'],
            'clinic_phone' => ['nullable', 'string', 'max:50'],
            'clinic_address' => ['nullable', 'string', 'max:500'],
            'date_format' => ['required', Rule::in(array_keys(self::dateFormats()))],
            'table_page_length' => ['required', 'integer', Rule::in([10, 25, 50, 100])],
            'backup_enabled' => ['boolean'],
            'backup_retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'audit_log_retention_days' => ['required', 'integer', 'min:30', 'max:3650'],
        ];
    }

    public static function dateFormats(): array
    {
        return [
            'M d, Y' => now()->format('M d, Y'),
            'd/m/Y' => now()->format('d/m/Y'),
            'm/d/Y' => now()->format('m/d/Y'),
            'Y-m-d' => now()->format('Y-m-d'),
            'F j, Y' => now()->format('F j, Y'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['backup_enabled' => $this->boolean('backup_enabled')]);
    }
}
