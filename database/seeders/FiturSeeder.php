<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fitur;

class FiturSeeder extends Seeder
{
    public function run(): void
    {
        $fiturs = [
            // ---------------------- Auth ----------------------
            ['web', 'User hanya dapat login melalui aplikasi web'],
            ['apk', 'User hanya dapat login melalui aplikasi mobile'],

            // ---------------------- Lembur ----------------------
            ['lihat_lembur', 'Melihat daftar lembur'],
            ['lihat_semua_lembur', 'Karyawan dapat melihat semua lembur yang tercatat'],
            ['lihat_lembur_sendiri', 'Karyawan hanya dapat melihat lembur sendiri'],
            ['tambah_lembur', 'Menambah lembur baru'],
            ['approve_lembur', 'Menyetujui lembur'],
            ['approve_lembur_step1', 'Melakukan persetujuan lembur pada tahap awal'],
            ['approve_lembur_step2', 'Melakukan persetujuan lembur final'],
            ['decline_lembur', 'Menolak lembur'],

            // ---------------------- Cuti ----------------------
            ['lihat_cuti', 'Melihat daftar cuti'],
            ['lihat_semua_cuti', 'Karyawan dapat melihat semua cuti yang tercatat'],
            ['lihat_cuti_sendiri', 'Karyawan hanya dapat melihat cuti sendiri'],
            ['tambah_cuti', 'Mengajukan cuti baru'],
            ['approve_cuti', 'Menyetujui cuti'],
            ['approve_cuti_step1', 'Melakukan persetujuan cuti pada tahap awal'],
            ['approve_cuti_step2', 'Melakukan persetujuan cuti final'],
            ['decline_cuti', 'Menolak cuti'],

            // ---------------------- Tugas ----------------------
            ['lihat_tugas', 'Melihat daftar tugas'],
            ['lihat_semua_tugas', 'Karyawan dapat melihat semua daftar tugas'],
            ['tambah_tugas', 'Menambahkan tugas baru'],
            ['edit_tugas', 'Mengedit tugas'],
            ['hapus_tugas', 'Menghapus tugas'],
            ['lihat_tugas_sendiri', 'Karyawan hanya dapat melihat daftar tugas'],
            ['tambah_tugas', 'Menambahkan tugas baru'],
            ['edit_tugas', 'Mengedit tugas'],
            ['hapus_tugas', 'Menghapus tugas'],
            ['tambah_lampiran_tugas', 'Menambahkan lampiran tugas'],
            ['ubah_status_tugas', 'Mengupbah status tugas'],

            // ---------------------- Departemen ----------------------
            ['departemen', 'Manajemen Departemen'],

            // ---------------------- Peran ----------------------
            ['peran', 'Manajemen Peran'],

            // ---------------------- Jabatan ----------------------
            ['jabatan', 'Manajemen Jabatan'],

            // ---------------------- User / Karyawan ----------------------
            ['karyawan', 'Manajemen Karyawan'],

            // ---------------------- Gaji ----------------------
            ['gaji', 'Mengelola Gaji'],

            // ---------------------- Potongan ----------------------
            ['potongan_gaji', 'Mengelola Potongan Gaji'],

            // ---------------------- Kantor ----------------------
            ['kantor', 'Mengelola Profil Kantor'],

            // ---------------------- Absensi ----------------------
            ['absensi', 'Fitur Absensi (Checkin / Checkout)'],
            ['lihat_absensi_sendiri', 'Karyawan hanya dapat melihat daftar absen-nya sendiri'],
            ['lihat_semua_absensi', 'Karyawan dapat melihat semua daftar absen'],

            // ---------------------- Log ----------------------
            ['log_aktifitas', 'Melihat log aktivitas'],

            // ---------------------- Pengingat ----------------------
            ['pengingat', 'Mengelola pengingat'],

            // ---------------------- Denger ----------------------
            ['denger', 'Menghapus data secara massal'],

            // ---------------------- Buka akun ----------------------
            ['buka_akun', 'menejemen akun yang terkunci'],

            // ---------------------- menejemen device ----------------------
            ['reset_device', 'menejemen akun untuk reset device terkait'],

        ];

        foreach ($fiturs as [$nama, $deskripsi]) {
            Fitur::updateOrCreate(
                ['nama_fitur' => $nama],
                ['deskripsi' => $deskripsi]
            );
        }
    }
}
