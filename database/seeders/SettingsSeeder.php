<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed default application settings without overwriting existing values.
     */
    public function run(): void
    {
        foreach (SettingService::DEFAULTS as $key => $setting) {
            Setting::firstOrCreate(['key' => $key], $setting);
        }

        app(SettingService::class)->flush();
    }
}
