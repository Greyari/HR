<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PotonganGaji;
use Illuminate\Http\Request;

class PotonganGajiController extends Controller
{
    // List semua potongan
    public function index()
    {
        $potongangaji = PotonganGaji::all();

        return response()->json([
            'message' => 'Data potongan gaji berhasil diambil',
            'data' => $potongangaji
        ]);
    }

    // Tambah potongan baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_potongan' => 'required|string|max:255',
            'persen' => 'required|numeric|min:0|max:100', 
        ]);

        $potongan = PotonganGaji::create([
            'nama_potongan' => $request->nama_potongan,
            'persen' => $request->persen,
        ]);

        return response()->json([
            'message' => 'Potongan berhasil ditambahkan',
            'data' => $potongan
        ], 201);
    }

    // Update potongan
    public function update(Request $request, $id)
    {
        $potongan = PotonganGaji::findOrFail($id);

        $request->validate([
            'nama_potongan' => 'sometimes|required|string|max:255',
            'persen' => 'sometimes|required|numeric|min:0|max:100',
        ]);

        $potongan->update($request->only(['nama_potongan', 'persen']));

        return response()->json([
            'message' => 'Potongan berhasil diperbarui',
            'data' => $potongan
        ]);
    }

    // Hapus potongan
    public function destroy($id)
    {
        $potongan = PotonganGaji::findOrFail($id);
        $potongan->delete();

        return response()->json([
            'message' => 'Potongan berhasil dihapus'
        ]);
    }
}
