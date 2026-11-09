<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Services\AuditLogger;
use App\Services\LogoService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:settings.manage')];
    }

    public function edit(SettingService $settings): View
    {
        return view('settings.edit', [
            'settings' => $settings->all(),
            'dateFormats' => UpdateSettingsRequest::dateFormats(),
        ]);
    }

    public function update(
        UpdateSettingsRequest $request,
        SettingService $settings,
        AuditLogger $auditLogger,
        LogoService $logoService
    ): RedirectResponse {
        $old = $settings->all();
        $new = $request->validated();

        $uploadedFile = $request->file('logo') ?? ($request->file('school_logo') ?? $request->file('logo_primary'));

        if ($request->boolean('remove_logo') || $request->boolean('remove_logo_primary')) {
            $logoService->deleteExistingVariants($old);
            foreach (LogoService::VARIANT_KEYS as $key) {
                $new[$key] = null;
            }
        } elseif ($uploadedFile) {
            $logoService->deleteExistingVariants($old);
            $variants = $logoService->generateVariants($uploadedFile);
            foreach ($variants as $key => $path) {
                $new[$key] = $path;
            }
        }

        unset($new['logo'], $new['school_logo'], $new['remove_logo'], $new['remove_logo_primary']);

        $settings->set($new);

        $changed = array_keys(array_diff_assoc(array_map('strval', $new), array_map('strval', array_intersect_key($old, $new))));

        if ($changed !== []) {
            $auditLogger->log('updated', 'Updated application settings and generated logo variants', properties: [
                'old' => array_intersect_key($old, array_flip($changed)),
                'new' => array_intersect_key($new, array_flip($changed)),
            ]);
        }

        return redirect()->route('settings.edit')->with('success', 'Settings saved and logo variants generated successfully.');
    }
}
