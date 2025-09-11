<?php

namespace Database\Seeders;

use App\Models\Fitur;
use App\Models\Peran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HakAksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil peran Super Admin
        $superAdmin = Peran::where('nama_peran', 'Super Admin')->first();

        // Ambil semua fitur kecuali approve step 1
        $fiturList = Fitur::whereNotIn('nama_fitur', [
            'approve_cuti_step1',
            'approve_lembur_step1',
            'lihat_lembur_sendiri',
            'lihat_cuti_sendiri',
            'lihat_tugas_sendiri',
            'tambah_lampiran_tugas',
        ])->get();

        foreach ($fiturList as $fitur) {
            DB::table('izin_fitur')->updateOrInsert(
                [
                    'peran_id' => $superAdmin->id,
                    'fitur_id' => $fitur->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
