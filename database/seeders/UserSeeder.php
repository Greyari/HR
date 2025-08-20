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
        $peranSA = Peran::where('nama_peran', 'Super Admin')->first();
        $peranAO = Peran::where('nama_peran', 'Admin Office')->first();
        $peranT = Peran::where('nama_peran', 'Technical')->first();
        $jabatanGM = Jabatan::where('nama_jabatan', 'GM')->first();
        $jabatanPR = Jabatan::where('nama_jabatan', 'Presiden')->first();
        $departemenIT = Departemen::where('nama_departemen', 'IT')->first();
        $departemenOffice = Departemen::where('nama_departemen', 'Office')->first();


            User::create([
                'nama' => 'Grey Ari',
                'email' => 'grey@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanGM->id,
                'peran_id' => $peranSA->id,
                'departemen_id' => $departemenIT->id,
                'gaji_pokok' => 900,
                'npwp' => 555,
                'bpjs_kesehatan' => 8829,
                'bpjs_ketenagakerjaan' => 9090
            ]);

            User::create([
                'nama' => 'Haikal',
                'email' => 'haikal@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanGM->id,
                'peran_id' => $peranAO->id,
                'departemen_id' => $departemenIT->id,
                'gaji_pokok' => 100,
                'npwp' => 545,
                'bpjs_kesehatan' => 4321,
                'bpjs_ketenagakerjaan' => 8080

            ]);

            User::create([
                'nama' => 'Zidan',
                'email' => 'zidan@gmail.com',
                'password' => Hash::make('123'),
                'jabatan_id' => $jabatanPR->id,
                'peran_id' => $peranT->id,
                'departemen_id' => $departemenOffice->id,
                'gaji_pokok' => 700,
                'npwp' => 590,
                'bpjs_kesehatan' => 1234,
                'bpjs_ketenagakerjaan' => 7070
            ]);
    }
}
