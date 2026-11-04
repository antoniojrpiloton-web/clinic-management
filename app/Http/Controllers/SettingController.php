<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SettingController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:settings.manage')];
    }

    /**
     * Show the settings form.
     */
    public function edit(SettingService $settings): View
    {
        return view('settings.edit', [
            'settings' => $settings->all(),
            'dateFormats' => UpdateSettingsRequest::dateFormats(),
        ]);
    }

    /**
     * Persist application settings.
     */
    public function update(UpdateSettingsRequest $request, SettingService $settings, AuditLogger $auditLogger): RedirectResponse
    {
        $old = $settings->all();
        $new = $request->validated();

        $settings->set($new);

        $changed = array_keys(array_diff_assoc(array_map('strval', $new), array_map('strval', array_intersect_key($old, $new))));

        if ($changed !== []) {
            $auditLogger->log('updated', 'Updated application settings', properties: [
                'old' => array_intersect_key($old, array_flip($changed)),
                'new' => array_intersect_key($new, array_flip($changed)),
            ]);
        }

        return redirect()->route('settings.edit')->with('success', 'Settings saved successfully.');
    }
}
