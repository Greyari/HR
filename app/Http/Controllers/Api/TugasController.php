<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            $item->lampiran = $item->lampiran
                ? asset('storage/' . $item->lampiran)
                : null;
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
            'jam_mulai'         => 'required|date_format:H:i:s',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi'            => 'nullable|string',
            'instruksi_tugas'   => 'nullable|string',
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
            'jam_mulai'         => 'sometimes|required|date_format:H:i:s',
            'tanggal_mulai'     => 'sometimes|required|date',
            'tanggal_selesai'   => 'sometimes|required|date|after_or_equal:tanggal_mulai',
            'lokasi'            => 'nullable|string',
            'instruksi_tugas'   => 'nullable|string',
            'status'            => 'in:Proses,Selesai',
            'bukti_video'       => 'nullable|string',
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

        // Hapus video jika ada
        if ($tugas->bukti_video && Storage::disk('public')->exists($tugas->bukti_video)) {
            Storage::disk('public')->delete($tugas->bukti_video);
        }

        $tugas->delete();

        return response()->json(['message' => 'Tugas berhasil dihapus']);
    }

    // User upload bukti lampiran
    public function uploadLampiran(Request $request, $id)
    {
        $request->validate([
            'lampiran' => 'required|file|max:204800',
        ]);

        $tugas = Tugas::findOrFail($id);

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');

            // Bisa simpan di folder sesuai tipe
            $extension = $file->getClientOriginalExtension();
            $folder = match($extension) {
                'mp4', 'mov', 'avi', '3gp' => 'videos',
                'jpg', 'jpeg', 'png' => 'images',
                default => 'files',
            };

            $path = $file->store($folder, 'public');

            // Simpan path ke database
            $tugas->lampiran = $path;
            $tugas->status = "Menunggu Admin";
            $tugas->save();
        }

        return response()->json([
            'message' => 'Lampiran berhasil diupload!',
            'data' => $tugas,
            'file_url' => asset('storage/' . $tugas->lampiran),
        ]);
    }

}
