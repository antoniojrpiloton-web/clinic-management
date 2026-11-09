<?php

use App\Models\AuditLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run --type=scheduled')
    ->dailyAt('01:00')
    ->withoutOverlapping(60)
    ->onOneServer();

Schedule::command('backup:prune')->dailyAt('02:00');

Schedule::command('model:prune', ['--model' => [AuditLog::class]])->dailyAt('02:30');

Schedule::command('queue:prune-failed --hours=168')->weekly();
