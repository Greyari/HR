<?php

namespace Database\Seeders;

use App\Models\Peran;
use Illuminate\Database\Seeder;

class PeranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Peran::create([
            'nama_peran' => 'Admin Super',
        ]);

        Peran::create([
            'nama_peran' => 'Admin Office Website',
        ]);

        Peran::create([
            'nama_peran' => 'Admin Office Aplikasi',
        ]);

        Peran::create([
            'nama_peran' => 'Teknisi Website',
        ]);

        Peran::create([
            'nama_peran' => 'Teknisi Aplikasi',
        ]);

        Peran::create([
            'nama_peran' => 'Marketing Website',
        ]);

        Peran::create([
            'nama_peran' => 'Marketing Aplikasi',
        ]);

        Peran::create([
            'nama_peran' => 'Magang Aplikasi',
        ]);

        Peran::create([
            'nama_peran' => 'Magang Website',
        ]);
    }
}
