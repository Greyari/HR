<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Models\Lembur;
use App\Models\Gaji;
use App\Models\Tugas;
use App\Models\LogAktivitas;

class DengerController extends Controller
{
    // ============================
    // CUTI
    // ============================
    public function resetCutiByMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $query = Cuti::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan);

        if (!$query->exists()) {
            return response()->json([
                'message' => "Data cuti bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => "Sebanyak $deleted data cuti bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus"
        ]);
    }

    public function availableCutiMonths()
    {
        $data = Cuti::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json($data);
    }

    // ============================
    // LEMBUR
    // ============================
    public function resetLemburByMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $query = Lembur::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan);

        if (!$query->exists()) {
            return response()->json([
                'message' => "Data lembur bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => "Sebanyak $deleted data lembur bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus"
        ]);
    }

    public function availableLemburMonths()
    {
        $data = Lembur::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json($data);
    }

    // ============================
    // GAJI
    // ============================
    public function resetGajiByMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $query = Gaji::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan);

        if (!$query->exists()) {
            return response()->json([
                'message' => "Data gaji bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => "Sebanyak $deleted data gaji bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus"
        ]);
    }

    public function availableGajiMonths()
    {
        $data = Gaji::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json($data);
    }

    // ============================
    // TUGAS
    // ============================
    public function resetTugasByMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $query = Tugas::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan);

        if (!$query->exists()) {
            return response()->json([
                'message' => "Data tugas bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => "Sebanyak $deleted data tugas bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus"
        ]);
    }

    public function availableTugasMonths()
    {
        $data = Tugas::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json($data);
    }

    // ============================
    // LOG AKTIVITAS
    // ============================
    public function resetLogByMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $query = LogAktivitas::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan);

        if (!$query->exists()) {
            return response()->json([
                'message' => "Log aktivitas bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => "Sebanyak $deleted log aktivitas bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus"
        ]);
    }

    public function availableLogMonths()
    {
        $data = LogAktivitas::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json($data);
    }

    // ============================
    // Absen AKTIVITAS
    // ============================
    public function resetAbsenByMonth(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $query = Absensi::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan);

        if (!$query->exists()) {
            return response()->json([
                'message' => "Absensi bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        $deleted = $query->delete();

        return response()->json([
            'message' => "Sebanyak $deleted absensi bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus"
        ]);
    }

    public function availableAbsenMonths()
    {
        $data = Absensi::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json($data);
    }
}
