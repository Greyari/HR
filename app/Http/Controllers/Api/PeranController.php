<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use Illuminate\Http\Request;

class PeranController extends Controller
{
    // List semua peran
    public function index()
    {
        $perans = Peran::all();

        return response()->json([
            'message' => 'Data peran berhasil diambil',
            'data' => $perans
        ]);
    }

    // Simpan peran baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_peran' => 'required|string|max:255',
        ]);

        $peran = Peran::create([
            'nama_peran' => $request->nama_peran,
        ]);

        return response()->json([
            'message' => 'Peran berhasil dibuat',
            'data' => $peran
        ]);
    }

    // Update peran
    public function update(Request $request, $id)
    {
        $peran = Peran::find($id);

        if (!$peran) {
            return response()->json(['message' => 'Peran tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_peran' => 'required|string|max:255',
        ]);

        $peran->nama_peran = $request->nama_peran;
        $peran->save();

        return response()->json([
            'message' => 'Peran berhasil diperbarui',
            'data' => $peran
        ]);
    }

    // Hapus peran
    public function destroy($id)
    {
        $peran = Peran::find($id);

        if (!$peran) {
            return response()->json(['message' => 'Peran tidak ditemukan'], 404);
        }

        $peran->delete();

        return response()->json(['message' => 'Peran berhasil dihapus']);
    }
}
