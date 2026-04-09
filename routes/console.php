<?php

use App\Console\Commands\RefreshQuickBooksTokens;
use App\Console\Commands\SendTrialEndingReminders;
use Illuminate\Support\Facades\Schedule;

// QuickBooks token refresh — every 15 minutes
Schedule::command(RefreshQuickBooksTokens::class)->everyFifteenMinutes();

// Trial ending reminders — daily at 9am UTC
Schedule::command(SendTrialEndingReminders::class)->dailyAt('09:00');

// Prune stale event logs older than 90 days — weekly
Schedule::call(function () {
    \App\Models\EventLog::where('created_at', '<', now()->subDays(90))->delete();
})->weekly();
