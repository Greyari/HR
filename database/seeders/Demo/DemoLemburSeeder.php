<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use App\Models\Lembur;
use App\Models\User;
use Carbon\Carbon;

class DemoLemburSeeder extends Seeder
{
    public function run(): void
    {
        Lembur::truncate();

        $user = User::first();

        Lembur::updateOrCreate([
            'user_id' => $user->id,
            'tanggal' => Carbon::yesterday(),
            'jam_mulai' => '18:00',
            'jam_selesai' => '21:00',
            'deskripsi' => 'Lembur demo akhir bulan',
            'status' => 'disetujui',
            'approval_step' => 2,
        ]);
    }
}
