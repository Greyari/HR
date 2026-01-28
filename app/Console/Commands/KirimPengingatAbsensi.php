<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Absensi;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class KirimPengingatAbsensi extends Command
{
    protected $signature = 'absensi:pengingat';
    protected $description = 'Kirim notifikasi pengingat ke karyawan yang belum check-in (kecuali admin/HR)';

    public function handle()
    {
        $hariIni = Carbon::today()->format('Y-m-d');
        $jamSekarang = Carbon::now();

        // Batasi jam kerja (misal setelah jam 08:00)
        if ($jamSekarang->hour < 8) {
            return;
        }

        // Ambil semua user kecuali yang punya fitur 'lihat_semua_absensi'
        $users = User::whereDoesntHave('peran.fitur', function ($q) {
            $q->where('nama_fitur', 'lihat_semua_absensi');
        })->get();

        foreach ($users as $user) {

            // Skip user tanpa device token
            if (!$user->device_token) continue;

            // Cek apakah sudah absen hari ini
            $sudahAbsen = Absensi::where('user_id', $user->id)
                ->whereDate('checkin_date', $hariIni)
                ->exists();

            if ($sudahAbsen) continue;

            // Kirim notifikasi pengingat
            NotificationHelper::sendToUser(
                $user,
                '⏰ Pengingat Absensi',
                'Anda belum melakukan absensi hari ini. Silakan check-in.',
                'absensi_reminder'
            );
        }
    }
}
