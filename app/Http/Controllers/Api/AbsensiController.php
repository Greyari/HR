<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Kantor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{

    /**
     * Ambil semua data absensi
     */
    public function getAbsensi()
    {
        $user = Auth::user();
        $fiturUser = $user->peran->fitur->pluck('nama_fitur');

        if ($fiturUser->contains('lihat_semua_absensi')) {
            // Bisa lihat semua absensi
            $absensiList = Absensi::with('user')
                ->orderBy('checkin_time', 'desc')
                ->get();
        } elseif ($fiturUser->contains('lihat_absensi_sendiri')) {
            // Hanya bisa lihat absensi miliknya sendiri
            $absensiList = Absensi::with('user')
                ->where('user_id', $user->id)
                ->orderBy('checkin_time', 'desc')
                ->get();
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Anda tidak punya akses untuk melihat absensi'
            ], 403);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Data absensi berhasil diambil',
            'data'    => $absensiList
        ]);
    }

    /**
     * Check-in
     */
    public function checkin(Request $request)
    {
        Log::info('RAW Request masuk ke checkin', [
            'all'   => $request->all(),
            'files' => $request->allFiles(),
            'headers' => $request->headers->all(),
        ]);

        $user = Auth::user();

        $request->validate([
            'lat'          => 'required|numeric',
            'lng'          => 'required|numeric',
            'checkin_date' => 'required|string',
            'checkin_time' => 'required|string',
            'video_user'   => 'nullable|mimes:mp4,avi,mpeg,mov|max:51200',
            'video_base64' => 'nullable|string',
        ]);

        // Debug log untuk Railway
        Log::info('Checkin request received', [
            'files'       => $request->allFiles(),
            'has_video'   => $request->hasFile('video_user') ? 'yes' : 'no',
            'keys'        => array_keys($request->all()),
        ]);

        Log::info('Checkin Debug', [
            'auth'        => $request->header('Authorization'),
            'contentType' => $request->header('Content-Type'),
            'keys'        => array_keys($request->all()),
            'hasFile'     => $request->hasFile('video_user'),
            'files'       => $request->allFiles(),
        ]);


        $kantor = Kantor::first();

        // Konversi tanggal & waktu
        $checkinDate = Carbon::createFromFormat('d/m/Y', $request->checkin_date)->format('Y-m-d');
        $checkinTime = Carbon::createFromFormat('H:i', $request->checkin_time)->format('H:i:s');

        // Hitung jarak
        $jarak = $this->hitungJarak($request->lat, $request->lng, $kantor->lat, $kantor->lng);
        if ($jarak > $kantor->radius_meter) {
            return response()->json([
                'status'  => false,
                'message' => 'Anda berada di luar radius kantor!'
            ], 403);
        }

        // Cek apakah sudah absen
        $absensi = Absensi::where('user_id', $user->id)
            ->whereDate('checkin_date', $checkinDate)
            ->first();

        if ($absensi) {
            return response()->json([
                'status'  => false,
                'message' => 'Anda sudah melakukan check-in pada tanggal ini.'
            ], 400);
        }

        // Gabungkan date + time untuk evaluasi keterlambatan
        $checkinDateTime = Carbon::parse($checkinDate . ' ' . $checkinTime);
        $toleransi = Carbon::parse($kantor->jam_masuk)
            ->addMinutes($kantor->minimal_keterlambatan);

        $status = $checkinDateTime->lte($toleransi) ? 'Tepat Waktu' : 'Terlambat';

        // ================================
        // Upload video ke Lokal
        // ================================
        $videoUrl = null;

        try {
            if ($request->hasFile('video_user')) {
                // simpan di disk public
                $path = $request->file('video_user')->store('absensi/video', 'public');
                $videoUrl = Storage::url($path);
            } elseif ($request->filled('video_base64')) {
                $videoData = base64_decode($request->video_base64);
                $fileName = uniqid() . '.mp4';
                $filePath = 'absensi/' . $fileName;
                Storage::disk('public')->put($filePath, $videoData);
                $videoUrl = Storage::url($filePath);
            }

        } catch (\Exception $e) {
            Log::error('Upload video gagal: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Upload video gagal: ' . $e->getMessage(),
            ], 500);
        }

        // Simpan absensi baru
        $absensi = Absensi::create([
            'user_id'      => $user->id,
            'checkin_lat'  => $request->lat,
            'checkin_lng'  => $request->lng,
            'checkin_date' => $checkinDate,
            'checkin_time' => $checkinTime,
            'video_user'   => $videoUrl,
            'status'       => $status,
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Check-in berhasil',
            'data'      => $absensi,
            'video_url' => $videoUrl,
        ]);
    }

    /**
     * Check-out
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'lat'           => 'required|numeric',
            'lng'           => 'required|numeric',
            'checkout_date' => 'required|string',
            'checkout_time' => 'required|string',
        ]);

        // Konversi dari format Flutter ke format MySQL
        $checkoutDate = Carbon::createFromFormat('d/m/Y', $request->checkout_date)->format('Y-m-d');
        $checkoutTime = Carbon::createFromFormat('H:i', $request->checkout_time)->format('H:i:s');

        $absensi = Absensi::where('user_id', $user->id)
            ->whereDate('checkin_date', $checkoutDate)
            ->first();

        if (!$absensi) {
            return response()->json([
                'status' => false,
                'message' => 'Anda belum melakukan check-in pada tanggal ini.'
            ], 400);
        }

        if ($absensi->checkout_time) {
            return response()->json([
                'status' => false,
                'message' => 'Anda sudah melakukan check-out hari ini.'
            ], 400);
        }

        $absensi->update([
            'checkout_lat'  => $request->lat,
            'checkout_lng'  => $request->lng,
            'checkout_date' => $checkoutDate,
            'checkout_time' => $checkoutTime,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Check-out berhasil',
            'data' => $absensi
        ]);
    }

    /**
     * Hitung jarak (Haversine formula)
     */
    private function hitungJarak($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
}
