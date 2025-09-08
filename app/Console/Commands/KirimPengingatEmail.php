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
        Log::info('[Command] KirimPengingatEmail dijalankan');

        $now = Carbon::now();

        // Ambil pengingat
        if ($this->option('test')) {
            Log::info('[Command] Mode TEST aktif, ambil pengingat hari ini.');
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

        Log::info('[Command] Jumlah pengingat ditemukan: ' . $pengingats->count());

        foreach ($pengingats as $pengingat) {
            $now = Carbon::now();

            if (!$pengingat->peran || $pengingat->peran->users->isEmpty()) {
                Log::info("[Command] Pengingat ID {$pengingat->id} dilewati, peran atau user kosong.");
                continue;
            }

            // Aturan beda sesuai environment
            if (app()->environment('production')) {
                // Di production: jangan kirim lebih dari sekali dalam 24 jam
                if ($pengingat->last_notified_at && $pengingat->last_notified_at->diffInHours($now) < 24) {
                    Log::info("[Command] Pengingat ID {$pengingat->id} dilewati (sudah dikirim < 24 jam).");
                    continue;
                }
            } else {
                // Di local/dev: bebas (supaya bisa test tiap menit)
                Log::info("[Command] Mode LOCAL: skip cek 24 jam untuk pengingat ID {$pengingat->id}");
            }

            foreach ($pengingat->peran->users as $user) {
                try {
                    Mail::to($user->email)->queue(new PengingatEmail($pengingat));
                    Log::info("[Command] Email queued ke: {$user->email} untuk pengingat ID {$pengingat->id}");
                } catch (\Exception $e) {
                    Log::error("[Command] Gagal mengirim email ke {$user->email}: " . $e->getMessage());
                }
            }

            $pengingat->last_notified_at = $now;
            $pengingat->save();
        }

        $this->info('Pengingat email berhasil diproses.');
        Log::info('[Command] Pengingat email selesai diproses.');
    }
}
