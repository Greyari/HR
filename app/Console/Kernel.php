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
        echo "[DEBUG] schedule() terpanggil\n";
        Log::info('[Scheduler] schedule() dipanggil');

        if (app()->environment('production')) {
            // Ambil jam masuk dari tabel kantor
            $kantor = DB::table('kantor')->first();

            if ($kantor && $kantor->jam_masuk) {
                $jamMasuk = Carbon::createFromFormat('H:i:s', $kantor->jam_masuk)->format('H:i');

                $schedule->command('pengingat:kirim')
                    ->dailyAt($jamMasuk) 
                    ->before(function () use ($jamMasuk) {
                        Log::info("[Scheduler][PRODUCTION] pengingat:kirim akan dijalankan jam {$jamMasuk}");
                    })
                    ->after(function () {
                        Log::info('[Scheduler][PRODUCTION] pengingat:kirim selesai dijalankan');
                    });
            } else {
                Log::warning('[Scheduler] Data kantor tidak ditemukan atau jam_masuk kosong');
            }
        } else {
            // Local / development → tiap menit biar cepat testing
            $schedule->command('pengingat:kirim')
                ->everyMinute()
                ->before(function () {
                    Log::info('[Scheduler][LOCAL] pengingat:kirim akan dijalankan');
                })
                ->after(function () {
                    Log::info('[Scheduler][LOCAL] pengingat:kirim selesai dijalankan');
                });
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
