<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Peran;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
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
            User::create([
                'nama' => 'User Super',
                'email' => 'usersuper@gmail.com',
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

            //////////////// Super admin ////////////////
            User::create([
                'nama' => 'ksi.admin',
                'email' => 'ksi.admin@gmail.com',
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

            User::create([
                'nama' => 'Bapak Jhoni',
                'email' => 'jhonip.sinaga@kreatifsystem.com',
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
            User::create([
                'nama' => 'Desy',
                'email' => 'desy@gmail.com',
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

            User::create([
                'nama' => 'Nata',
                'email' => 'nata@gmail.com',
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

            //////////////// marketing ////////////////
            User::create([
                'nama' => 'Eka Paksi Ramadhani',
                'email' => 'ekapaksir@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanSe->id,
                'peran_id' => $peranMkApk->id,
                'departemen_id' => $departemenMarketing->id,
                'gaji_per_hari' => 250000,
                'npwp' => "39.716.935.0-225.000",
                'bpjs_kesehatan' => "0003510022364",
                'bpjs_ketenagakerjaan' => "2171 1063 1299 9001",
                'status_pernikahan' => 'Belum Menikah',
                'jenis_kelamin' => 'Perempuan'
            ]);

            //////////////// magang ////////////////
            User::create([
                'nama' => 'Grey Ari Daniel Simatupang',
                'email' => 'tupang1017@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanIi->id,
                'peran_id' => $peranMgApk->id,
                'departemen_id' => $departemenIT->id,
                'gaji_per_hari' => 50000,
                // 'npwp' => "-",
                // 'bpjs_kesehatan' => "-",
                // 'bpjs_ketenagakerjaan' => "-",
                'status_pernikahan' => 'Belum Menikah',
                'jenis_kelamin' => 'Laki-laki'
            ]);

            User::create([
                'nama' => 'M Taufiq karim Haikal',
                'email' => 'haikaltaufiq4@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanIi->id,
                'peran_id' => $peranMgApk->id,
                'departemen_id' => $departemenIT->id,
                'gaji_per_hari' => 50000,
                // 'npwp' => "-",
                // 'bpjs_kesehatan' => "-",
                // 'bpjs_ketenagakerjaan' => "-",
                'status_pernikahan' => 'Belum Menikah',
                'jenis_kelamin' => 'Laki-laki'
            ]);

            User::create([
                'nama' => 'Zidan',
                'email' => 'zidan@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanMi->id,
                'peran_id' => $peranMgWeb->id,
                'departemen_id' => $departemenMarketing->id,
                'gaji_per_hari' => 50000,
                'npwp' => "-",
                'bpjs_kesehatan' => "-",
                'bpjs_ketenagakerjaan' => "-",
                'status_pernikahan' => 'Belum Menikah',
                'jenis_kelamin' => 'Laki-laki'
            ]);

            User::create([
                'nama' => 'Calvin',
                'email' => 'calvin@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanMi->id,
                'peran_id' => $peranMgApk->id,
                'departemen_id' => $departemenMarketing->id,
                'gaji_per_hari' => 50000,
                // 'npwp' => "-",
                // 'bpjs_kesehatan' => "-",
                // 'bpjs_ketenagakerjaan' => "-",
                'status_pernikahan' => 'Belum Menikah',
                'jenis_kelamin' => 'Laki-laki'
            ]);

            //////////////// teknisi ////////////////
            User::create([
                'nama' => 'Budi',
                'email' => 'budi@gmail.com',
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

            User::create([
                'nama' => 'Louis',
                'email' => 'louis@gmail.com',
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

            User::create([
                'nama' => 'Siddiq',
                'email' => 'siddiq@gmail.com',
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
