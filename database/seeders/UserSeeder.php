<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Peran;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $peran = Peran::where('nama_peran', 'Super Admin')->first();

        if ($peran) {
            User::create([
                'nama' => 'Grey Ari',
                'email' => 'grey@gmail.com',
                'password' => Hash::make('123'),
                'peran_id' => $peran->id,
            ]);
        } else {
            echo "Peran 'General Manager' tidak ditemukan di tabel peran.\n";
        }
    }
}
