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
            'nama_peran' => 'Super Admin',
        ]);
    }
}
