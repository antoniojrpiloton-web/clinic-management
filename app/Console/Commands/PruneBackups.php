<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\BackupService;
use App\Services\SettingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('backup:prune')]
#[Description('Delete database backups older than the configured retention period')]
class PruneBackups extends Command
{
    public function handle(SettingService $settings, BackupService $backupService): int
    {
        $days = max(1, (int) $settings->get('backup_retention_days', 30));
        $deleted = 0;

        Backup::query()
            ->where('created_at', '<', now()->subDays($days))
            ->chunkById(100, function ($backups) use ($backupService, &$deleted): void {
                foreach ($backups as $backup) {
                    $backupService->delete($backup);
                    $deleted++;
                }
            });

        $this->components->info("Pruned {$deleted} backup(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
