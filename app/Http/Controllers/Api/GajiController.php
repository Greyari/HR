<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gaji;
use App\Models\PotonganGaji;
use Illuminate\Http\Request;

class GajiController extends Controller
{
    // Hitung gaji semua user sekaligus
    public function calculateAll()
    {
        $users = User::all();
        $potonganList = PotonganGaji::all();

        foreach ($users as $user) {
            $gajiPokok = $user->gaji_pokok ?? 0;
            $totalLembur = 0;
            $totalPotongan = 0;
            $detailPotongan = [];

            foreach ($potonganList as $potongan) {
                $nilaiPotongan = ($gajiPokok * $potongan->persen / 100);
                $totalPotongan += $nilaiPotongan;
                $detailPotongan[] = [
                    'nama_potongan' => $potongan->nama_potongan,
                    'persen' => $potongan->persen,
                    'nilai' => $nilaiPotongan,
                ];
            }

            $gajiBersih = $gajiPokok + $totalLembur - $totalPotongan;

                $gaji = Gaji::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gaji_pokok' => $gajiPokok,
                    'total_lembur' => $totalLembur,
                    'gaji_bersih' => $gajiBersih,
                ]
            );

            // Tambahkan ke hasil response
            $result[] = [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                ],
                'gaji_pokok' => $gajiPokok,
                'total_lembur' => $totalLembur,
                'potongan' => $detailPotongan,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $gajiBersih,
                'status' => $gaji->status
            ];
        }

        return response()->json([
            'message' => 'Semua gaji berhasil dihitung dan disimpan',
            'data' => $result
        ]);
    }

    // update status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Sudah Dibayar,Belum Dibayar',
        ]);

        $gaji = Gaji::findOrFail($id);
        $gaji->status = $request->status;
        $gaji->save();

        return response()->json([
            'message' => 'Status gaji berhasil diperbarui',
            'data' => $gaji
        ]);
    }

}
