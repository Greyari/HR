<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use Illuminate\Http\Request;

class DepartemenController extends Controller
{
    // List semua departemen
    public function index()
    {
        $departemen = Departemen::all();

        return response()->json([
            'message' => 'Data departemen berhasil diambil',
            'data' => $departemen
        ]);
    }

    // Simpan departemen baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_departemen' => 'required|string|max:255',
        ]);

        $departemen = Departemen::create([
            'nama_departemen' => $request->nama_departemen,
        ]);

        return response()->json([
            'message' => 'Departemen berhasil dibuat',
            'data' => $departemen
        ]);
    }

    // Update departemen
    public function update(Request $request, $id)
    {
        $departemen = Departemen::find($id);

        if (!$departemen) {
            return response()->json(['message' => 'Departemen tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_departemen' => 'required|string|max:255',
        ]);

        $departemen->nama_departemen = $request->nama_departemen;
        $departemen->save();

        return response()->json([
            'message' => 'Departemen berhasil diperbarui',
            'data' => $departemen
        ]);
    }

    // Hapus departemen
    public function destroy($id)
    {
        $departemen = Departemen::find($id);

        if (!$departemen) {
            return response()->json(['message' => 'Departemen tidak ditemukan'], 404);
        }

        $departemen->delete();

        return response()->json([
            'message' => 'Departemen berhasil dihapus',
        ]);
    }
}
