<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Models\User;
use App\Notifications\BackupCompleted;
use App\Notifications\BackupFailed;
use App\Services\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use Throwable;

class CreateDatabaseBackup implements ShouldQueue
{
    use Queueable;

    /**
     * Backups are not retried automatically to avoid duplicate dumps.
     */
    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(public Backup $backup) {}

    /**
     * Execute the job.
     */
    public function handle(BackupService $backupService): void
    {
        $backupService->run($this->backup);

        Notification::send($this->recipients(), new BackupCompleted($this->backup));
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->backup->update([
            'status' => Backup::STATUS_FAILED,
            'error' => $exception?->getMessage(),
            'completed_at' => now(),
        ]);

        Notification::send($this->recipients(), new BackupFailed($this->backup));
    }

    /**
     * Active users allowed to manage backups.
     *
     * @return Collection<int, User>
     */
    protected function recipients(): Collection
    {
        return User::active()->permission('backups.view')->get();
    }
}
