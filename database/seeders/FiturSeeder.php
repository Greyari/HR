<?php

namespace Database\Seeders;

use App\Models\Fitur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FiturSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Fitur::create([
            'nama_fitur' => 'Pengajuan Cuti',
            'deskripsi' => 'Fitur untuk mengajukan cuti',
        ]);
        Fitur::create([
            'nama_fitur' => 'Laporan Cuti',
            'deskripsi' => 'Fitur untuk melihat laporan cuti',
        ]);
        Fitur::create([
            'nama_fitur' => 'Manajemen Karyawan',
            'deskripsi' => 'Fitur untuk mengelola data karyawan',
        ]);
        Fitur::create([
            'nama_fitur' => 'Manajemen Departemen',
            'deskripsi' => 'Fitur untuk mengelola data departemen',
        ]);
        Fitur::create([
            'nama_fitur' => 'Manajemen Peran',
            'deskripsi' => 'Fitur untuk mengelola peran dan izin akses',
        ]);
        Fitur::create([
            'nama_fitur' => 'Manajemen Izin',
            'deskripsi' => 'Fitur untuk mengelola izin karyawan',
        ]);
        Fitur::create([
            'nama_fitur' => 'Manajemen Fitur',
            'deskripsi' => 'Fitur untuk mengelola fitur aplikasi',
        ]);
        Fitur::create([
            'nama_fitur' => 'Manajemen Laporan',
            'deskripsi' => 'Fitur untuk melihat laporan aktivitas aplikasi',
        ]);
        Fitur::create([
            'nama_fitur' => 'Pengaturan Aplikasi',
            'deskripsi' => 'Fitur untuk mengatur konfigurasi aplikasi',
        ]);
        Fitur::create([
            'nama_fitur' => 'Notifikasi',
            'deskripsi' => 'Fitur untuk mengelola notifikasi aplikasi',
        ]);
    }
}
