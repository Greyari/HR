<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class Jabatanseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Jabatan::create([
            'nama_jabatan' => 'Presiden',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'GM',
        ]);
    }
}
