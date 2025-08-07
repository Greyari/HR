<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LemburController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Jika dia admin, tampilkan semua
        if ($user->nama_peran === 'Super admin') {
            $lembur = Lembur::with('karyawan')->latest()->get();
        } else {
            // Kalau bukan admin, tampilkan hanya lembur miliknya
            $lembur = Lembur::where('user_id', $user->id)->latest()->get();
        }

        return response()->json([
            'message' => 'Data lembur berhasil diambil',
            'data' => $lembur
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $lembur = Lembur::create([
            'user_id' => Auth::id(),
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan lembur berhasil dikirim',
            'data' => $lembur
        ], 201);
    }

}
