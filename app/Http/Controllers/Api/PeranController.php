<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use Illuminate\Http\Request;

class PeranController extends Controller
{
    /**
     * List semua peran dengan fitur
     */
    public function index()
    {
        $perans = Peran::with('fitur')->get();

        return response()->json([
            'message' => 'Data peran berhasil diambil',
            'data' => $perans
        ]);
    }

    /**
     * Simpan peran baru beserta fitur
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_peran' => 'required|string|max:255|unique:peran,nama_peran',
            'fitur_ids' => 'nullable|array',
            'fitur_ids.*' => 'exists:fitur,id'
        ]);

        $peran = Peran::create([
            'nama_peran' => $request->nama_peran,
        ]);

        // Assign fitur jika ada
        if ($request->has('fitur_ids')) {
            $peran->fitur()->sync($request->fitur_ids);
        }

        return response()->json([
            'message' => 'Peran berhasil dibuat',
            'data' => $peran->load('fitur')
        ]);
    }

    /**
     * Update peran dan fiturnya
     */
    public function update(Request $request, $id)
    {
        $peran = Peran::find($id);

        if (!$peran) {
            return response()->json(['message' => 'Peran tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_peran' => 'required|string|max:255|unique:peran,nama_peran,' . $id,
            'fitur_ids' => 'nullable|array',
            'fitur_ids.*' => 'exists:fitur,id'
        ]);

        $peran->nama_peran = $request->nama_peran;
        $peran->save();

        // Update fitur
        if ($request->has('fitur_ids')) {
            $peran->fitur()->sync($request->fitur_ids);
        }

        return response()->json([
            'message' => 'Peran berhasil diperbarui',
            'data' => $peran->load('fitur')
        ]);
    }

    /**
     * Hapus peran jika tidak ada user
     */
    public function destroy($id)
    {
        $peran = Peran::find($id);

        if (!$peran) {
            return response()->json(['message' => 'Peran tidak ditemukan'], 404);
        }

        if ($peran->users()->count() > 0) {
            return response()->json([
                'message' => 'Peran masih digunakan oleh user, tidak bisa dihapus'
            ], 400);
        }

        $peran->delete();

        return response()->json(['message' => 'Peran berhasil dihapus']);
    }
}
