<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PeranSeeder::class);
        $this->call(DepartemenSeeder::class);
        $this->call(JabatanSeeder::class);
        $this->call(FiturSeeder::class);
        $this->call(HakAksesSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(KantorSeeder::class);
    }
}
