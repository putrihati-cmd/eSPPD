<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled commands for e-SPPD system

// Process approval reminders every hour
Schedule::command('approval:reminders')->hourly();

// Send scheduled reports at 7:00 AM daily
Schedule::command('reports:send-scheduled')->dailyAt('07:00');

// Clear old webhook logs weekly
Schedule::command('model:prune', ['--model' => \App\Models\WebhookLog::class])->weekly();

// Clear temporary files daily
Schedule::command('esppd:cleanup-temp')->daily();
