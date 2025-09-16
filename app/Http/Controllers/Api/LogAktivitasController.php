<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    // Nampilin log aktivitas berdasarkan bulan & tahun
    public function availableMonths()
    {
        $logs = LogAktivitas::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json($logs);
    }

    // Hapus log aktivitas berdasarkan bulan & tahun
    public function resetByMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $query = LogAktivitas::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan);

        // cek apakah ada data
        if (!$query->exists()) {
            return response()->json([
                'message' => "Log aktivitas bulan $bulan tahun $tahun tidak ditemukan"
            ], 404);
        }

        // hapus data
        $deleted = $query->delete();

        return response()->json([
            'message' => "Sebanyak $deleted log aktivitas bulan $bulan tahun $tahun berhasil dihapus"
        ]);
    }
    
}
