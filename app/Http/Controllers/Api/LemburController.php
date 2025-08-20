<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LemburController extends Controller
{
    // Menampilkan daftar lembur
    public function index()
    {
        $user = Auth::user();

        if ($user->peran_id === 1) {
            $lembur = Lembur::with(['user.peran'])->latest()->get();
        } elseif ($user->peran_id === 2) {
            $lembur = Lembur::with(['user.peran'])->latest()->get();
        } else {
            $lembur = Lembur::with(['user.peran'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return response()->json([
            'message' => 'Data lembur berhasil diambil',
            'data' => $lembur
        ]);
    }


    // Menyimpan pengajuan lembur
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
            'status' => 'Pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan lembur berhasil dikirim',
            'data' => $lembur
        ], 201);
    }

    // Update lembur
    public function update(Request $request, $id)
    {
        $lembur = Lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Data lembur tidak ditemukan'], 404);
        }

        if ($lembur->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk mengedit lembur ini'], 403);
        }

        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $lembur->update([
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'deskripsi' => $request->deskripsi,
        ]);


        return response()->json([
            'message' => 'Lembur berhasil diperbarui',
            'data' => $lembur
        ]);
    }

    // Hapus lembur
    public function destroy($id)
    {
        $lembur = Lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Data lembur tidak ditemukan'], 404);
        }

        if ($lembur->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk menghapus lembur ini'], 403);
        }

        $lembur->delete();

        return response()->json([
            'message' => 'Lembur berhasil dihapus'
        ]);
    }

    // Approve lembur
    public function approve($id)
    {
        $user = Auth::user();
        $lembur = Lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Lembur tidak ditemukan'], 404);
        }

        // Jika Admin Office (peran_id = 2)
        if ($user->peran_id === 2) {
            if (!in_array($lembur->approval_step, [0, 3])) {
                return response()->json(['message' => 'Lembur sudah diproses oleh Admin Office'], 400);
            }
            $lembur->approval_step = 1;
            $lembur->status = 'Proses';
            $lembur->save();

            return response()->json([
                'message' => 'Lembur disetujui Admin Office, menunggu Super Admin',
                'step'    => $lembur->approval_step,
                'status'  => $lembur->status,
                'data'    => $lembur
            ]);
        }

        // Jika Super Admin (peran_id = 1)
        if ($user->peran_id === 1) {
            if (!in_array($lembur->approval_step, [1, 3])) {
                return response()->json(['message' => 'Lembur harus disetujui Admin Office dulu'], 400);
            }
            $lembur->approval_step = 2;
            $lembur->status = 'Disetujui';
            $lembur->save();

            return response()->json([
                'message' => 'Lembur disetujui final oleh Super Admin',
                'step'    => $lembur->approval_step,
                'status'  => $lembur->status,
                'data'    => $lembur
            ]);
        }

        return response()->json(['message' => 'Tidak memiliki izin'], 403);
    }

    // Decline
    public function decline($id)
    {
        $user = Auth::user();
        $lembur = lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Lembur tidak ditemukan'], 404);
        }

        if (!in_array($user->peran_id, [1, 2])) {
            return response()->json(['message' => 'Tidak memiliki izin'], 403);
        }

        if ($lembur->approval_step < 2) {
            $lembur->approval_step = 3;
            $lembur->status = 'Ditolak';
            $lembur->save();

            return response()->json([
                'message' => 'Lembur ditolak',
                'step'    => $lembur->approval_step,
                'status'  => $lembur->status,
                'data'    => $lembur
            ]);
        }

        return response()->json(['message' => 'Lembur sudah final, tidak bisa ditolak'], 400);
    }
}
