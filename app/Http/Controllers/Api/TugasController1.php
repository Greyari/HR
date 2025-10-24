<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Upload\UploadApi;

class TugasController1 extends Controller
{
    // ====== LIST SEMUA TUGAS ======
    public function index()
    {
        $user = Auth::user();
        $fiturUser = $user->peran->fitur->pluck('nama_fitur');

        if ($fiturUser->contains('lihat_semua_tugas')) {
            $tugas = Tugas::with('user')->latest()->get();
        } elseif ($fiturUser->contains('lihat_tugas_sendiri')) {
            $tugas = Tugas::with('user')->where('user_id', $user->id)->latest()->get();
        } else {
            return response()->json(['message' => 'Anda tidak punya akses untuk melihat tugas'], 403);
        }

        $tugas->transform(function ($item) {
            $item->lampiran = $item->lampiran ?: null;
            return $item;
        });

        return response()->json([
            'message' => 'Data tugas berhasil diambil',
            'data'    => $tugas
        ]);
    }

    // ====== SIMPAN TUGAS BARU ======
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'nama_tugas'          => 'required|string|max:255',
            'tanggal_penugasan'   => 'required|date',
            'batas_penugasan'     => 'required|date|after_or_equal:tanggal_penugasan',
            'instruksi_tugas'     => 'nullable|string',
            'tugas_lat'           => 'required|numeric',
            'tugas_lng'           => 'required|numeric',
            'radius_meter'        => 'required|integer|min:10',
        ]);

        $validated['status'] = 'Proses';

        $tugas = Tugas::create($validated);

        // 🔹 Kirim notifikasi tugas baru ke user
        NotificationHelper::sendTugasBaru(
            $tugas->user,
            'Tugas Baru Diberikan',
            'Anda mendapat tugas baru: ' . $tugas->nama_tugas,
            $tugas
        );

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data'    => $tugas->load('user')
        ], 201);
    }

    // ====== UPDATE TUGAS ======
    public function update(Request $request, $id)
    {
        $tugas = Tugas::find($id);
        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'user_id'             => 'sometimes|exists:users,id',
            'nama_tugas'          => 'sometimes|required|string|max:255',
            'tanggal_penugasan'   => 'sometimes|required|date',
            'batas_penugasan'     => 'sometimes|required|date|after_or_equal:tanggal_penugasan',
            'instruksi_tugas'     => 'nullable|string',
            'status'              => 'in:Proses,Selesai,Menunggu Admin',
            'tugas_lat'           => 'sometimes|numeric',
            'tugas_lng'           => 'sometimes|numeric',
            'radius_meter'        => 'sometimes|integer|min:10',
        ]);

        $userLama = $tugas->user;

        // 🔹 Jika PIC diganti
        if (isset($validated['user_id']) && $validated['user_id'] != $tugas->user_id) {
            $tugas->update($validated);

            // Kirim notifikasi ke user lama bahwa tugas dialihkan
            NotificationHelper::sendTugasDialihkan($userLama, $tugas);

            // Kirim notifikasi ke user baru bahwa ada tugas baru
            NotificationHelper::sendTugasBaru(
                $tugas->user,
                'Tugas Baru Diberikan',
                'Anda mendapat tugas baru: ' . $tugas->nama_tugas,
                $tugas
            );
        } else {
            // 🔹 Jika hanya update waktu / status / instruksi
            $tugas->update($validated);
            NotificationHelper::sendTugasUpdate($tugas->user, $tugas);
        }

        return response()->json([
            'message' => 'Tugas berhasil diperbarui',
            'data'    => $tugas->load('user')
        ]);
    }

    // ====== HAPUS TUGAS ======
    public function destroy($id)
    {
        $tugas = Tugas::find($id);
        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        if ($tugas->lampiran) {
            try {
                $publicId = pathinfo(parse_url($tugas->lampiran)['path'], PATHINFO_FILENAME);
                (new AdminApi())->deleteAssets([$publicId]);
            } catch (\Exception $e) {
                // Abaikan error hapus cloudinary
            }
        }

        // 🔹 Kirim notifikasi ke user jika tugas belum selesai
        if ($tugas->user && $tugas->status !== 'Selesai') {
            NotificationHelper::sendTugasDihapus($tugas->user, $tugas);
        }

        $tugas->delete();

        return response()->json(['message' => 'Tugas berhasil dihapus']);
    }

    // ====== UPLOAD LAMPIRAN ======
    public function uploadLampiran(Request $request, $id)
    {
        $request->validate([
            'lampiran'     => 'required|file|max:204800',
            'lampiran_lat' => 'required|numeric',
            'lampiran_lng' => 'required|numeric',
        ]);

        $tugas = Tugas::findOrFail($id);

        $distance = $this->calculateDistance(
            $tugas->tugas_lat, $tugas->tugas_lng,
            $request->lampiran_lat, $request->lampiran_lng
        );

        if ($distance > $tugas->radius_meter) {
            return response()->json([
                'message' => 'Upload gagal. Lokasi Anda berada di luar radius tugas (' . round($distance, 2) . ' m).',
            ], 403);
        }

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $ext = strtolower($file->getClientOriginalExtension());
            $folder = match ($ext) {
                'mp4', 'mov', 'avi', '3gp' => 'tugas/videos',
                'jpg', 'jpeg', 'png'       => 'tugas/images',
                default                    => 'tugas/files',
            };

            $result = (new UploadApi())->upload(
                $file->getRealPath(),
                [
                    'resource_type' => in_array($ext, ['jpg','jpeg','png']) ? 'image' :
                        (in_array($ext, ['mp4','mov','avi','3gp']) ? 'video' : 'raw'),
                    'folder' => $folder,
                ]
            );

            $tugas->lampiran = $result['secure_url'];
        }

        $tugas->lampiran_lat = $request->lampiran_lat;
        $tugas->lampiran_lng = $request->lampiran_lng;
        $tugas->status = "Menunggu Admin";
        $tugas->waktu_upload = now();
        $tugas->save();

        // 🔹 Kirim notifikasi ke admin
        NotificationHelper::sendToFitur(
            'lihat_semua_tugas',
            'Lampiran Baru Dikirim',
            'User ' . $tugas->user->name . ' mengunggah hasil tugas "' . $tugas->nama_tugas . '".',
            'tugas'
        );

        // 🔹 Kirim notifikasi ke user (hapus progres bar + tunggal)
        NotificationHelper::sendLampiranDikirim($tugas->user, $tugas);

        return response()->json([
            'message' => 'Lampiran berhasil diupload!',
            'data'    => $tugas,
        ]);
    }

    // ====== HITUNG JARAK (METER) ======
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
