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
use Illuminate\Support\Facades\Storage;
use App\Events\DataResetEvent;
use Illuminate\Support\Facades\Log;


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

        // Trigger Observer Event
        DataResetEvent::dispatch(
            'cuti',
            $request->bulan,
            $request->tahun,
            $deleted,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );


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
            ->get()
            ->makeHidden(['keterangan_status']);

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

        // Trigger Observer Event
        DataResetEvent::dispatch(
            'lembur',
            $request->bulan,
            $request->tahun,
            $deleted,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );


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
            ->get()
            ->makeHidden(['keterangan_status']);

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

        // Trigger Observer Event
        DataResetEvent::dispatch(
            'gaji',
            $request->bulan,
            $request->tahun,
            $deleted,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => "Sebanyak $deleted data gaji bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus"
        ]);
    }

    public function availableGajiMonths()
    {
        $data = Gaji::selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as jumlah')
                    ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                    ->orderByDesc('tahun')
                    ->orderByDesc('bulan')
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

        $tugasList = Tugas::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan)
            ->get();

        if ($tugasList->isEmpty()) {
            return response()->json([
                'message' => "Data tugas bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        // Hapus file storage satu per satu
        foreach ($tugasList as $tugas) {
            if ($tugas->lampiran) {
                $parsed = parse_url($tugas->lampiran, PHP_URL_PATH);
                $filePath = str_replace('/storage/', '', $parsed); // hasil: "tugas/images/xxx.png"
                Storage::disk('public')->delete($filePath);
            }
        }

        // Hapus data di database
        $deletedCount = Tugas::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan)
            ->delete();

        // Trigger Observer Event
        DataResetEvent::dispatch(
            'tugas',
            $request->bulan,
            $request->tahun,
            $deletedCount,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => "Sebanyak $deletedCount data tugas + semua file lampiran berhasil dihapus."
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
        Log::info("=== DEBUG: CONTROLLER DISPATCH EVENT ===", [
            'route' => $request->path(),
            'module' => 'cuti/lembur/gaji/tugas/log/absen'
        ]);

        // Trigger Observer Event
        DataResetEvent::dispatch(
            'log aktivitas',
            $request->bulan,
            $request->tahun,
            $deleted,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );


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

        $absensis = Absensi::whereYear('created_at', $request->tahun)
            ->whereMonth('created_at', $request->bulan)
            ->get();

        if ($absensis->isEmpty()) {
            return response()->json([
                'message' => "Absensi bulan {$request->bulan} tahun {$request->tahun} tidak ditemukan"
            ], 404);
        }

        // Hapus file videonya dulu
        foreach ($absensis as $absen) {
            if (!empty($absen->video_user)) {
                // Convert URL ke path relatif yang bisa dihapus
                $filePath = str_replace('/storage/', '', $absen->video_user);
                Storage::disk('public')->delete($filePath);
            }
        }

        // Baru hapus data dari database
        $deleted = Absensi::whereIn('id', $absensis->pluck('id'))->delete();

        // Trigger Observer Event
        DataResetEvent::dispatch(
            'absensi',
            $request->bulan,
            $request->tahun,
            $deleted,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message' => "Sebanyak $deleted absensi bulan {$request->bulan} tahun {$request->tahun} berhasil dihapus (beserta videonya)"
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
