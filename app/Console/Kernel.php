<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\KirimPengingatEmail::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Debug log untuk memastikan scheduler method terpanggil
        Log::info('[Scheduler] schedule() dipanggil');

        $schedule->command('pengingat:kirim')->everyMinute()
                 ->before(function () {
                     Log::info('[Scheduler] pengingat:kirim akan dijalankan');
                 })
                 ->after(function () {
                     Log::info('[Scheduler] pengingat:kirim selesai dijalankan');
                 });
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
