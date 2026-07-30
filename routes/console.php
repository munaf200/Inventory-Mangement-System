<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// 1. Local DB Backup banao (Offline-friendly)
Schedule::command('backup:run --only-db')->dailyAt('21:23');

// 2. Har 15 minute baad check karo ke internet aaya ya nahi, agar aaya to Drive par sync kardo
Schedule::command('backup:sync-drive')->everyFifteenMinutes();