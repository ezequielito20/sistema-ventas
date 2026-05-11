<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Actualización automática de la tasa de cambio BCV a las 6:00 AM y 6:00 PM
Schedule::command('exchange-rate:update')
    ->twiceDaily(6, 18)
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/exchange-rate.log'));

// === Scheduler SaaS ===
Schedule::job(new \App\Jobs\GenerateMonthlyInvoices)->dailyAt('01:00')->withoutOverlapping();
Schedule::job(new \App\Jobs\SuspendOverdueCompanies)->dailyAt('02:00')->withoutOverlapping();
Schedule::job(new \App\Jobs\CollectMonthlyUsage)->monthlyOn(1, '03:00')->withoutOverlapping();
