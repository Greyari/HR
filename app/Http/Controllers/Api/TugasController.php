<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Upload\UploadApi;
class TugasController extends Controller
{
    // List semua tugas
    public function index()
    {
        $user = Auth::user();

        $fiturUser = $user->peran->fitur->pluck('nama_fitur');

        if ($fiturUser->contains('lihat_semua_tugas')) {
            // bisa lihat semua tugas
            $tugas = Tugas::with('user')->latest()->get();
        } elseif ($fiturUser->contains('lihat_tugas_sendiri')) {
            // hanya bisa lihat miliknya
            $tugas = Tugas::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        } else {
            return response()->json([
                'message' => 'Anda tidak punya akses untuk melihat tugas',
            ], 403);
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

    // Simpan tugas baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'           => 'required|exists:users,id',
            'nama_tugas'        => 'required|string|max:255',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
            'instruksi_tugas'   => 'nullable|string',
            'tugas_lat'         => 'required|numeric',
            'tugas_lng'         => 'required|numeric',
            'radius_meter'      => 'required|integer|min:10',
        ]);

        $validated['status'] = 'Proses';

        $tugas = Tugas::create($validated);

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data'    => $tugas->load('user')
        ], 201);
    }

    // Update tugas
    public function update(Request $request, $id)
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'user_id'           => 'sometimes|exists:users,id',
            'nama_tugas'        => 'sometimes|required|string|max:255',
            'tanggal_mulai'     => 'sometimes|required|date',
            'tanggal_selesai'   => 'sometimes|required|date|after_or_equal:tanggal_mulai',
            'instruksi_tugas'   => 'nullable|string',
            'status'            => 'in:Proses,Selesai',
            'tugas_lat'         => 'sometimes|numeric',
            'tugas_lng'         => 'sometimes|numeric',
            'radius_meter'      => 'sometimes|integer|min:10',
        ]);

        $tugas->update($validated);

        return response()->json([
            'message' => 'Tugas berhasil diperbarui',
            'data'    => $tugas->load('user')
        ]);
    }

    // Update status tugas (khusus ganti status)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Selesai,Menunggu Admin,Proses,Ditolak'
        ]);

        $tugas = Tugas::findOrFail($id);
        $tugas->status = $request->status;
        $tugas->save();

        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diperbarui',
            'data' => $tugas
        ]);
    }

    // Hapus tugas
    public function destroy($id)
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        // Opsional: hapus file di Cloudinary
        if ($tugas->lampiran) {
            try {
                $publicId = pathinfo(parse_url($tugas->lampiran)['path'], PATHINFO_FILENAME);
                (new AdminApi())->deleteAssets([$publicId]);
            } catch (\Exception $e) {
                // kalau gagal hapus, abaikan aja
            }
        }

        $tugas->delete();

        return response()->json(['message' => 'Tugas berhasil dihapus']);
    }

    // User upload bukti lampiran
    public function uploadLampiran(Request $request, $id)
    {
        $request->validate([
            'lampiran'     => 'required|file|max:204800',
            'lampiran_lat' => 'required|numeric',
            'lampiran_lng' => 'required|numeric',
        ]);

        $tugas = Tugas::findOrFail($id);

        // Pastikan tugas punya lokasi
        if (!$tugas->tugas_lat || !$tugas->tugas_lng) {
            return response()->json([
                'message' => 'Tugas ini belum memiliki lokasi koordinat.'
            ], 422);
        }

        // Hitung jarak
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

        // Simpan koordinat upload
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
                    'folder' => $folder,
                ]
            );

            $uploadedUrl = $result['secure_url'];
            $tugas->lampiran = $uploadedUrl;

            // === CEK TIMELINE ===
            $today = now()->toDateString();
            if ($today < $tugas->tanggal_mulai || $today > $tugas->tanggal_selesai) {
                $tugas->terlambat = true;   // upload di luar timeline
            } else {
                $tugas->terlambat = false;  // upload sesuai timeline
            }

            // Update status biasa
            $tugas->status = "Menunggu Admin";
            $tugas->save();
        }

        return response()->json([
            'message'   => 'Lampiran berhasil diupload!',
            'data'      => $tugas,
            'file_url'  => $tugas->lampiran,
            'terlambat' => $tugas->terlambat,
        ]);
    }

    // helpon hitung radius
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance; // meter
    }
}
