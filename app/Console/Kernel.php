<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        Log::channel('scheduler')->info('[Scheduler] schedule() dipanggil');

        if (app()->environment('production')) {
            $schedule->command('pengingat:kirim')
                ->dailyAt('08:00')
                ->before(function () {
                    Log::channel('scheduler')->info("[PRODUCTION] pengingat:kirim akan dijalankan jam 08:00");
                })
                ->after(function () {
                    Log::channel('scheduler')->info('[PRODUCTION] pengingat:kirim selesai dijalankan pada ' . now());
                });
        } else {
            $schedule->command('pengingat:kirim')
                ->everyMinute()
                ->before(function () {
                    Log::channel('scheduler')->info('[LOCAL] pengingat:kirim akan dijalankan');
                })
                ->after(function () {
                    Log::channel('scheduler')->info('[LOCAL] pengingat:kirim selesai dijalankan pada ' . now());
                });
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
