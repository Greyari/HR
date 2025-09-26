<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Jabatan::create([
            'nama_jabatan' => 'Sales Executive',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'Telemarketing',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'General Manager',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'Admin Sales',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'Admin Office Senior',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'Marketing Intern',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'IT Intern',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'Teknisi Senior',
        ]);

        Jabatan::create([
            'nama_jabatan' => 'Teknisi Intern',
        ]);
    }
}
