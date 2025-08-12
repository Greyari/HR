<?php

namespace Database\Seeders;

use App\Models\Departemen;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Peran;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $peranSA = Peran::where('nama_peran', 'Super Admin')->first();
        $peranWA = Peran::where('nama_peran', 'Wakil Direktur')->first();
        $departemenIT = Departemen::where('nama_departemen', 'IT')->first();
        $departemenOffice = Departemen::where('nama_departemen', 'Office')->first();


            User::create([
                'nama' => 'Grey Ari',
                'email' => 'grey@gmail.com',
                'password' => Hash::make('123'),
                'peran_id' => $peranSA->id,
                'departemen_id' => $departemenIT->id,
            ]);

            User::create([
                'nama' => 'Haikal',
                'email' => 'haikal@gmail.com',
                'password' => Hash::make('123'),
                'peran_id' => $peranWA->id,
                'departemen_id' => $departemenIT->id,
            ]);

            User::create([
                'nama' => 'Zidan',
                'email' => 'zidan@gmail.com',
                'password' => Hash::make('123'),
                'peran_id' => $peranWA->id,
                'departemen_id' => $departemenOffice->id,
            ]);

    }
}
