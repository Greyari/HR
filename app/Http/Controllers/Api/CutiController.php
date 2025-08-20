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
            $cuti = Cuti::with(['user.peran'])->latest()->get();
        } elseif ($user->peran_id === 2) {
            $cuti = Cuti::with(['user.peran'])->latest()->get();
        } else {
            $cuti = Cuti::with(['user.peran'])
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
    public function store(Request $request)
    {
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
            'status' => 'Pending'
        ]);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim',
            'data' => $cuti
        ], 201);
    }

    // Update cuti (hanya pemilik cuti)
    public function update(Request $request, $id)
    {
        $cuti = Cuti::find($id);

        if (!$cuti) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }

        // Pastikan user hanya bisa update cutinya sendiri
        if ($cuti->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk mengedit cuti ini'], 403);
        }

        $request->validate([
            'tipe_cuti' => 'required|string|max:50',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'nullable|string|max:255',
        ]);

        $cuti->update([
            'tipe_cuti' => $request->tipe_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
        ]);

        return response()->json([
            'message' => 'Cuti berhasil diperbarui',
            'data' => $cuti
        ]);
    }

    // Hapus cuti (hanya pemilik cuti)
    public function destroy($id)
    {
        $cuti = Cuti::find($id);

        if (!$cuti) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }

        if ($cuti->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk menghapus cuti ini'], 403);
        }

        $cuti->delete();

        return response()->json([
            'message' => 'Cuti berhasil dihapus'
        ]);
    }

    // Approve
    public function approve($id)
    {
        $user = Auth::user();
        $cuti = Cuti::find($id);

        if (!$cuti) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }

        // Jika Admin Office (peran_id = 2)
        if ($user->peran_id === 2) {
            if (!in_array($cuti->approval_step, [0, 3])) {
                return response()->json(['message' => 'Cuti sudah diproses oleh Admin Office'], 400);
            }
            $cuti->approval_step = 1;
            $cuti->status = 'Proses';
            $cuti->save();

            return response()->json([
                'message' => 'Cuti disetujui Admin Office, menunggu Super Admin',
                'step'    => $cuti->approval_step,
                'status'  => $cuti->status,
                'data'    => $cuti
            ]);
        }

        // Jika Super Admin (peran_id = 1)
        if ($user->peran_id === 1) {
            if (!in_array($cuti->approval_step, [1, 3])) {
                return response()->json(['message' => 'Cuti harus disetujui Admin Office dulu'], 400);
            }
            $cuti->approval_step = 2;
            $cuti->status = 'Disetujui';
            $cuti->save();

            return response()->json([
                'message' => 'Cuti disetujui final oleh Super Admin',
                'step'    => $cuti->approval_step,
                'status'  => $cuti->status,
                'data'    => $cuti
            ]);
        }

        return response()->json(['message' => 'Tidak memiliki izin'], 403);
    }

    // Decline
    public function decline($id)
    {
        $user = Auth::user();
        $cuti = Cuti::find($id);

        if (!$cuti) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }

        if (!in_array($user->peran_id, [1, 2])) {
            return response()->json(['message' => 'Tidak memiliki izin'], 403);
        }

        if ($cuti->approval_step < 2) {
            $cuti->approval_step = 3;
            $cuti->status = 'Ditolak';
            $cuti->save();

            return response()->json([
                'message' => 'Cuti ditolak',
                'step'    => $cuti->approval_step,
                'status'  => $cuti->status,
                'data'    => $cuti
            ]);
        }

        return response()->json(['message' => 'Cuti sudah final, tidak bisa ditolak'], 400);
    }
}
