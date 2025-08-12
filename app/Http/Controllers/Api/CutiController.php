<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CutiController extends Controller
{
    // Menampilkan daftar cuti
    public function index()
    {
        $user = Auth::user();

        if ($user->peran_id === 1) {
            $cuti = Cuti::with(['user.peran', 'user.jabatan', 'user.departemen'])->latest()->get();
        } else {
            $cuti = Cuti::with(['user.peran', 'user.jabatan', 'user.departemen'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return response()->json([
            'message' => 'Data cuti berhasil diambil',
            'data' => $cuti
        ]);
    }

    // Menyimpan pengajuan cuti
    public function store(Request $request) {
        $request->validate([
            'tipe_cuti' => 'required|string|max:50',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'nullable|string|max:255',
        ]);

        $cuti = Cuti::create([
            'user_id' => Auth::id(),
            'tipe_cuti' => $request->tipe_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
        ]);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim',
            'data' => $cuti
        ], 201);
    }

    // Approve cuti
    public function approve($id)
    {
        $cuti = Cuti::find($id);

        if (!$cuti) {
            return response()->json(['message' => 'Data cuti tidak ditemukan'], 404);
        }

        $cuti->status = 'Disetujui';
        $cuti->save();

        return response()->json([
            'message' => 'Cuti berhasil disetujui',
            'data' => $cuti
        ]);
    }

    // Decline cuti
    public function decline($id)
    {
        $cuti = Cuti::find($id);

        if (!$cuti) {
            return response()->json(['message' => 'Cuti not found'], 404);
        }

        $cuti->status = 'Ditolak';
        $cuti->save();

        return response()->json([
            'message' => 'Cuti berhasil ditolak',
            'data' => $cuti
        ]);
    }
}
