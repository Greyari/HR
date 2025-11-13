<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Ambil bahasa pengguna dari tabel pengaturan
     */
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    /**
     * List semua user
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $users = User::with(['peran', 'departemen', 'jabatan'])->get()
            ->map(function ($u) {
                $u->gaji_per_hari = (fmod($u->gaji_per_hari, 1) == 0.0)
                    ? (int) $u->gaji_per_hari
                    : (float) $u->gaji_per_hari;
                return $u;
            });

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Data user berhasil diambil'
                : 'User data retrieved successfully',
            'data' => $users
        ]);
    }

    /**
     * Simpan user baru
     */
    public function store(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $request->validate([
            'nama' => 'required|string|max:255',
            'peran_id' => 'required|exists:peran,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'gaji_per_hari' => 'required|numeric|min:0',
            'npwp' => 'required|string|unique:users,npwp',
            'bpjs_kesehatan' => 'required|string|unique:users,bpjs_kesehatan',
            'bpjs_ketenagakerjaan' => 'required|string|unique:users,bpjs_ketenagakerjaan',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'status_pernikahan' => 'required|in:Menikah,Belum Menikah',
            'password' => 'required|string|min:6',
        ]);

        // Generate email otomatis
        $namaDepan = strtolower(str_replace(' ', '', explode(' ', $request->nama)[0]));
        $email = $namaDepan . '@gmail.com';
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = $namaDepan . $counter . '@gmail.com';
            $counter++;
        }

        $karyawan = User::create([
            'nama' => $request->nama,
            'email' => $email,
            'peran_id' => $request->peran_id,
            'jabatan_id' => $request->jabatan_id,
            'departemen_id' => $request->departemen_id,
            'gaji_per_hari' => $request->gaji_per_hari,
            'npwp' => $request->npwp,
            'bpjs_kesehatan' => $request->bpjs_kesehatan,
            'bpjs_ketenagakerjaan' => $request->bpjs_ketenagakerjaan,
            'jenis_kelamin' => $request->jenis_kelamin,
            'status_pernikahan' => $request->status_pernikahan,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Karyawan berhasil dibuat'
                : 'Employee created successfully',
            'data' => $karyawan
        ], 201);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $currentUser = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($currentUser);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'User tidak ditemukan'
                    : 'User not found',
            ], 404);
        }

        $request->validate([
            'nama' => 'nullable|string|max:255',
            'peran_id' => 'nullable|exists:peran,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'gaji_per_hari' => 'numeric|min:0',
            'npwp' => ['nullable', 'string', Rule::unique('users', 'npwp')->ignore($user->id)],
            'bpjs_kesehatan' => ['nullable', 'string', Rule::unique('users', 'bpjs_kesehatan')->ignore($user->id)],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', Rule::unique('users', 'bpjs_ketenagakerjaan')->ignore($user->id)],
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'status_pernikahan' => 'nullable|in:Menikah,Belum Menikah',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only([
            'nama',
            'peran_id',
            'jabatan_id',
            'departemen_id',
            'gaji_per_hari',
            'npwp',
            'bpjs_kesehatan',
            'bpjs_ketenagakerjaan',
            'jenis_kelamin',
            'status_pernikahan',
        ]);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'User berhasil diperbarui'
                : 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Hapus user
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($currentUser);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'User tidak ditemukan'
                    : 'User not found',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'User berhasil dihapus'
                : 'User deleted successfully',
        ]);
    }
}
