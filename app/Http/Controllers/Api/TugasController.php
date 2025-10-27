<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Upload\UploadApi;

class TugasController extends Controller
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

        // Kirim notifikasi ke user yang ditugaskan
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
        $tugas = Tugas::with('user')->find($id);

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

        $userLama = $tugas->user; // simpan PIC lama sebelum diubah
        $isPICChanged = isset($validated['user_id']) && $userLama->id !== $validated['user_id'];

        // Jalankan update ke database
        $tugas->update($validated);

        // Refresh agar relasi dan data terbarui
        $tugas->refresh();
        $tugas->load('user');

        // ==== CEK APAKAH PIC DIGANTI ====
        if ($isPICChanged) {
            // 🔸 Kirim notif ke user lama bahwa tugas sudah dialihkan
            // ✅ PENTING: Pastikan user lama masih ada device_token
            if ($userLama->device_token) {
                NotificationHelper::sendTugasDialihkan($userLama, $tugas);
            }

            // 🔸 Kirim notif ke user baru bahwa dia dapat tugas baru
            if ($tugas->user->device_token) {
                NotificationHelper::sendTugasBaru(
                    $tugas->user,
                    'Tugas Baru Diberikan',
                    'Anda mendapat tugas baru: ' . $tugas->nama_tugas,
                    $tugas
                );
            }

        } else {
            // 🔹 Jika tidak ada pergantian PIC, berarti cuma update biasa
            if ($tugas->user->device_token) {
                NotificationHelper::sendTugasUpdate($tugas->user, $tugas);
            }
        }

        return response()->json([
            'message' => 'Tugas berhasil diperbarui',
            'data'    => $tugas
        ]);
    }

    // ====== UPDATE STATUS ======
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Selesai,Menunggu Admin,Proses,Ditolak'
        ]);

        $tugas = Tugas::with('user')->findOrFail($id);
        $statusLama = $tugas->status;
        $tugas->status = $request->status;
        $tugas->save();

        // 🔹 Kirim notifikasi ke user berdasarkan perubahan status
        if ($tugas->user && $tugas->user->device_token) {
            // Jika status diubah menjadi Selesai
            if ($request->status === 'Selesai' && $statusLama !== 'Selesai') {
                NotificationHelper::sendTugasSelesai($tugas->user, $tugas);
            }
            // // Jika status diubah menjadi Ditolak
            // elseif ($request->status === 'Ditolak') {
            //     NotificationHelper::sendTugasDitolak($tugas->user, $tugas);
            // }

            // Untuk status lainnya (Proses, Menunggu Admin)
            else {
                NotificationHelper::sendToUser(
                    $tugas->user,
                    'Status Tugas Diperbarui',
                    'Status tugas "' . $tugas->nama_tugas . '" diubah menjadi: ' . $tugas->status,
                    'tugas'
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diperbarui',
            'data'    => $tugas
        ]);
    }


    // ====== HAPUS TUGAS ======
    public function destroy($id)
    {
        $tugas = Tugas::with('user')->find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        // ✅ Simpan data user sebelum tugas dihapus
        $tugasUser = $tugas->user;
        $tugasStatus = $tugas->status;
        $tugasNama = $tugas->nama_tugas;
        $tugasId = $tugas->id;

        // Hapus lampiran dari Cloudinary jika ada
        if ($tugas->lampiran) {
            try {
                $publicId = pathinfo(parse_url($tugas->lampiran)['path'], PATHINFO_FILENAME);
                (new AdminApi())->deleteAssets([$publicId]);
            } catch (\Exception $e) {
                // jika gagal hapus, abaikan
            }
        }

        // Hapus tugas dari database
        $tugas->delete();

        // 🔹 Kirim notifikasi ke user SETELAH tugas dihapus (jika belum selesai)
        if ($tugasUser && $tugasUser->device_token && $tugasStatus !== 'Selesai') {
            // Buat object temporary untuk notifikasi
            $tugasTemp = (object) [
                'id' => $tugasId,
                'nama_tugas' => $tugasNama,
                'status' => $tugasStatus,
                'user' => $tugasUser
            ];

            NotificationHelper::sendTugasDihapus($tugasUser, $tugasTemp);
        }

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

        if (!$tugas->tugas_lat || !$tugas->tugas_lng) {
            return response()->json(['message' => 'Tugas ini belum memiliki lokasi koordinat.'], 422);
        }

        $distance = $this->calculateDistance(
            $tugas->tugas_lat,
            $tugas->tugas_lng,
            $request->lampiran_lat,
            $request->lampiran_lng
        );

        if ($distance > $tugas->radius_meter) {
            return response()->json([
                'message' => 'Upload gagal. Lokasi Anda berada di luar radius tugas (' . round($distance, 2) . ' m).',
            ], 403);
        }

        $tugas->lampiran_lat = $request->lampiran_lat;
        $tugas->lampiran_lng = $request->lampiran_lng;

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = strtolower($file->getClientOriginalExtension());

            $folder = match ($extension) {
                'mp4', 'mov', 'avi', '3gp' => 'tugas/videos',
                'jpg', 'jpeg', 'png'       => 'tugas/images',
                default                    => 'tugas/files',
            };

            $result = (new UploadApi())->upload(
                $file->getRealPath(),
                [
                    'resource_type' => in_array($extension, ['jpg', 'jpeg', 'png']) ? 'image' :
                                      (in_array($extension, ['mp4', 'mov', 'avi', '3gp']) ? 'video' : 'raw'),
                    'folder'        => $folder,
                ]
            );

            $tugas->lampiran = $result['secure_url'];

            // === CATAT WAKTU UPLOAD & HITUNG KETERLAMBATAN ===
            $now = now();
            $tugas->waktu_upload = $now;

            // Jika lewat dari batas penugasan → terlambat
            if ($now->gt($tugas->batas_penugasan)) {
                $diffInMinutes = $tugas->batas_penugasan->diffInMinutes($now);
                $tugas->terlambat = true;
                $tugas->menit_terlambat = $diffInMinutes;
            } else {
                $tugas->terlambat = false;
                $tugas->menit_terlambat = 0;
            }

            $tugas->status = "Menunggu Admin";
            $tugas->save();
        }

        // Kirim notifikasi ke admin
        NotificationHelper::sendToFitur(
            'lihat_semua_tugas',
            'Lampiran Baru Dikirim',
            'User ' . $tugas->user->name . ' mengunggah hasil tugas "' . $tugas->nama_tugas . '".',
            'tugas'
        );

        // Kirim notifikasi ke user (hapus progres bar + tunggal)
        NotificationHelper::sendLampiranDikirim($tugas->user, $tugas);

        return response()->json([
            'message'         => 'Lampiran berhasil diupload!',
            'data'            => $tugas,
            'file_url'        => $tugas->lampiran,
            'terlambat'       => $tugas->terlambat,
            'menit_terlambat' => $tugas->menit_terlambat,
            'waktu_upload'    => $tugas->waktu_upload,
        ]);
    }

    // ====== HITUNG JARAK (METER) ======
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
