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

        $tugas = in_array($user->peran_id, [1, 2])
            ? Tugas::with('user')->latest()->get()
            : Tugas::with('user')->where('user_id', $user->id)->latest()->get();

        $tugas->transform(function ($item) {
            if ($item->lampiran) {
                $item->lampiran = asset('storage/' . $item->lampiran);
            } else {
                $item->lampiran = null;
            }
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

            // Simpan path ke database (misal di kolom lampiran)
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
