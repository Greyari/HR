<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasController extends Controller
{
    // List semua tugas
    public function index()
    {
        $user = Auth::user();

        if ($user->peran_id === 1) {
            $tugas = Tugas::with(['users.peran', 'users.jabatan', 'users.departemen'])
                ->latest()
                ->get();
        } else {
            $tugas = Tugas::with(['users.peran', 'users.jabatan', 'users.departemen'])
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->orWhere('departemen_id', $user->departemen_id)
                ->get();
        }

        return response()->json([
            'message' => 'Data tugas berhasil diambil',
            'data' => $tugas
        ]);
    }

    // Simpan tugas baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_tugas' => 'required|string|max:255',
            'jam_mulai' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|string',
            'instruksi_tugas' => 'nullable|string',
            'departemen_id' => 'nullable|exists:departemen,id',
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:users,id',
        ]);

        $tugas = Tugas::create([
            'nama_tugas' => $request->nama_tugas,
            'jam_mulai' => $request->jam_mulai,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'departemen_id' => $request->departemen_id,
            'lokasi' => $request->lokasi,
            'instruksi_tugas' => $request->instruksi_tugas,
        ]);

        $userIds = [];

        if ($request->filled('user_id')) {
            $userIds = $request->user_id;
        } elseif ($request->filled('departemen_id')) {
            $userIds = User::where('departemen_id', $request->departemen_id)->pluck('id')->toArray();
        }

        if (!empty($userIds)) {
            foreach ($userIds as $uid) {
                $tugas->users()->attach($uid, [
                    'status' => 'Proses',
                    'laporan_user' => null
                ]);
            }
        }

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data' => $tugas->load('users')
        ]);
    }

    //  Update tugas
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
            'lokasi' => 'nullable|string',
            'instruksi_tugas' => 'nullable|string',
            'departemen_id' => 'nullable|exists:departemen,id',
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:users,id',
        ]);

        $tugas->update($request->only([
            'nama_tugas', 'jam_mulai', 'tanggal_mulai', 'tanggal_selesai',
            'lokasi', 'instruksi_tugas', 'departemen_id'
        ]));

        if ($request->assignment_mode === 'Per User' && !empty($request->user_id)) {
            $tugas->departemen_id = null;
            $tugas->save();
            $tugas->users()->sync($request->user_id);
        } elseif ($request->assignment_mode === 'Per Departemen' && $request->departemen_id) {
            $tugas->departemen_id = $request->departemen_id;
            $tugas->save();

            $userIds = User::where('departemen_id', $request->departemen_id)->pluck('id')->toArray();
            $tugas->users()->sync($userIds);
        }

        return response()->json([
            'message' => 'Tugas berhasil diperbarui',
            'data' => $tugas->load('users')
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
