<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Monthly summary email - runs on the 5th of each month at 8am
Schedule::command('expenses:monthly-summary')->monthlyOn(5, '08:00');
