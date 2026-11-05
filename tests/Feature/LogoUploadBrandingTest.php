<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LogoService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogoUploadBrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_upload_single_logo_and_system_auto_generates_all_variants_and_sizes(): void
    {
        Storage::fake('public');
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $singleMasterLogo = UploadedFile::fake()->image('master_school_logo.png', 800, 800);

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'university_name' => 'Surigao del Norte State University',
            'university_short_name' => 'SNSU',
            'clinic_name' => 'SNSU Health Services Clinic',
            'clinic_email' => 'clinic@snsu.edu.ph',
            'clinic_phone' => '+63 912 345 6789',
            'date_format' => 'M d, Y',
            'table_page_length' => 25,
            'backup_retention_days' => 30,
            'audit_log_retention_days' => 365,
            'logo' => $singleMasterLogo,
            'logo_print_enabled' => '1',
            'logo_print_variant' => 'seal',
            'logo_print_size' => 'large',
            'logo_print_position' => 'dual',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHas('success');

        $settings = app(SettingService::class)->all();

        $this->assertNotNull($settings['logo_original']);
        $this->assertNotNull($settings['logo_large']);
        $this->assertNotNull($settings['logo_medium']);
        $this->assertNotNull($settings['logo_small']);
        $this->assertNotNull($settings['logo_favicon']);
        $this->assertNotNull($settings['logo_transparent']);
        $this->assertNotNull($settings['logo_seal']);
        $this->assertNotNull($settings['logo_grayscale']);
        $this->assertNotNull($settings['logo_watermark']);
        $this->assertSame('large', $settings['logo_print_size']);
        $this->assertSame('dual', $settings['logo_print_position']);

        foreach (LogoService::VARIANT_KEYS as $variantKey) {
            $this->assertNotEmpty($settings[$variantKey]);
            Storage::disk('public')->assertExists($settings[$variantKey]);
        }
    }

    public function test_admin_can_remove_uploaded_logo_and_all_generated_variants_are_purged(): void
    {
        Storage::fake('public');
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();
        $logoService = app(LogoService::class);

        $file = UploadedFile::fake()->image('test_logo.png', 400, 400);
        $generated = $logoService->generateVariants($file);
        app(SettingService::class)->set($generated);

        foreach (LogoService::VARIANT_KEYS as $key) {
            if (! empty($generated[$key])) {
                Storage::disk('public')->assertExists($generated[$key]);
            }
        }

        $response = $this->actingAs($admin)->put(route('settings.update'), [
            'university_name' => 'Surigao del Norte State University',
            'clinic_name' => 'SNSU Health Services Clinic',
            'date_format' => 'M d, Y',
            'table_page_length' => 25,
            'backup_retention_days' => 30,
            'audit_log_retention_days' => 365,
            'remove_logo' => '1',
        ]);

        $response->assertRedirect(route('settings.edit'));

        $settings = app(SettingService::class)->all();
        foreach (LogoService::VARIANT_KEYS as $key) {
            $this->assertNull($settings[$key]);
            if (! empty($generated[$key])) {
                Storage::disk('public')->assertMissing($generated[$key]);
            }
        }
    }

    public function test_settings_page_displays_single_upload_and_auto_variants_showcase(): void
    {
        $admin = User::role(User::SUPER_ADMIN_ROLE)->first();

        $response = $this->actingAs($admin)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertSee('School &amp; Clinic Logo &amp; Auto-Generated Variants', false);
        $response->assertSee('Upload Master School / Clinic Logo');
        $response->assertSee('Auto-Generated Variants');
        $response->assertSee('PDF Document Branding &amp; Sizing Configuration', false);
        $response->assertSee('Header Logo Sizing');
        $response->assertSee('Header Placement');
    }
}
