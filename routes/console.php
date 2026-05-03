<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sinkronisasi data pengguna dari Sipetra (SSO) setiap jam 6 pagi
Schedule::command('sync:users')->dailyAt('06:00');
