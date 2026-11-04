<?php

namespace App\Console\Commands;

use App\Jobs\CreateDatabaseBackup;
use App\Models\Backup;
use App\Services\SettingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('backup:run {--type=scheduled : Backup type label (scheduled|manual)} {--force : Run even when scheduled backups are disabled}')]
#[Description('Create a compressed MySQL database backup')]
class BackupDatabase extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SettingService $settings): int
    {
        if (! $this->option('force') && ! (bool) $settings->get('backup_enabled')) {
            $this->components->warn('Scheduled backups are disabled in settings.');

            return self::SUCCESS;
        }

        $backup = Backup::create([
            'type' => $this->option('type'),
            'status' => Backup::STATUS_PENDING,
        ]);

        try {
            CreateDatabaseBackup::dispatchSync($backup);
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $backup->refresh();
        $this->components->info("Backup created: {$backup->filename} ({$backup->humanSize()})");

        return self::SUCCESS;
    }
}
