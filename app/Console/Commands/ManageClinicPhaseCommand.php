<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Services\FeatureControlService;
use App\Services\SettingService;
use Illuminate\Console\Command;

class ManageClinicPhaseCommand extends Command
{
    protected $signature = 'clinic:phase
                            {action=status : Action to run: status, next, 25, 50, 75, 100, phase1, phase2, phase3, phase4, unlock, or lock}
                            {feature? : Specific feature key when using unlock or lock (e.g. feature_dental_consultations)}
                            {--display= : Optional sidebar mode for locked modules (badge or hide)}';

    protected $description = 'Manage system turn-over phases (25%, 50%, 75%, 100%) and unlock/lock individual clinic modules via terminal';

    public function handle(
        FeatureControlService $featureControl,
        SettingService $settings,
        AuditLogger $auditLogger
    ): int {
        $action = strtolower(trim((string) $this->argument('action')));
        $featureArg = $this->argument('feature') ? strtolower(trim((string) $this->argument('feature'))) : null;
        $displayOpt = $this->option('display') ? strtolower(trim((string) $this->option('display'))) : null;

        if ($displayOpt !== null) {
            if (! in_array($displayOpt, ['badge', 'hide'], true)) {
                $this->error("Invalid --display value [{$displayOpt}]. Allowed values: badge, hide.");

                return self::FAILURE;
            }
            $settings->set(['feature_lock_display_mode' => $displayOpt]);
            $this->info("Sidebar lock display mode set to: {$displayOpt}");
        }

        if ($action === 'status') {
            $this->renderStatus($featureControl, $settings);

            return self::SUCCESS;
        }

        if ($action === 'next') {
            $current = $featureControl->progressSummary()['percentage'];
            $nextMilestone = match (true) {
                $current < 25 => 25,
                $current < 50 => 50,
                $current < 75 => 75,
                default => 100,
            };

            if ($current >= 100) {
                $this->warn('System is already at 100% Full Turn-Over (all 12 modules unlocked).');
                $this->renderStatus($featureControl, $settings);

                return self::SUCCESS;
            }

            $featureControl->applyMilestonePreset($nextMilestone);
            $auditLogger->log('updated', "CLI unlocked next turn-over phase ({$nextMilestone}%)", null, ['milestone' => $nextMilestone]);
            $this->info("Unlocked next turn-over phase: {$current}% -> {$nextMilestone}%!");
            $this->renderStatus($featureControl, $settings);

            return self::SUCCESS;
        }

        $milestoneMap = [
            '25' => 25,
            '25%' => 25,
            'phase1' => 25,
            '1' => 25,
            '50' => 50,
            '50%' => 50,
            'phase2' => 50,
            '2' => 50,
            '75' => 75,
            '75%' => 75,
            'phase3' => 75,
            '3' => 75,
            '100' => 100,
            '100%' => 100,
            'phase4' => 100,
            '4' => 100,
            'all' => 100,
        ];

        if (isset($milestoneMap[$action])) {
            $targetMilestone = $milestoneMap[$action];
            $featureControl->applyMilestonePreset($targetMilestone);
            $auditLogger->log('updated', "CLI applied {$targetMilestone}% turn-over phase", null, ['milestone' => $targetMilestone]);
            $this->info("Turn-over phase set to {$targetMilestone}%!");
            $this->renderStatus($featureControl, $settings);

            return self::SUCCESS;
        }

        if (in_array($action, ['unlock', 'enable', 'lock', 'disable'], true)) {
            $definitions = FeatureControlService::definitions();

            if (! $featureArg) {
                $this->error('Please specify a feature key, e.g.: php artisan clinic:phase unlock feature_dental_consultations');
                $this->line('Available feature keys: '.implode(', ', array_keys($definitions)));

                return self::FAILURE;
            }

            $resolvedKey = isset($definitions[$featureArg])
                ? $featureArg
                : (isset($definitions['feature_'.$featureArg]) ? 'feature_'.$featureArg : null);

            if (! $resolvedKey) {
                $this->error("Unknown feature key [{$featureArg}].");
                $this->line('Available feature keys: '.implode(', ', array_keys($definitions)));

                return self::FAILURE;
            }

            $enable = in_array($action, ['unlock', 'enable'], true);
            $settings->set([$resolvedKey => $enable ? '1' : '0']);
            $stateText = $enable ? 'UNLOCKED' : 'LOCKED';
            $this->info("Feature [{$resolvedKey}] ({$definitions[$resolvedKey]['title']}) is now {$stateText}.");
            $this->renderStatus($featureControl, $settings);

            return self::SUCCESS;
        }

        $this->error("Unknown action [{$action}]. Use: status, next, 25, 50, 75, 100, phase1..phase4, unlock <feature>, or lock <feature>.");

        return self::FAILURE;
    }

    protected function renderStatus(FeatureControlService $featureControl, SettingService $settings): void
    {
        $summary = $featureControl->progressSummary();
        $displayMode = (string) $settings->get('feature_lock_display_mode', 'badge');

        $this->newLine();
        $this->line("  <options=bold>Active Turn-Over Progress:</> <fg=cyan;options=bold>{$summary['percentage']}%</> ({$summary['enabled_count']}/{$summary['total_count']} modules unlocked)");
        $this->line("  <options=bold>Sidebar Lock Display Mode:</> <fg=yellow>{$displayMode}</> | <options=bold>Master Bypass:</> <fg=green>apiloton1@snsu.edu.ph</>");
        $this->newLine();

        $rows = [];
        foreach ($featureControl->allWithStatus() as $key => $feature) {
            $rows[] = [
                'Phase '.($feature['milestone'] / 25)." ({$feature['milestone']}%)",
                $key,
                $feature['title'],
                $feature['enabled'] ? '<fg=green;options=bold>UNLOCKED</>' : '<fg=red>LOCKED</>',
            ];
        }

        $this->table(['Phase / Tranche', 'Feature Key', 'Module Name', 'Client Status'], $rows);
    }
}
