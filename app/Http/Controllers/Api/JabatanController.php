<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JabatanController extends Controller
{
    /**
     * Ambil preferensi bahasa dari pengaturan user.
     */
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    // ====== LIST SEMUA JABATAN ======
    public function index()
    {
        $jabatan = Jabatan::all();

        return response()->json([
            'message' => 'Data jabatan berhasil diambil',
            'data' => $jabatan
        ]);
    }

    // ====== SIMPAN JABATAN BARU ======
    public function store(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        // Pesan validasi per bahasa
        $messages = [
            'indonesia' => [
                'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
                'nama_jabatan.unique'   => 'Nama jabatan sudah digunakan.',
                'nama_jabatan.max'      => 'Nama jabatan tidak boleh lebih dari 255 karakter.',
            ],
            'inggris' => [
                'nama_jabatan.required' => 'Job title is required.',
                'nama_jabatan.unique'   => 'Job title has already been taken.',
                'nama_jabatan.max'      => 'Job title must not exceed 255 characters.',
            ],
        ];

        try {
            $validated = $request->validate([
                'nama_jabatan' => 'required|string|max:255|unique:jabatan,nama_jabatan',
            ], $messages[$bahasa]);

            $jabatan = Jabatan::create($validated);

            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Jabatan berhasil dibuat'
                    : 'Job title created successfully',
                'data' => $jabatan
            ]);
        } catch (ValidationException $e) {
            $errorMessages = $e->errors();
            $firstError = collect($errorMessages)->flatten()->first();

            return response()->json([
                'message' => $firstError,
                'errors' => $errorMessages,
            ], 422);
        }
    }

    // ====== UPDATE JABATAN ======
    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::find($id);
        if (!$jabatan) {
            return response()->json(['message' => 'Jabatan tidak ditemukan'], 404);
        }

        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $messages = [
            'indonesia' => [
                'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
                'nama_jabatan.unique'   => 'Nama jabatan sudah digunakan.',
                'nama_jabatan.max'      => 'Nama jabatan tidak boleh lebih dari 255 karakter.',
            ],
            'inggris' => [
                'nama_jabatan.required' => 'Job title is required.',
                'nama_jabatan.unique'   => 'Job title has already been taken.',
                'nama_jabatan.max'      => 'Job title must not exceed 255 characters.',
            ],
        ];

        try {
            $validated = $request->validate([
                'nama_jabatan' => 'required|string|max:255|unique:jabatan,nama_jabatan,' . $id,
            ], $messages[$bahasa]);

            $jabatan->update($validated);

            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Jabatan berhasil diperbarui'
                    : 'Job title updated successfully',
                'data' => $jabatan
            ]);
        } catch (ValidationException $e) {
            $errorMessages = $e->errors();
            $firstError = collect($errorMessages)->flatten()->first();

            return response()->json([
                'message' => $firstError,
                'errors' => $errorMessages,
            ], 422);
        }
    }

    // ====== HAPUS JABATAN ======
    public function destroy(Request $request, $id)
    {
        $jabatan = Jabatan::find($id);

        // Ambil bahasa dari pengaturan user
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        if (!$jabatan) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Jabatan tidak ditemukan'
                    : 'Job title not found',
            ], 404);
        }

        $jabatan->delete();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Jabatan berhasil dihapus'
                : 'Job title deleted successfully',
        ]);
    }
}
