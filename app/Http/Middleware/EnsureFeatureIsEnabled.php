<?php

namespace App\Http\Middleware;

use App\Services\FeatureControlService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureIsEnabled
{
    public function __construct(protected FeatureControlService $featureControl) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests() && ! config('app.enforce_feature_gate_in_tests', false)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->isFeatureController()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $featureKey = $this->featureControl->resolveFeatureForRoute($routeName);

        if ($featureKey !== null && ! $this->featureControl->userCanAccess($featureKey, $user)) {
            $definitions = FeatureControlService::definitions();
            $feature = $definitions[$featureKey] ?? [
                'key' => $featureKey,
                'title' => 'System Module',
                'description' => 'This module is currently locked.',
                'icon' => 'bi-lock-fill',
                'milestone' => 100,
                'milestone_label' => 'Pending Turn-Over Activation',
            ];
            $progress = $this->featureControl->progressSummary();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => "{$feature['title']} is currently locked under the {$progress['percentage']}% turn-over phase.",
                ], 403);
            }

            return response()->view('feature-control.locked', [
                'feature' => $feature,
                'progress' => $progress,
            ], 403);
        }

        return $next($request);
    }
}
