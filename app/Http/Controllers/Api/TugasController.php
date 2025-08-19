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

        $tugas = ($user->peran_id === 1)
            ? Tugas::with('user')->latest()->get()
            : Tugas::with('user')->where('user_id', $user->id)->latest()->get();

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

    // User upload bukti video
    public function uploadBuktiVideo(Request $request, $id)
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $request->validate([
            'bukti_video' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg|max:102400',
        ]);

        // Hapus video lama jika ada
        if ($tugas->bukti_video && Storage::disk('public')->exists($tugas->bukti_video)) {
            Storage::disk('public')->delete($tugas->bukti_video);
        }

        // Simpan video baru
        $path = $request->file('bukti_video')->store('videos', 'public');

        $tugas->update([
            'bukti_video' => $path,
            'status'      => 'Selesai',
        ]);

        return response()->json([
            'message' => 'Bukti video berhasil diupload',
            'data'    => $tugas->load('user')
        ]);
    }
}
