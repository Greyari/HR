<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use App\Models\Pengingat;
use Carbon\Carbon;

class DemoPengingatSeeder extends Seeder
{
    public function run(): void
    {
        Pengingat::truncate();

        Pengingat::updateOrCreate(
            ['peran_id' => 1],
            [
            'judul' => 'Pengingat Sidang',
            'deskripsi' => 'Pastikan semua fitur HRIS siap demo',
            'tanggal_jatuh_tempo' => Carbon::now()->addDays(1),
            'status' => 'Selesai',
        ]);
    }
}
