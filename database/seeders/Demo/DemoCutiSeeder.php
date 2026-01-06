<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use App\Models\Cuti;
use App\Models\User;
use Carbon\Carbon;

class DemoCutiSeeder extends Seeder
{
    public function run(): void
    {
        Cuti::truncate();

        $user = User::first();

        Cuti::create([
            'user_id' => $user->id,
            'tipe_cuti' => 'Sakit',
            'tanggal_mulai' => Carbon::now()->addDays(3),
            'tanggal_selesai' => Carbon::now()->addDays(5),
            'alasan' => 'Cuti demo untuk sidang',
            'status' => 'disetujui',
            'approval_step' => 2,
        ]);

        Cuti::create([
            'user_id' => $user->id,
            'tipe_cuti' => 'Izin',
            'tanggal_mulai' => Carbon::now()->addDays(3),
            'tanggal_selesai' => Carbon::now()->addDays(5),
            'alasan' => 'Cuti demo untuk sidang',
            'status' => 'disetujui',
            'approval_step' => 3,
        ]);
    }
}
