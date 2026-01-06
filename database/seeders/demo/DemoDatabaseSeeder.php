<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoUserSeeder::class,
            DemoCutiSeeder::class,
            DemoLemburSeeder::class,
            DemoPengingatSeeder::class,
            DemoTugasSeeder::class,
        ]);
    }
}
