<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\Tugas;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    /**
     * Ambil bahasa user dari tabel pengaturan
     */
    private function getUserLanguage()
    {
        $user = Auth::user();
        return Pengaturan::where('user_id', $user->id)->value('bahasa') ?? 'indonesia';
    }

    // ====== LIST SEMUA TUGAS ======
    public function index()
    {
        $user = Auth::user();
        $bahasa = $this->getUserLanguage();
        $fiturUser = $user->peran->fitur->pluck('nama_fitur');

        if ($fiturUser->contains('lihat_semua_tugas')) {
            $tugas = Tugas::with('user')->latest()->get();
        } elseif ($fiturUser->contains('lihat_tugas_sendiri')) {
            $tugas = Tugas::with('user')->where('user_id', $user->id)->latest()->get();
        } else {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Anda tidak punya akses untuk melihat tugas'
                    : 'You do not have access to view tasks'
            ], 403);
        }

        $tugas->transform(function ($item) {
            $item->lampiran = $item->lampiran ?: null;
            return $item;
        });

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Data tugas berhasil diambil'
                : 'Task data retrieved successfully',
            'data' => $tugas
        ]);
    }

    // ====== SIMPAN TUGAS BARU ======
    public function store(Request $request)
    {
        $bahasa = $this->getUserLanguage();

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

        // Kirim notifikasi
        NotificationHelper::sendTugasBaru(
            $tugas->user,
            $bahasa === 'indonesia' ? 'Tugas Baru Diberikan' : 'New Task Assigned',
            ($bahasa === 'indonesia'
                ? 'Anda mendapat tugas baru: '
                : 'You have received a new task: ') . $tugas->nama_tugas,
            $tugas
        );

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Tugas berhasil dibuat'
                : 'Task created successfully',
            'data' => $tugas->load('user')
        ], 201);
    }

    // ====== UPDATE TUGAS ======
    public function update(Request $request, $id)
    {
        $bahasa = $this->getUserLanguage();

        $tugas = Tugas::with('user')->find($id);

        if (!$tugas) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Tugas tidak ditemukan'
                    : 'Task not found',
            ], 404);
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
        $isPICChanged = isset($validated['user_id']) && $userLama->id !== $validated['user_id'];

        $tugas->update($validated);
        $tugas->refresh()->load('user');

        // Kirim notifikasi tergantung perubahan PIC
        if ($isPICChanged) {
            if ($userLama->device_token) {
                NotificationHelper::sendTugasDialihkan($userLama, $tugas);
            }
            if ($tugas->user->device_token) {
                NotificationHelper::sendTugasBaru(
                    $tugas->user,
                    $bahasa === 'indonesia' ? 'Tugas Baru Diberikan' : 'New Task Assigned',
                    ($bahasa === 'indonesia'
                        ? 'Anda mendapat tugas baru: '
                        : 'You have received a new task: ') . $tugas->nama_tugas,
                    $tugas
                );
            }
        } else {
            if ($tugas->user->device_token) {
                NotificationHelper::sendTugasUpdate($tugas->user, $tugas);
            }
        }

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Tugas berhasil diperbarui'
                : 'Task updated successfully',
            'data' => $tugas
        ]);
    }

    // ====== UPDATE STATUS ======
    public function updateStatus(Request $request, $id)
    {
        $bahasa = $this->getUserLanguage();

        $request->validate([
            'status' => 'required|in:Selesai,Menunggu Admin,Proses,Ditolak'
        ]);

        $tugas = Tugas::with('user')->findOrFail($id);
        $statusLama = $tugas->status;
        $tugas->status = $request->status;
        $tugas->save();

        if ($tugas->user && $tugas->user->device_token) {
            if ($request->status === 'Selesai' && $statusLama !== 'Selesai') {
                NotificationHelper::sendTugasSelesai($tugas->user, $tugas);
            } elseif ($request->status === 'Proses' && $statusLama !== 'Proses') {
                NotificationHelper::sendTugasDiproses($tugas->user, $tugas);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $bahasa === 'indonesia'
                ? 'Status tugas berhasil diperbarui'
                : 'Task status updated successfully',
            'data' => $tugas
        ]);
    }

    // ====== HAPUS TUGAS ======
    public function destroy($id)
    {
        $bahasa = $this->getUserLanguage();
        $tugas = Tugas::with('user')->find($id);

        if (!$tugas) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Tugas tidak ditemukan'
                    : 'Task not found',
            ], 404);
        }

        $tugasUser = $tugas->user;
        $tugasStatus = $tugas->status;
        $tugasNama = $tugas->nama_tugas;
        $tugasId = $tugas->id;

        if ($tugas->lampiran) {
            $filePath = str_replace('/storage/', '', $tugas->lampiran);
            Storage::disk('public')->delete($filePath);
        }

        $tugas->delete();

        if ($tugasUser && $tugasUser->device_token && $tugasStatus !== 'Selesai') {
            $tugasTemp = (object)[
                'id' => $tugasId,
                'nama_tugas' => $tugasNama,
                'status' => $tugasStatus,
                'user' => $tugasUser
            ];
            NotificationHelper::sendTugasDihapus($tugasUser, $tugasTemp);
        }

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Tugas berhasil dihapus'
                : 'Task deleted successfully',
        ]);
    }

    // ====== UPLOAD LAMPIRAN ======
    public function uploadLampiran(Request $request, $id)
    {
        $bahasa = $this->getUserLanguage();

        $request->validate([
            'lampiran'     => 'required|file|max:204800',
            'lampiran_lat' => 'required|numeric',
            'lampiran_lng' => 'required|numeric',
        ]);

        $tugas = Tugas::findOrFail($id);

        if (!$tugas->tugas_lat || !$tugas->tugas_lng) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Tugas ini belum memiliki lokasi koordinat.'
                    : 'This task does not have location coordinates yet.',
            ], 422);
        }

        $distance = $this->calculateDistance(
            $tugas->tugas_lat,
            $tugas->tugas_lng,
            $request->lampiran_lat,
            $request->lampiran_lng
        );

        if ($distance > $tugas->radius_meter) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Upload gagal. Lokasi Anda berada di luar radius tugas (' . round($distance, 2) . ' m).'
                    : 'Upload failed. You are outside the task radius (' . round($distance, 2) . ' m).',
            ], 403);
        }

        // === PROSES UPLOAD ===
        if ($request->hasFile('lampiran')) {
            if ($tugas->lampiran) {
                $oldPath = str_replace('/storage/', '', $tugas->lampiran);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('lampiran');
            $ext = strtolower($file->getClientOriginalExtension());
            $folder = match ($ext) {
                'mp4', 'mov', 'avi', '3gp' => 'tugas/videos',
                'jpg', 'jpeg', 'png'       => 'tugas/images',
                default                    => 'tugas/files',
            };

            $path = $file->store($folder, 'public');
            $tugas->lampiran = Storage::url($path);

            // === CATAT WAKTU UPLOAD & HITUNG KETERLAMBATAN ===
            $now = now();
            $tugas->waktu_upload = $now;
            $tugas->terlambat = $now->gt($tugas->batas_penugasan);
            $tugas->menit_terlambat = $tugas->terlambat
                ? $tugas->batas_penugasan->diffInMinutes($now)
                : 0;
            $tugas->status = "Menunggu Admin";
            $tugas->save();
        }

        NotificationHelper::sendToFitur(
            'lihat_semua_tugas',
            $bahasa === 'indonesia' ? 'Lampiran Baru Dikirim' : 'New Attachment Submitted',
            ($bahasa === 'indonesia'
                ? 'User ' . $tugas->user->nama . ' mengunggah hasil tugas "'
                : 'User ' . $tugas->user->nama . ' uploaded task "') . $tugas->nama_tugas . '".',
            'tugas'
        );

        NotificationHelper::sendLampiranDikirim($tugas->user, $tugas);

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Lampiran berhasil diupload!'
                : 'Attachment uploaded successfully!',
            'data' => $tugas,
            'file_url' => $tugas->lampiran,
            'terlambat' => $tugas->terlambat,
            'menit_terlambat' => $tugas->menit_terlambat,
            'waktu_upload' => $tugas->waktu_upload,
        ]);
    }

    // ====== HITUNG JARAK ======
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
