<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Scheduler - Reminder
|--------------------------------------------------------------------------
*/

if (app()->environment('production')) {

    /**
     * ===============================
     * REMINDER PEMBAYARAN (EMAIL)
     * ===============================
     */
    Schedule::command('pengingat:kirim')
        ->dailyAt('08:00')
        ->before(fn () =>
            Log::channel('scheduler')->info('[PROD] pengingat:kirim (EMAIL) akan dijalankan')
        )
        ->after(fn () =>
            Log::channel('scheduler')->info('[PROD] pengingat:kirim (EMAIL) selesai')
        );

    /**
     * ===============================
     * REMINDER ABSENSI (PUSH NOTIF)
     * ===============================
     */
    Schedule::command('absensi:pengingat')
        ->hourly()
        ->when(fn () => ! now()->isSunday())
        ->withoutOverlapping()
        ->before(fn () =>
            Log::channel('scheduler')->info('[PROD] absensi:pengingat (PUSH) akan dijalankan')
        )
        ->after(fn () =>
            Log::channel('scheduler')->info('[PROD] absensi:pengingat (PUSH) selesai')
        );

} else {

    /**
     * ===============================
     * LOCAL / WINDOWS (TESTING)
     * ===============================
     */

    Schedule::command('pengingat:kirim')
        ->everyMinute()
        ->before(fn () =>
            Log::channel('scheduler')->info('[LOCAL] pengingat:kirim (EMAIL) akan dijalankan')
        )
        ->after(fn () =>
            Log::channel('scheduler')->info('[LOCAL] pengingat:kirim (EMAIL) selesai')
        );

    Schedule::command('absensi:pengingat')
        ->everyMinute()
        ->before(fn () =>
            Log::channel('scheduler')->info('[LOCAL] absensi:pengingat (PUSH) akan dijalankan')
        )
        ->after(fn () =>
            Log::channel('scheduler')->info('[LOCAL] absensi:pengingat (PUSH) selesai')
        );
}
