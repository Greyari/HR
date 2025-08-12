<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PeranSeeder::class);
        $this->call(DepartemenSeeder::class);
        $this->call(UserSeeder::class);
    }
}
