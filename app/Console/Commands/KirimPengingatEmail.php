<?php

namespace App\Console\Commands;

use App\Models\Pengingat;
use App\Mail\PengingatEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class KirimPengingatEmail extends Command implements ShouldQueue
{
    protected $signature = 'pengingat:kirim {--test : Kirim email langsung untuk testing}';
    protected $description = 'Kirim email pengingat ke user sesuai peran H-7 atau kurang dari 7 hari lagi';

    public function handle()
    {
        Log::channel('scheduler')->info('[Command] KirimPengingatEmail dijalankan');

        $now = Carbon::now();

        // Ambil pengingat
        if ($this->option('test')) {
            Log::channel('scheduler')->info('[Command] Mode TEST aktif, ambil pengingat hari ini.');
            $pengingats = Pengingat::with('peran.users')
                ->where('status', 'Pending')
                ->whereDate('tanggal_jatuh_tempo', $now->startOfDay())
                ->get();
        } else {
            $pengingats = Pengingat::with('peran.users')
                ->where('status', 'Pending')
                ->get()
                ->filter(function ($p) use ($now) {
                    $diffDays = $now->diffInDays($p->tanggal_jatuh_tempo, false);
                    return $diffDays >= 0 && $diffDays <= 7;
                });
        }

        Log::channel('scheduler')->info('[Command] Jumlah pengingat ditemukan: ' . $pengingats->count());

        foreach ($pengingats as $pengingat) {
            $now = Carbon::now();

            if (!$pengingat->peran || $pengingat->peran->users->isEmpty()) {
                Log::channel('scheduler')->info("[Command] Pengingat ID {$pengingat->id} dilewati, peran atau user kosong.");
                continue;
            }

            // Aturan beda sesuai environment
            if (app()->environment('production')) {
                if ($pengingat->last_notified_at
                    && $pengingat->last_notified_at->isSameDay($now)) {
                    // Sudah dikirim hari ini
                    Log::channel('scheduler')->info("[Command] Pengingat ID {$pengingat->id} dilewati (sudah dikirim hari ini).");
                    continue;
                }
            } else {
                Log::channel('scheduler')->info("[Command] Mode LOCAL: skip cek harian untuk pengingat ID {$pengingat->id}");
            }


            foreach ($pengingat->peran->users as $user) {
                try {
                    Mail::to($user->email)->queue(new PengingatEmail($pengingat));
                    Log::channel('scheduler')->info("[Command] Email queued ke: {$user->email} untuk pengingat ID {$pengingat->id}");
                } catch (\Exception $e) {
                    Log::channel('scheduler')->error("[Command] Gagal mengirim email ke {$user->email}: " . $e->getMessage());
                }
            }

            $pengingat->last_notified_at = $now;
            $pengingat->save();
        }

        $this->info('Pengingat email berhasil diproses.');
        Log::channel('scheduler')->info('[Command] Pengingat email selesai diproses.');
    }
}
