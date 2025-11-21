<?php

namespace Database\Seeders;

use App\Models\Fitur;
use App\Models\Peran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HakAksesSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua fitur (keyBy supaya gampang dipanggil pakai nama)
        $allFitur = Fitur::all()->keyBy('nama_fitur');

        // ========================
        // Mapping peran => fitur yang diizinkan
        // ========================
        $roleAccess = [
            // Admin Super (semua kecuali blacklist)
            'Admin Super' => Fitur::whereNotIn('nama_fitur', [
                'approve_cuti_step1',
                'approve_lembur_step1',
                'lihat_lembur_sendiri',
                'lihat_cuti_sendiri',
                'lihat_tugas_sendiri',
                'tambah_lampiran_tugas',
                'absensi',
                'lihat_absensi_sendiri',
                'tambah_lembur',
                'tambah_cuti',
            ])->pluck('nama_fitur')->toArray(),

            // Admin Office Website
            'Admin Office Website' => [
                'web',
                'tambah_cuti',
                'tambah_lembur',
                'approve_cuti_step1',
                'approve_lembur_step1',
                'approve_cuti',
                'approve_lembur',
                'decline_cuti',
                'decline_lembur',
                'lihat_semua_lembur',
                'lihat_lembur',
                'lihat_cuti',
                'lihat_semua_cuti',
                'ubah_status_tugas',
                'tambah_tugas',
                'edit_tugas',
                'hapus_tugas',
                'lihat_semua_tugas',
                'lihat_tugas',
                'gaji',
                'potongan_gaji',
                'absensi',
                'lihat_absensi_sendiri',
                'pengingat'
            ],

            // Admin Office Aplikasi
            'Admin Office Aplikasi' => [
                'apk',
                'tambah_cuti',
                'tambah_lembur',
                'approve_cuti_step1',
                'approve_lembur_step1',
                'approve_cuti',
                'approve_lembur',
                'decline_cuti',
                'decline_lembur',
                'lihat_semua_lembur',
                'lihat_lembur',
                'lihat_cuti',
                'lihat_semua_cuti',
                'ubah_status_tugas',
                'tambah_tugas',
                'edit_tugas',
                'hapus_tugas',
                'lihat_semua_tugas',
                'lihat_tugas',
                'gaji',
                'potongan_gaji',
                'absensi',
                'lihat_absensi_sendiri',
                'pengingat'
            ],

            // Teknisi Website
            'Teknisi Website' => [
                'web',
                'lihat_tugas',
                'lihat_tugas_sendiri',
                'lihat_lembur',
                'lihat_lembur_sendiri',
                'lihat_cuti',
                'lihat_cuti_sendiri',
                'tambah_lembur',
                'tambah_cuti',
                'tambah_lampiran_tugas',
                'absensi',
                'lihat_absensi_sendiri',
            ],

            // Teknisi Aplikasi
            'Teknisi Aplikasi' => [
                'apk',
                'lihat_tugas',
                'lihat_tugas_sendiri',
                'lihat_lembur',
                'lihat_lembur_sendiri',
                'lihat_cuti',
                'lihat_cuti_sendiri',
                'tambah_lembur',
                'tambah_cuti',
                'tambah_lampiran_tugas',
                'absensi',
                'lihat_absensi_sendiri',
            ],

            // Marketing Website
            'Marketing Website' => [
                'web',
                'lihat_tugas',
                'lihat_tugas_sendiri',
                'lihat_lembur',
                'lihat_lembur_sendiri',
                'lihat_cuti',
                'lihat_cuti_sendiri',
                'tambah_lembur',
                'tambah_cuti',
                'tambah_lampiran_tugas',
                'absensi',
                'lihat_absensi_sendiri',
            ],

            // Marketing Aplikasi
            'Marketing Aplikasi' => [
                'apk',
                'lihat_tugas',
                'lihat_tugas_sendiri',
                'lihat_lembur',
                'lihat_lembur_sendiri',
                'lihat_cuti',
                'lihat_cuti_sendiri',
                'tambah_lembur',
                'tambah_cuti',
                'tambah_lampiran_tugas',
                'absensi',
                'lihat_absensi_sendiri',
            ],

            // Magang Website
            'Magang Website' => [
                'web',
                'lihat_tugas',
                'lihat_tugas_sendiri',
                'lihat_cuti',
                'lihat_cuti_sendiri',
                'tambah_cuti',
                'tambah_lampiran_tugas',
                'absensi',
                'lihat_absensi_sendiri',
            ],

            // Magang Aplikasi
            'Magang Aplikasi' => [
                'apk',
                'lihat_tugas',
                'lihat_tugas_sendiri',
                'lihat_cuti',
                'lihat_cuti_sendiri',
                'tambah_cuti',
                'tambah_lampiran_tugas',
                'absensi',
                'lihat_absensi_sendiri',
            ],
        ];

        // ========================
        // Proses insert izin_fitur
        // ========================
        foreach ($roleAccess as $roleName => $fiturList) {
            $peran = Peran::where('nama_peran', $roleName)->first();
            if (!$peran) continue;

            foreach ($fiturList as $namaFitur) {
                if (isset($allFitur[$namaFitur])) {
                    DB::table('izin_fitur')->updateOrInsert(
                        ['peran_id' => $peran->id, 'fitur_id' => $allFitur[$namaFitur]->id],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }
}
