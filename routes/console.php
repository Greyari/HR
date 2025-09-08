<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Command contoh bawaan Laravel
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// scheduler 
Schedule::command('pengingat:kirim')
    ->everyMinute()
    ->before(function () {
        info('[Scheduler] pengingat:kirim akan dijalankan');
    })
    ->after(function () {
        info('[Scheduler] pengingat:kirim selesai dijalankan');
    });
