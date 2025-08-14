<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    // List semua jabatan
    public function index()
    {
        $jabatan = Jabatan::all();

        return response()->json([
            'message' => 'Data jabatan berhasil diambil',
            'data' => $jabatan
        ]);
    }

    // Simpan jabatan baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:255',
        ]);

        $jabatan = Jabatan::create([
            'nama_jabatan' => $request->nama_jabatan,
        ]);

        return response()->json([
            'message' => 'Jabatan berhasil dibuat',
            'data' => $jabatan
        ]);
    }

    // Update jabatan
    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::find($id);

        if (!$jabatan) {
            return response()->json(['message' => 'Jabatan tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_jabatan' => 'required|string|max:255',
        ]);

        $jabatan->nama_jabatan = $request->nama_jabatan;
        $jabatan->save();

        return response()->json([
            'message' => 'Jabatan berhasil diperbarui',
            'data' => $jabatan
        ]);
    }

    // Hapus jabatan
    public function destroy($id)
    {
        $jabatan = Jabatan::find($id);

        if (!$jabatan) {
            return response()->json(['message' => 'Jabatan tidak ditemukan'], 404);
        }

        $jabatan->delete();

        return response()->json([
            'message' => 'Jabatan berhasil dihapus'
        ]);
    }
}
