<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\FeatureControlService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureControlController extends Controller
{
    public function __construct(
        protected FeatureControlService $featureControl,
        protected SettingService $settings,
        protected AuditLogger $auditLogger
    ) {}

    protected function authorizeController(Request $request): void
    {
        if (! $request->user()?->isFeatureController()) {
            abort(403, 'Only the master system developer (apiloton1@snsu.edu.ph) can manage feature release controls.');
        }
    }

    public function index(Request $request): View
    {
        $this->authorizeController($request);

        return view('feature-control.index', [
            'features' => $this->featureControl->allWithStatus(),
            'progress' => $this->featureControl->progressSummary(),
            'displayMode' => (string) $this->settings->get('feature_lock_display_mode', 'badge'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeController($request);

        $validated = $request->validate([
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'in:0,1'],
            'feature_lock_display_mode' => ['required', 'in:badge,hide'],
        ]);

        $submittedFeatures = (array) ($validated['features'] ?? []);
        $updates = [
            'feature_lock_display_mode' => $validated['feature_lock_display_mode'],
        ];

        foreach (array_keys(FeatureControlService::definitions()) as $key) {
            $updates[$key] = isset($submittedFeatures[$key]) && (string) $submittedFeatures[$key] === '1' ? '1' : '0';
        }

        $this->settings->set($updates);

        $progress = $this->featureControl->progressSummary();
        $this->auditLogger->log(
            'updated',
            "Updated feature release controls ({$progress['enabled_count']}/{$progress['total_count']} modules active — {$progress['percentage']}%)",
            null,
            ['updates' => $updates]
        );

        return redirect()
            ->route('feature-control.index')
            ->with('success', "Feature access updated! Client turn-over progress is now set to {$progress['percentage']}% ({$progress['enabled_count']} of {$progress['total_count']} modules active).");
    }

    public function applyPreset(Request $request): RedirectResponse
    {
        $this->authorizeController($request);

        $validated = $request->validate([
            'milestone' => ['required', 'integer', 'in:25,50,75,100'],
        ]);

        $milestone = (int) $validated['milestone'];
        $this->featureControl->applyMilestonePreset($milestone);

        $progress = $this->featureControl->progressSummary();
        $this->auditLogger->log(
            'updated',
            "Applied {$milestone}% turn-over milestone preset ({$progress['enabled_count']}/{$progress['total_count']} modules active)",
            null,
            ['milestone' => $milestone]
        );

        return redirect()
            ->route('feature-control.index')
            ->with('success', "Applied {$milestone}% Turn-Over Preset ({$progress['enabled_count']} of {$progress['total_count']} modules enabled for client accounts).");
    }
}
