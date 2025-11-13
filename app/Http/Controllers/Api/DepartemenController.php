<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DepartemenController extends Controller
{

    /**
     * Ambil preferensi bahasa dari pengaturan user.
     */
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    // List semua departemen
    public function index()
    {
        $departemen = Departemen::all();

        return response()->json([
            'message' => 'Data departemen berhasil diambil',
            'data' => $departemen
        ]);
    }

    // Simpan departemen baru
    public function store(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $messages = [
            'indonesia' => [
                'nama_departemen.required' => 'Nama departemen wajib diisi.',
                'nama_departemen.unique'   => 'Nama departemen sudah digunakan.',
                'nama_departemen.max'      => 'Nama departemen tidak boleh lebih dari 255 karakter.',
            ],
            'inggris' => [
                'nama_departemen.required' => 'Department name is required.',
                'nama_departemen.unique'   => 'Department name has already been taken.',
                'nama_departemen.max'      => 'Department name must not exceed 255 characters.',
            ],
        ];

        try {
            $validated = $request->validate([
                'nama_departemen' => 'required|string|max:255|unique:departemen,nama_departemen',
            ], $messages[$bahasa]);

            $departemen = Departemen::create($validated);

            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Departemen berhasil dibuat'
                    : 'Department created successfully',
                'data' => $departemen
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

    // ====== UPDATE DEPARTEMEN ======
    public function update(Request $request, $id)
    {
        $departemen = Departemen::find($id);
        if (!$departemen) {
            return response()->json(['message' => 'Departemen tidak ditemukan'], 404);
        }

        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $messages = [
            'indonesia' => [
                'nama_departemen.required' => 'Nama departemen wajib diisi.',
                'nama_departemen.unique'   => 'Nama departemen sudah digunakan.',
                'nama_departemen.max'      => 'Nama departemen tidak boleh lebih dari 255 karakter.',
            ],
            'inggris' => [
                'nama_departemen.required' => 'Department name is required.',
                'nama_departemen.unique'   => 'Department name has already been taken.',
                'nama_departemen.max'      => 'Department name must not exceed 255 characters.',
            ],
        ];

        try {
            $validated = $request->validate([
                'nama_departemen' => 'required|string|max:255|unique:departemen,nama_departemen,' . $id,
            ], $messages[$bahasa]);

            $departemen->update($validated);

            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Departemen berhasil diperbarui'
                    : 'Department updated successfully',
                'data' => $departemen
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

    // ====== HAPUS DEPARTEMEN ======
    public function destroy(Request $request, $id)
    {
        $departemen = Departemen::find($id);

        // Ambil bahasa dari pengaturan user
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        if (!$departemen) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Departemen tidak ditemukan'
                    : 'Department not found',
            ], 404);
        }

        $departemen->delete();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Departemen berhasil dihapus'
                : 'Department deleted successfully',
        ]);
    }

}
