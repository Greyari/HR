<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KantorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kantor')->insert([
            'jam_masuk'             => '08:00:00',
            'jam_keluar'            => '17:00:00',
            'minimal_keterlambatan' => 10,
            'lat'                   => 1.125027673628618,
            'lng'                   => 104.029007793717,
            'radius_meter'          => 100,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }
}
