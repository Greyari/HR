<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Peran;
use App\Models\Jabatan;
use App\Models\Departemen;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // Peran
        $peranSA = Peran::where('nama_peran', 'Admin Super')->first();
        $peranAoApk = Peran::where('nama_peran', 'Admin Office Aplikasi')->first();
        $peranAoWeb = Peran::where('nama_peran', 'Admin Office Website')->first();
        $peranTnApk = Peran::where('nama_peran', 'Teknisi Aplikasi')->first();
        $peranTnWeb = Peran::where('nama_peran', 'Teknisi Website')->first();
        $peranMgApk = Peran::where('nama_peran', 'Magang Aplikasi')->first();
        $peranMgWeb = Peran::where('nama_peran', 'Magang Website')->first();
        $peranMkApk = Peran::where('nama_peran', 'Marketing Aplikasi')->first();
        $peranMkWeb = Peran::where('nama_peran', 'Marketing Website')->first();

        // Jabatan
        $jabatanGM = Jabatan::where('nama_jabatan', 'General Manager')->first();
        $jabatanSe = Jabatan::where('nama_jabatan', 'Sales Executive')->first();
        $jabatanTm = Jabatan::where('nama_jabatan', 'Telemarketing')->first();
        $jabatanAs = Jabatan::where('nama_jabatan', 'Admin Sales')->first();
        $jabatanAos = Jabatan::where('nama_jabatan', 'Admin Office Senior')->first();
        $jabatanMi = Jabatan::where('nama_jabatan', 'Marketing Intern')->first();
        $jabatanIi = Jabatan::where('nama_jabatan', 'IT Intern')->first();
        $jabatanTs = Jabatan::where('nama_jabatan', 'Teknisi Senior')->first();
        $jabatanTi = Jabatan::where('nama_jabatan', 'Teknisi Intern')->first();

        // Departemen
        $departemenIT = Departemen::where('nama_departemen', 'IT')->first();
        $departemenOffice = Departemen::where('nama_departemen', 'Office')->first();
        $departemenMarketing = Departemen::where('nama_departemen', 'Marketing')->first();
        $departemenMaintenance = Departemen::where('nama_departemen', 'Maintenance')->first();


            //////////////// Super admin ////////////////
            User::updateOrCreate(
                ['email' => 'bekcupsuper@gmail.com'],
                [
                'nama' => 'bekcup',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanGM->id,
                'peran_id' => $peranSA->id,
                'departemen_id' => $departemenIT->id,
                'gaji_per_hari' => 500000,
                // 'npwp' => "-",
                // 'bpjs_kesehatan' => "-",
                // 'bpjs_ketenagakerjaan' => "-",
                'status_pernikahan' => 'Menikah',
                'jenis_kelamin' => 'Laki-laki'
            ]);


            //////////////// Admin  Office ////////////////
            User::updateOrCreate(
                ['email' => 'bekcupoffice@gmail.com'],
                [
                'nama' => 'bekcup',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanAos->id,
                'peran_id' => $peranAoWeb->id,
                'departemen_id' => $departemenOffice->id,
                'gaji_per_hari' => 200000,
                // 'npwp' => "-",
                // 'bpjs_kesehatan' => "-",
                // 'bpjs_ketenagakerjaan' => "-",
                'status_pernikahan' => 'Belum Menikah',
                'jenis_kelamin' => 'Perempuan'
            ]);


            User::updateOrCreate(
                ['email' => 'bekcupteknisi@gmail.com'],
                [
                'nama' => 'bekcup',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanTs->id,
                'peran_id' => $peranTnApk->id,
                'departemen_id' => $departemenMaintenance->id,
                'gaji_per_hari' => 250000,
                // 'npwp' => "-",
                // 'bpjs_kesehatan' => "-",
                // 'bpjs_ketenagakerjaan' => "-",
                'status_pernikahan' => 'Belum Menikah',
                'jenis_kelamin' => 'Laki-laki'
            ]);
    }
}
