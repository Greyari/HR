<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;

class DemoTugasSeeder extends Seeder
{
    public function run(): void
    {
        Tugas::truncate();

        $user = User::where('email', 'karyawan@demo.local')->first();

        Tugas::updateOrCreate([
            'user_id' => $user->id,
            'nama_tugas' => 'Input absensi demo',
            'tanggal_penugasan' => Carbon::now(),
            'batas_penugasan' => Carbon::now()->addDays(1),
            'instruksi_tugas' => 'Lakukan absensi menggunakan GPS',
            'status' => 'proses',
            'terlambat' => false,
            'nama_lokasi_penugasan' => 'Kantor Demo',
        ]);
    }
}
