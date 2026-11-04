<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public const CACHE_KEY = 'app.settings';

    /**
     * Default values used when a setting has not been stored yet.
     *
     * @var array<string, array{group: string, value: string|null}>
     */
    public const DEFAULTS = [
        'clinic_name' => ['group' => 'general', 'value' => 'NVC Clinic'],
        'clinic_email' => ['group' => 'general', 'value' => null],
        'clinic_phone' => ['group' => 'general', 'value' => null],
        'clinic_address' => ['group' => 'general', 'value' => null],
        'date_format' => ['group' => 'general', 'value' => 'M d, Y'],
        'table_page_length' => ['group' => 'general', 'value' => '25'],
        'backup_enabled' => ['group' => 'backup', 'value' => '1'],
        'backup_retention_days' => ['group' => 'backup', 'value' => '30'],
        'audit_log_retention_days' => ['group' => 'security', 'value' => '365'],
    ];

    /**
     * Request-level memoized settings.
     *
     * @var array<string, string|null>|null
     */
    protected ?array $settings = null;

    /**
     * Get every setting merged with defaults.
     *
     * @return array<string, string|null>
     */
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

    /**
     * Persist multiple settings and flush the cache.
     *
     * @param  array<string, mixed>  $values
     */
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
