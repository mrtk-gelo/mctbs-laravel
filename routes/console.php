<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invitation:remind-expired')->daily();
Schedule::command('database:backup')
    ->weeklyOn(0, '02:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();
