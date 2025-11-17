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

        $messages = [
            'indonesia' => [
                'user_id.required'             => 'Pegawai wajib dipilih.',
                'user_id.exists'               => 'Pegawai tidak ditemukan.',
                'nama_tugas.required'          => 'Nama tugas wajib diisi.',
                'nama_tugas.max'               => 'Nama tugas tidak boleh lebih dari 255 karakter.',
                'tanggal_penugasan.required'   => 'Tanggal penugasan wajib diisi.',
                'tanggal_penugasan.date'       => 'Tanggal penugasan harus berupa tanggal yang valid.',
                'batas_penugasan.required'     => 'Batas penugasan wajib diisi.',
                'batas_penugasan.date'         => 'Batas penugasan harus berupa tanggal yang valid.',
                'batas_penugasan.after_or_equal' => 'Batas penugasan tidak boleh sebelum tanggal penugasan.',
                'tugas_lat.required'           => 'Koordinat latitude tugas wajib diisi.',
                'tugas_lat.numeric'            => 'Koordinat latitude tugas harus berupa angka.',
                'tugas_lng.required'           => 'Koordinat longitude tugas wajib diisi.',
                'tugas_lng.numeric'            => 'Koordinat longitude tugas harus berupa angka.',
                'radius_meter.required'        => 'Radius meter wajib diisi.',
                'radius_meter.integer'         => 'Radius meter harus berupa angka bulat.',
                'radius_meter.min'             => 'Radius meter minimal 10 meter.',
            ],
            'inggris' => [
                'user_id.required'             => 'Employee is required.',
                'user_id.exists'               => 'Selected employee not found.',
                'nama_tugas.required'          => 'Task name is required.',
                'nama_tugas.max'               => 'Task name must not exceed 255 characters.',
                'tanggal_penugasan.required'   => 'Assignment date is required.',
                'tanggal_penugasan.date'       => 'Assignment date must be a valid date.',
                'batas_penugasan.required'     => 'Deadline is required.',
                'batas_penugasan.date'         => 'Deadline must be a valid date.',
                'batas_penugasan.after_or_equal' => 'Deadline cannot be before the assignment date.',
                'tugas_lat.required'           => 'Task latitude coordinate is required.',
                'tugas_lat.numeric'            => 'Task latitude coordinate must be numeric.',
                'tugas_lng.required'           => 'Task longitude coordinate is required.',
                'tugas_lng.numeric'            => 'Task longitude coordinate must be numeric.',
                'radius_meter.required'        => 'Radius meter is required.',
                'radius_meter.integer'         => 'Radius meter must be an integer.',
                'radius_meter.min'             => 'Radius meter must be at least 10 meters.',
            ],
        ];

        $validated = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'nama_tugas'          => 'required|string|max:255',
            'tanggal_penugasan'   => 'required|date',
            'batas_penugasan'     => 'required|date|after_or_equal:tanggal_penugasan',
            'instruksi_tugas'     => 'nullable|string',
            'tugas_lat'           => 'required|numeric',
            'tugas_lng'           => 'required|numeric',
            'radius_meter'        => 'required|integer|min:10',
        ], $messages[$bahasa]);

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

        $messages = [
            'indonesia' => [
                'nama_tugas.required'        => 'Nama tugas wajib diisi.',
                'tanggal_penugasan.required' => 'Tanggal penugasan wajib diisi.',
                'batas_penugasan.required'   => 'Batas penugasan wajib diisi.',
                'batas_penugasan.after_or_equal' => 'Batas penugasan tidak boleh sebelum tanggal penugasan.',
                'radius_meter.min'           => 'Radius meter minimal 10 meter.',
            ],
            'inggris' => [
                'nama_tugas.required'        => 'Task name is required.',
                'tanggal_penugasan.required' => 'Assignment date is required.',
                'batas_penugasan.required'   => 'Deadline is required.',
                'batas_penugasan.after_or_equal' => 'Deadline cannot be before assignment date.',
                'radius_meter.min'           => 'Radius meter must be at least 10 meters.',
            ],
        ];

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
        ], $messages[$bahasa]);

        $userLama = $tugas->user;
        $isPICChanged = isset($validated['user_id']) && $userLama->id !== $validated['user_id'];

        $tugas->update($validated);
        $tugas->refresh()->load('user');

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

        // 🔹 Kirim notifikasi ke user berdasarkan perubahan status
        if ($tugas->user && $tugas->user->device_token) {
            // Jika status diubah menjadi Selesai
            if ($request->status === 'Selesai' && $statusLama !== 'Selesai') {
                NotificationHelper::sendTugasSelesai($tugas->user, $tugas);
            }

            // Jika status diubah menjadi Proses
            elseif ($request->status === 'Proses' && $statusLama !== 'Proses') {
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

        $messages = [
            'indonesia' => [
                'lampiran.required'     => 'File lampiran wajib diunggah.',
                'lampiran.file'         => 'Lampiran harus berupa file.',
                'lampiran.max'          => 'Ukuran lampiran tidak boleh lebih dari 200MB.',
                'lampiran_lat.required' => 'Koordinat latitude wajib diisi.',
                'lampiran_lat.numeric'  => 'Koordinat latitude harus berupa angka.',
                'lampiran_lng.required' => 'Koordinat longitude wajib diisi.',
                'lampiran_lng.numeric'  => 'Koordinat longitude harus berupa angka.',
            ],
            'inggris' => [
                'lampiran.required'     => 'Attachment file is required.',
                'lampiran.file'         => 'Attachment must be a valid file.',
                'lampiran.max'          => 'Attachment size must not exceed 200MB.',
                'lampiran_lat.required' => 'Latitude coordinate is required.',
                'lampiran_lat.numeric'  => 'Latitude coordinate must be numeric.',
                'lampiran_lng.required' => 'Longitude coordinate is required.',
                'lampiran_lng.numeric'  => 'Longitude coordinate must be numeric.',
            ],
        ];

        $request->validate([
            'lampiran'     => 'required|file|max:204800',
            'lampiran_lat' => 'required|numeric',
            'lampiran_lng' => 'required|numeric',
        ], $messages[$bahasa]);

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

        $tugas->lampiran_lat = $request->lampiran_lat;
        $tugas->lampiran_lng = $request->lampiran_lng;

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

        // Kirim notifikasi ke user (hapus progres bar + tunggal)
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
