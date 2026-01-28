<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Kantor;
use App\Models\Absensi;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class KirimPengingatAbsensi extends Command
{
    protected $signature = 'absensi:pengingat';
    protected $description = 'Kirim notifikasi pengingat ke karyawan yang belum check-in (kecuali admin/HR)';

    public function handle()
    {
        $this->info('🚀 absensi:pengingat DIMULAI');

        $kantor = Kantor::first();

        if (!$kantor) {
            $this->error('❌ Data kantor tidak ditemukan');
            return;
        }

        $now = Carbon::now();
        $jamMasuk = Carbon::createFromTimeString($kantor->jam_masuk);
        $jamKeluar = Carbon::createFromTimeString($kantor->jam_keluar);

        // Cek apakah masih jam kerja
        if ($now->lt($jamMasuk) || $now->gt($jamKeluar)) {
            $this->warn('⏱️ Di luar jam kerja, skip');
            return;
        }

        $hariIni = Carbon::today()->toDateString();

        $users = User::whereNotNull('device_token')
            ->whereDoesntHave('peran.fitur', function ($q) {
                $q->where('nama_fitur', 'lihat_semua_absensi');
            })
            ->get();

        $this->info('👥 Total user: ' . $users->count());

        foreach ($users as $user) {

            $sudahAbsen = Absensi::where('user_id', $user->id)
                ->whereDate('checkin_date', $hariIni)
                ->exists();

            if ($sudahAbsen) {
                continue;
            }

            NotificationHelper::sendToUser(
                $user,
                '⏰ Pengingat Absensi',
                'Anda belum melakukan absensi hari ini.',
                'absensi_reminder'
            );

            $this->info("🔔 Notif dikirim ke {$user->nama}");
        }

        $this->info('🏁 absensi:pengingat SELESAI');
    }

}
