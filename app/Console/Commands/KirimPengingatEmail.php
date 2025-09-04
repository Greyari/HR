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
    // Signature dengan option --test untuk pengiriman langsung testing
    protected $signature = 'pengingat:kirim {--test : Kirim email langsung untuk testing}';
    protected $description = 'Kirim email pengingat ke user sesuai peran H-7 atau kurang dari 7 hari lago';

    public function handle()
    {
        Log::info('[Command] KirimPengingatEmail dijalankan');

        $now = Carbon::now();

        // Ambil pengingat sesuai mode
        if ($this->option('test')) {
            Log::info('[Command] Mode TEST aktif, ambil pengingat hari ini.');
            $pengingats = Pengingat::with('peran.users')
                ->where('status', 'Pending')
                ->whereDate('tanggal_jatuh_tempo', $now->startOfDay())
                ->get();
        } else {
            // Normal mode: ambil semua pengingat pending
            $pengingats = Pengingat::with('peran.users')
                ->where('status', 'Pending')
                ->get()
                ->filter(function ($p) use ($now) {
                    $diffDays = $now->diffInDays($p->tanggal_jatuh_tempo, false);
                    return $diffDays >= 0 && $diffDays <= 7; // ≤ 7 hari ke depan
                });
        }

        Log::info('[Command] Jumlah pengingat ditemukan: ' . $pengingats->count());

        foreach ($pengingats as $pengingat) {
            $now = Carbon::now();

            // Skip jika pengingat tidak punya peran atau user
            if (!$pengingat->peran || $pengingat->peran->users->isEmpty()) {
                Log::info("[Command] Pengingat ID {$pengingat->id} dilewati, peran atau user kosong.");
                continue;
            }

            // Cek apakah email sudah dikirim dalam 24 jam terakhir
            if ($pengingat->last_notified_at && $pengingat->last_notified_at->diffInHours($now) < 24) {
                Log::info("[Command] Pengingat ID {$pengingat->id} dilewati, sudah dikirim dalam 24 jam.");
                continue;
            }

            foreach ($pengingat->peran->users as $user) {
                try {
                    // Queue email, lebih aman untuk banyak user
                    Mail::to($user->email)->queue(new PengingatEmail($pengingat));
                    Log::info("[Command] Email queued ke: {$user->email} untuk pengingat ID {$pengingat->id}");
                } catch (\Exception $e) {
                    Log::error("[Command] Gagal mengirim email ke {$user->email}: " . $e->getMessage());
                }
            }

            // Update last_notified_at
            $pengingat->last_notified_at = $now;
            $pengingat->save();
        }

        $this->info('Pengingat email berhasil diproses.');
        Log::info('[Command] Pengingat email selesai diproses.');
    }
}
