<?php

namespace Database\Seeders;

use App\Models\Departemen;
use Illuminate\Database\Seeder;

class DepartemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Departemen::create([
            'nama_departemen' => 'IT',
        ]);

        Departemen::create([
            'nama_departemen' => 'Office',
        ]);

        Departemen::create([
            'nama_departemen' => 'Maintenance',
        ]);

        Departemen::create([
            'nama_departemen' => 'Marketing',
        ]);
    }
}
