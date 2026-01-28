<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
protected function schedule(Schedule $schedule): void
{
        Log::channel('scheduler')->info('[Scheduler] schedule() dipanggil');

        /**
         * ===============================
         * REMINDER PEMBAYARAN (EMAIL)
         * ===============================
         */
        if (app()->environment('production')) {
            $schedule->command('pengingat:kirim')
                ->dailyAt('08:00')
                ->before(fn () =>
                    Log::channel('scheduler')->info('[PROD] pengingat:kirim (EMAIL) akan dijalankan')
                )
                ->after(fn () =>
                    Log::channel('scheduler')->info('[PROD] pengingat:kirim (EMAIL) selesai')
                );
        } else {
            $schedule->command('pengingat:kirim')
                ->everyMinute()
                ->before(fn () =>
                    Log::channel('scheduler')->info('[LOCAL] pengingat:kirim (EMAIL) akan dijalankan')
                )
                ->after(fn () =>
                    Log::channel('scheduler')->info('[LOCAL] pengingat:kirim (EMAIL) selesai')
                );
        }

        /**
         * ===============================
         * REMINDER ABSENSI (PUSH NOTIF)
         * ===============================
         */
        if (app()->environment('production')) {
            $schedule->command('absensi:pengingat')
                ->hourly()
                ->before(fn () =>
                    Log::channel('scheduler')->info('[PROD] absensi:pengingat (PUSH) akan dijalankan')
                )
                ->after(fn () =>
                    Log::channel('scheduler')->info('[PROD] absensi:pengingat (PUSH) selesai')
                );
        } else {
            $schedule->command('absensi:pengingat')
                ->everyMinute() // testing lokal
                ->before(fn () =>
                    Log::channel('scheduler')->info('[LOCAL] absensi:pengingat (PUSH) akan dijalankan')
                )
                ->after(fn () =>
                    Log::channel('scheduler')->info('[LOCAL] absensi:pengingat (PUSH) selesai')
                );
        }
    }

}
