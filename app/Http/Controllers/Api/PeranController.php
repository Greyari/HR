<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PeranController extends Controller
{
    /**
     * Ambil preferensi bahasa user
     */
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    // ====== LIST PERAN ======
    public function index(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $data = Peran::with('fitur')->get();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Data peran berhasil diambil'
                : 'Role data retrieved successfully',
            'data' => $data
        ]);
    }

    // ====== SIMPAN PERAN ======
    public function store(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        // Pesan validasi custom
        $messages = [
            'indonesia' => [
                'nama_peran.required' => 'Nama peran wajib diisi.',
                'nama_peran.unique'   => 'Nama peran sudah digunakan.',
                'nama_peran.max'      => 'Nama peran tidak boleh lebih dari 255 karakter.',
                'fitur_ids.array'     => 'Fitur harus berupa array.',
                'fitur_ids.*.exists'  => 'Fitur yang dipilih tidak valid.',
            ],
            'inggris' => [
                'nama_peran.required' => 'Role name is required.',
                'nama_peran.unique'   => 'Role name has already been taken.',
                'nama_peran.max'      => 'Role name must not exceed 255 characters.',
                'fitur_ids.array'     => 'Features must be an array.',
                'fitur_ids.*.exists'  => 'Selected feature is invalid.',
            ]
        ];

        try {
            $validated = $request->validate([
                'nama_peran' => 'required|string|max:255|unique:peran,nama_peran',
                'fitur_ids' => 'nullable|array',
                'fitur_ids.*' => 'exists:fitur,id'
            ], $messages[$bahasa]);

            $peran = Peran::create([
                'nama_peran' => $validated['nama_peran'],
            ]);

            if ($request->has('fitur_ids')) {
                $peran->fitur()->sync($request->fitur_ids);
            }

            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Peran berhasil dibuat'
                    : 'Role created successfully',
                'data' => $peran->load('fitur')
            ]);
        } catch (ValidationException $e) {
            $error = collect($e->errors())->flatten()->first();
            return response()->json([
                'message' => $error,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // ====== UPDATE PERAN ======
    public function update(Request $request, $id)
    {
        $peran = Peran::find($id);

        if (!$peran) {
            return response()->json(['message' => 'Peran tidak ditemukan'], 404);
        }

        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $messages = [
            'indonesia' => [
                'nama_peran.required' => 'Nama peran wajib diisi.',
                'nama_peran.unique'   => 'Nama peran sudah digunakan.',
                'nama_peran.max'      => 'Nama peran tidak boleh lebih dari 255 karakter.',
                'fitur_ids.array'     => 'Fitur harus berupa array.',
                'fitur_ids.*.exists'  => 'Fitur yang dipilih tidak valid.',
            ],
            'inggris' => [
                'nama_peran.required' => 'Role name is required.',
                'nama_peran.unique'   => 'Role name has already been taken.',
                'nama_peran.max'      => 'Role name must not exceed 255 characters.',
                'fitur_ids.array'     => 'Features must be an array.',
                'fitur_ids.*.exists'  => 'Selected feature is invalid.',
            ]
        ];

        try {
            $validated = $request->validate([
                'nama_peran' => 'required|string|max:255|unique:peran,nama_peran,' . $id,
                'fitur_ids' => 'nullable|array',
                'fitur_ids.*' => 'exists:fitur,id'
            ], $messages[$bahasa]);

            $peran->update([
                'nama_peran' => $validated['nama_peran'],
            ]);

            if ($request->has('fitur_ids')) {
                $peran->fitur()->sync($request->fitur_ids);
            }

            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Peran berhasil diperbarui'
                    : 'Role updated successfully',
                'data' => $peran->load('fitur')
            ]);
        } catch (ValidationException $e) {
            $error = collect($e->errors())->flatten()->first();
            return response()->json([
                'message' => $error,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // ====== HAPUS PERAN ======
    public function destroy(Request $request, $id)
    {
        $peran = Peran::find($id);

        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        if (!$peran) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Peran tidak ditemukan'
                    : 'Role not found',
            ], 404);
        }

        // Set user peran menjadi null dulu
        User::where('peran_id', $id)->update(['peran_id' => null]);

        $peran->delete();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Peran berhasil dihapus'
                : 'Role deleted successfully',
        ]);
    }
}
