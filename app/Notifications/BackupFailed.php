<?php

namespace App\Notifications;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class BackupFailed extends Notification
{
    use Queueable;

    public function __construct(public Backup $backup) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Backup failed',
            'message' => 'Database backup failed: '.Str::limit((string) $this->backup->error, 120),
            'icon' => 'bi-exclamation-octagon',
            'color' => 'danger',
            'url' => route('backups.index'),
        ];
    }
}
