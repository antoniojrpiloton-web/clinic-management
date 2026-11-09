<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public const CACHE_KEY = 'app.settings';

    public const DEFAULTS = [
        'university_name' => ['group' => 'university', 'value' => 'Surigao del Norte State University'],
        'university_short_name' => ['group' => 'university', 'value' => 'SNSU'],
        'university_campus' => ['group' => 'university', 'value' => 'Main Campus, Surigao City'],
        'university_address' => ['group' => 'university', 'value' => 'Narciso Street, Surigao City, Surigao del Norte, Philippines'],
        'university_president' => ['group' => 'university', 'value' => 'Dr. Gregorio Z. Gamboa Jr.'],
        'university_website' => ['group' => 'university', 'value' => 'https://snsu.edu.ph'],
        'clinic_name' => ['group' => 'general', 'value' => 'University Health Department'],
        'clinic_head' => ['group' => 'general', 'value' => 'Dr. Maria Elena Santos, MD'],
        'clinic_license_no' => ['group' => 'general', 'value' => 'DOH-R13-CLN-2026-088'],
        'clinic_email' => ['group' => 'general', 'value' => 'clinic@snsu.edu.ph'],
        'clinic_phone' => ['group' => 'general', 'value' => '+63 (86) 826-0135'],
        'clinic_address' => ['group' => 'general', 'value' => 'Ground Floor, Student Services Center, SNSU Main Campus'],
        'date_format' => ['group' => 'general', 'value' => 'M d, Y'],
        'table_page_length' => ['group' => 'general', 'value' => '25'],
        'backup_enabled' => ['group' => 'backup', 'value' => '1'],
        'backup_retention_days' => ['group' => 'backup', 'value' => '30'],
        'audit_log_retention_days' => ['group' => 'security', 'value' => '365'],
        'logo_original' => ['group' => 'branding', 'value' => null],
        'logo_large' => ['group' => 'branding', 'value' => null],
        'logo_medium' => ['group' => 'branding', 'value' => null],
        'logo_small' => ['group' => 'branding', 'value' => null],
        'logo_favicon' => ['group' => 'branding', 'value' => null],
        'logo_transparent' => ['group' => 'branding', 'value' => null],
        'logo_seal' => ['group' => 'branding', 'value' => null],
        'logo_grayscale' => ['group' => 'branding', 'value' => null],
        'logo_watermark' => ['group' => 'branding', 'value' => null],
        'logo_original' => ['group' => 'branding', 'value' => null],
        'logo_large' => ['group' => 'branding', 'value' => null],
        'logo_medium' => ['group' => 'branding', 'value' => null],
        'logo_small' => ['group' => 'branding', 'value' => null],
        'logo_favicon' => ['group' => 'branding', 'value' => null],
        'logo_compact' => ['group' => 'branding', 'value' => null],
        'logo_print_enabled' => ['group' => 'branding', 'value' => '1'],
        'logo_print_variant' => ['group' => 'branding', 'value' => 'seal'],
        'logo_print_size' => ['group' => 'branding', 'value' => 'medium'],
        'logo_print_position' => ['group' => 'branding', 'value' => 'left'],
        'logo_primary' => ['group' => 'branding', 'value' => null],
        'logo_transparent' => ['group' => 'branding', 'value' => null],
        'logo_seal' => ['group' => 'branding', 'value' => null],
        'logo_grayscale' => ['group' => 'branding', 'value' => null],
        'logo_watermark' => ['group' => 'branding', 'value' => null],
        'logo_primary' => ['group' => 'branding', 'value' => null],
        'logo_compact' => ['group' => 'branding', 'value' => null],
        'logo_print_enabled' => ['group' => 'branding', 'value' => '1'],
        'logo_print_variant' => ['group' => 'branding', 'value' => 'seal'],
        'logo_print_size' => ['group' => 'branding', 'value' => 'medium'],
        'logo_print_position' => ['group' => 'branding', 'value' => 'left'],
        'feature_lock_display_mode' => ['group' => 'features', 'value' => 'badge'],
        'feature_dashboard' => ['group' => 'features', 'value' => '1'],
        'feature_patients' => ['group' => 'features', 'value' => '1'],
        'feature_logbook' => ['group' => 'features', 'value' => '1'],
        'feature_medical_consultations' => ['group' => 'features', 'value' => '0'],
        'feature_dental_consultations' => ['group' => 'features', 'value' => '0'],
        'feature_emergency_first_aid' => ['group' => 'features', 'value' => '0'],
        'feature_physical_examinations' => ['group' => 'features', 'value' => '0'],
        'feature_pharmacy_dispensing' => ['group' => 'features', 'value' => '0'],
        'feature_pharmacy_inventory' => ['group' => 'features', 'value' => '0'],
        'feature_reports_analytics' => ['group' => 'features', 'value' => '0'],
        'feature_administration' => ['group' => 'features', 'value' => '0'],
        'feature_configuration_backups' => ['group' => 'features', 'value' => '0'],
    ];

    protected ?array $settings = null;

    public function all(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $stored = Cache::rememberForever(self::CACHE_KEY, fn (): array => Setting::query()->pluck('value', 'key')->all());

        $defaults = array_map(fn (array $setting): ?string => $setting['value'], self::DEFAULTS);

        return $this->settings = array_merge($defaults, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['group' => self::DEFAULTS[$key]['group'] ?? 'general', 'value' => is_bool($value) ? (string) (int) $value : $value],
            );
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->settings = null;
    }
}
