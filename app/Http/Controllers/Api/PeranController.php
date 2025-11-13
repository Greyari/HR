<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PeranController extends Controller
{
    /**
     * Ambil preferensi bahasa dari pengaturan user.
     */
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    /**
     * List semua peran dengan fitur
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $perans = Peran::with('fitur')->get();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Data peran berhasil diambil'
                : 'Role data retrieved successfully',
            'data' => $perans
        ]);
    }

    /**
     * Simpan peran baru beserta fitur
     */
    public function store(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $request->validate([
            'nama_peran' => 'required|string|max:255|unique:peran,nama_peran',
            'fitur_ids' => 'nullable|array',
            'fitur_ids.*' => 'exists:fitur,id'
        ]);

        $peran = Peran::create([
            'nama_peran' => $request->nama_peran,
        ]);

        // Assign fitur jika ada
        if ($request->has('fitur_ids')) {
            $peran->fitur()->sync($request->fitur_ids);
        }

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Peran berhasil dibuat'
                : 'Role created successfully',
            'data' => $peran->load('fitur')
        ]);
    }

    /**
     * Update peran dan fiturnya
     */
    public function update(Request $request, $id)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $peran = Peran::find($id);

        if (!$peran) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Peran tidak ditemukan'
                    : 'Role not found',
            ], 404);
        }

        $request->validate([
            'nama_peran' => 'required|string|max:255|unique:peran,nama_peran,' . $id,
            'fitur_ids' => 'nullable|array',
            'fitur_ids.*' => 'exists:fitur,id'
        ]);

        $peran->nama_peran = $request->nama_peran;
        $peran->save();

        // Update fitur
        if ($request->has('fitur_ids')) {
            $peran->fitur()->sync($request->fitur_ids);
        }

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Peran berhasil diperbarui'
                : 'Role updated successfully',
            'data' => $peran->load('fitur')
        ]);
    }

    /**
     * Hapus peran jika tidak ada user
     */
    public function destroy(Request $request, $id)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $peran = Peran::find($id);

        if (!$peran) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Peran tidak ditemukan'
                    : 'Role not found',
            ], 404);
        }

        if ($peran->users()->count() > 0) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Peran masih digunakan oleh user, tidak bisa dihapus'
                    : 'This role is still assigned to users and cannot be deleted',
            ], 400);
        }

        $peran->delete();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Peran berhasil dihapus'
                : 'Role deleted successfully',
        ]);
    }
}
