<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gaji;
use App\Models\PotonganGaji;
use Illuminate\Http\Request;

class GajiController extends Controller
{
    // Ambil semua gaji (dengan potongan)
    public function index()
    {
        $gaji = Gaji::with(['user', 'potongan'])->get();

        return response()->json($gaji);
    }

    // Tambah data gaji baru
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bulan' => 'required|string',
            'tahun' => 'required|string',
            'gaji_pokok' => 'required|numeric|min:0',
            'total_lembur' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|array', // daftar potongan
            'potongan.*.id' => 'required|exists:potongan_gaji,id',
            'potongan.*.nominal' => 'required|numeric|min:0',
        ]);

        // Buat gaji
        $gaji = Gaji::create([
            'user_id' => $request->user_id,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'gaji_pokok' => $request->gaji_pokok,
            'total_lembur' => $request->total_lembur ?? 0,
            'gaji_bersih' => 0, // dihitung setelah potongan
        ]);

        // Hitung total potongan
        $totalPotongan = 0;
        if ($request->has('potongan')) {
            foreach ($request->potongan as $pot) {
                $gaji->potongan()->attach($pot['id'], ['nominal' => $pot['nominal']]);
                $totalPotongan += $pot['nominal'];
            }
        }

        // Update gaji bersih
        $gaji->gaji_bersih = ($gaji->gaji_pokok + $gaji->total_lembur) - $totalPotongan;
        $gaji->save();

        return response()->json([
            'message' => 'Gaji berhasil dibuat',
            'data' => $gaji->load('potongan')
        ], 201);
    }

    // Update data gaji
    public function update(Request $request, $id)
    {
        $gaji = Gaji::findOrFail($id);

        $request->validate([
            'gaji_pokok' => 'sometimes|required|numeric|min:0',
            'total_lembur' => 'sometimes|required|numeric|min:0',
            'potongan' => 'nullable|array',
            'potongan.*.id' => 'required|exists:potongan_gaji,id',
            'potongan.*.nominal' => 'required|numeric|min:0',
        ]);

        $gaji->update($request->only(['gaji_pokok', 'total_lembur']));

        // Reset potongan lama
        $gaji->potongan()->detach();

        // Hitung ulang potongan
        $totalPotongan = 0;
        if ($request->has('potongan')) {
            foreach ($request->potongan as $pot) {
                $gaji->potongan()->attach($pot['id'], ['nominal' => $pot['nominal']]);
                $totalPotongan += $pot['nominal'];
            }
        }

        // Update gaji bersih
        $gaji->gaji_bersih = ($gaji->gaji_pokok + $gaji->total_lembur) - $totalPotongan;
        $gaji->save();

        return response()->json([
            'message' => 'Gaji berhasil diperbarui',
            'data' => $gaji->load('potongan')
        ]);
    }

    // Hapus data gaji
    public function destroy($id)
    {
        $gaji = Gaji::findOrFail($id);
        $gaji->delete();

        return response()->json(['message' => 'Gaji berhasil dihapus']);
    }
}
