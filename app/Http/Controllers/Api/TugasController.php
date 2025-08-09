<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasController extends Controller
{
    // List semua tugas
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            // Super admin dapat lihat semua tugas
            $tugas = Tugas::with(['user', 'department'])->get();
        } else {
            // User biasa hanya lihat tugas yang ditugaskan ke dia (user_id)
            // atau tugas yang ditugaskan ke departemennya (departemen_id)
            $tugas = Tugas::with(['user', 'department'])
                ->where('user_id', $user->id)
                ->orWhere('departemen_id', $user->departemen_id)
                ->get();
        }

        return response()->json($tugas);
    }


    // Simpan tugas baru
    public function store(Request $request)
    {
        // Validasi minimal
        $request->validate([
            'nama_tugas' => 'required|string|max:255',
            'jam_mulai' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'user_id' => 'nullable|exists:users,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'lokasi' => 'nullable|string',
            'Note' => 'nullable|string',
        ]);

        $tugas = Tugas::create($request->all());

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data' => $tugas
        ], 201);
    }

    // Update tugas
    public function update(Request $request, $id)
    {
        $tugas = Tugas::find($id);
        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_tugas' => 'sometimes|required|string|max:255',
            'jam_mulai' => 'sometimes|required',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_selesai' => 'sometimes|required|date|after_or_equal:tanggal_mulai',
            'user_id' => 'nullable|exists:users,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'lokasi' => 'nullable|string',
            'Note' => 'nullable|string',
        ]);

        $tugas->update($request->all());

        return response()->json([
            'message' => 'Tugas berhasil diperbarui',
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

        $tugas->delete();

        return response()->json(['message' => 'Tugas berhasil dihapus']);
    }
}
