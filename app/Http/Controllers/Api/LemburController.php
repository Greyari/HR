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

        if ($user->nama_peran === 'Super admin') {
            $lembur = Lembur::with(['user.peran', 'user.jabatan', 'user.departemen', 'user.statusPernikahan'])->latest()->get();
        }
        else {
            $lembur = Lembur::with(['user.peran', 'user.jabatan', 'user.departemen', 'user.statusPernikahan'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
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
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $lembur = Lembur::create([
            'user_id' => Auth::id(),
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'deskripsi' => $request->deskripsi,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan lembur berhasil dikirim',
            'data' => $lembur
        ], 201);
    }

    public function approve($id)
    {
        $lembur = Lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Data lembur tidak ditemukan'], 404);
        }

        $lembur->status = 'Disetujui';
        $lembur->save();

        return response()->json([
            'message' => 'Lembur berhasil disetujui',
            'data' => $lembur
        ]);
    }

    public function decline($id)
    {
        $lembur = Lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Data lembur tidak ditemukan'], 404);
        }

        $lembur->status = 'Ditolak';
        $lembur->save();

        return response()->json([
            'message' => 'Lembur berhasil ditolak',
            'data' => $lembur
        ]);
    }

}
