<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PotonganGaji;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PotonganGajiController extends Controller
{
    /**
     * Ambil bahasa pengguna dari tabel pengaturan
     */
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    // ================== LIST ==================
    public function index()
    {
        $potongangaji = PotonganGaji::all();

        return response()->json([
            'message' => 'Data potongan gaji berhasil diambil',
            'data' => $potongangaji
        ]);
    }

    // ================== STORE ==================
    public function store(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        // Pesan validasi bilingual
        $messages = [
            'indonesia' => [
                'nama_potongan.required' => 'Nama potongan wajib diisi.',
                'nama_potongan.unique' => 'Nama potongan sudah digunakan.',
                'persen.required' => 'Persentase wajib diisi.',
                'persen.regex' => 'Persentase hanya boleh angka.',
                'persen.min' => 'Persentase minimal 0.',
                'persen.max' => 'Persentase maksimal 100.',
            ],
            'inggris' => [
                'nama_potongan.required' => 'Deduction name is required.',
                'nama_potongan.unique' => 'Deduction name already exists.',
                'persen.required' => 'Percentage is required.',
                'persen.regex' => 'Percentage must contain numbers only.',
                'persen.min' => 'Percentage cannot be less than 0.',
                'persen.max' => 'Percentage cannot be greater than 100.',
            ]
        ];

        $request->validate([
            'nama_potongan' => 'required|string|max:255|unique:potongan_gaji,nama_potongan',
            'persen' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'regex:/^[0-9]+(\.[0-9]+)?$/'
            ],
        ], $messages[$bahasa]);

        $potongan = PotonganGaji::create([
            'nama_potongan' => $request->nama_potongan,
            'persen' => $request->persen,
        ]);

        return response()->json([
            'message' => $bahasa === 'inggris'
                ? 'Deduction added successfully'
                : 'Potongan berhasil ditambahkan',
            'data' => $potongan
        ], 201);
    }

    // ================== UPDATE ==================
    public function update(Request $request, $id)
    {
        $potongan = PotonganGaji::findOrFail($id);

        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $messages = [
            'indonesia' => [
                'nama_potongan.required' => 'Nama potongan wajib diisi.',
                'nama_potongan.unique' => 'Nama potongan sudah digunakan.',
                'persen.required' => 'Persentase wajib diisi.',
                'persen.regex' => 'Persentase hanya boleh angka.',
                'persen.min' => 'Persentase minimal 0.',
                'persen.max' => 'Persentase maksimal 100.',
            ],
            'inggris' => [
                'nama_potongan.required' => 'Deduction name is required.',
                'nama_potongan.unique' => 'Deduction name already exists.',
                'persen.required' => 'Percentage is required.',
                'persen.regex' => 'Percentage must contain numbers only.',
                'persen.min' => 'Percentage cannot be less than 0.',
                'persen.max' => 'Percentage cannot be greater than 100.',
            ]
        ];

        $request->validate([
            'nama_potongan' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('potongan_gaji', 'nama_potongan')->ignore($potongan->id),
            ],
            'persen' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
                'max:100',
                'regex:/^[0-9]+(\.[0-9]+)?$/'
            ],
        ], $messages[$bahasa]);

        $potongan->update($request->only(['nama_potongan', 'persen']));

        return response()->json([
            'message' => $bahasa === 'inggris'
                ? 'Deduction updated successfully'
                : 'Potongan berhasil diperbarui',
            'data' => $potongan
        ]);
    }

    // ================== DELETE ==================
    public function destroy($id)
    {
        $potongan = PotonganGaji::findOrFail($id);
        $potongan->delete();

        return response()->json([
            'message' => 'Potongan berhasil dihapus'
        ]);
    }
}
