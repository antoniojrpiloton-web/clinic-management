<?php

namespace App\Notifications;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BackupCompleted extends Notification
{
    use Queueable;

    public function __construct(public Backup $backup) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Backup completed',
            'message' => "Database backup {$this->backup->filename} ({$this->backup->humanSize()}) was created.",
            'icon' => 'bi-database-check',
            'color' => 'success',
            'url' => route('backups.index'),
        ];
    }
}
