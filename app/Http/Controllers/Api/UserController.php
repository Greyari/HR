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

        // Pesan validasi multi bahasa
        $messages = [
            'indonesia' => [
                'nama.required' => 'Nama wajib diisi.',
                'nama.max' => 'Nama tidak boleh lebih dari 255 karakter.',
                'peran_id.required' => 'Peran wajib dipilih.',
                'peran_id.exists' => 'Peran tidak valid.',
                'jabatan_id.exists' => 'Jabatan tidak valid.',
                'departemen_id.exists' => 'Departemen tidak valid.',
                'gaji_per_hari.required' => 'Gaji per hari wajib diisi.',
                'gaji_per_hari.numeric' => 'Gaji per hari harus berupa angka.',
                'npwp.required' => 'NPWP wajib diisi.',
                'npwp.unique' => 'NPWP sudah digunakan.',
                'bpjs_kesehatan.required' => 'BPJS Kesehatan wajib diisi.',
                'bpjs_kesehatan.unique' => 'BPJS Kesehatan sudah digunakan.',
                'bpjs_ketenagakerjaan.required' => 'BPJS Ketenagakerjaan wajib diisi.',
                'bpjs_ketenagakerjaan.unique' => 'BPJS Ketenagakerjaan sudah digunakan.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
                'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
                'status_pernikahan.required' => 'Status pernikahan wajib dipilih.',
                'status_pernikahan.in' => 'Status pernikahan tidak valid.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
            ],
            'inggris' => [
                'nama.required' => 'Name is required.',
                'nama.max' => 'Name must not exceed 255 characters.',
                'peran_id.required' => 'Role is required.',
                'peran_id.exists' => 'Role is invalid.',
                'jabatan_id.exists' => 'Position is invalid.',
                'departemen_id.exists' => 'Department is invalid.',
                'gaji_per_hari.required' => 'Daily salary is required.',
                'gaji_per_hari.numeric' => 'Daily salary must be numeric.',
                'npwp.required' => 'NPWP is required.',
                'npwp.unique' => 'NPWP has already been taken.',
                'bpjs_kesehatan.required' => 'BPJS Kesehatan is required.',
                'bpjs_kesehatan.unique' => 'BPJS Kesehatan has already been taken.',
                'bpjs_ketenagakerjaan.required' => 'BPJS Ketenagakerjaan is required.',
                'bpjs_ketenagakerjaan.unique' => 'BPJS Ketenagakerjaan has already been taken.',
                'jenis_kelamin.required' => 'Gender is required.',
                'jenis_kelamin.in' => 'Gender is invalid.',
                'status_pernikahan.required' => 'Marital status is required.',
                'status_pernikahan.in' => 'Marital status is invalid.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 6 characters.',
            ],
        ];

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'peran_id' => 'required|exists:peran,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'gaji_per_hari' => 'required|numeric|min:0',
            'npwp' => [
                'required',
                'string',
                'unique:users,npwp',
                'regex:/^[0-9.\-\/]+$/',
            ],
            'bpjs_kesehatan' => [
                'required',
                'string',
                'unique:users,bpjs_kesehatan',
                'regex:/^[0-9]+$/',
            ],
            'bpjs_ketenagakerjaan' => [
                'required',
                'string',
                'unique:users,bpjs_ketenagakerjaan',
                'regex:/^[0-9]+$/', 
            ],
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'status_pernikahan' => 'required|in:Menikah,Belum Menikah',
            'password' => 'required|string|min:6',
        ], $messages[$bahasa]);


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
                'message' => $bahasa === 'indonesia' ? 'User tidak ditemukan' : 'User not found',
            ], 404);
        }

        // Pesan validasi multi bahasa untuk update
        $messages = [
            'indonesia' => [
                'nama.max' => 'Nama tidak boleh lebih dari 255 karakter.',
                'peran_id.exists' => 'Peran tidak valid.',
                'jabatan_id.exists' => 'Jabatan tidak valid.',
                'departemen_id.exists' => 'Departemen tidak valid.',
                'gaji_per_hari.numeric' => 'Gaji per hari harus berupa angka.',
                'gaji_per_hari.min' => 'Gaji per hari minimal 0.',
                'npwp.unique' => 'NPWP sudah digunakan.',
                'bpjs_kesehatan.unique' => 'BPJS Kesehatan sudah digunakan.',
                'bpjs_ketenagakerjaan.unique' => 'BPJS Ketenagakerjaan sudah digunakan.',
                'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
                'status_pernikahan.in' => 'Status pernikahan tidak valid.',
                'password.min' => 'Password minimal 6 karakter.',
            ],
            'inggris' => [
                'nama.max' => 'Name must not exceed 255 characters.',
                'peran_id.exists' => 'Role is invalid.',
                'jabatan_id.exists' => 'Position is invalid.',
                'departemen_id.exists' => 'Department is invalid.',
                'gaji_per_hari.numeric' => 'Daily salary must be numeric.',
                'gaji_per_hari.min' => 'Daily salary must be at least 0.',
                'npwp.unique' => 'NPWP has already been taken.',
                'bpjs_kesehatan.unique' => 'BPJS Kesehatan has already been taken.',
                'bpjs_ketenagakerjaan.unique' => 'BPJS Ketenagakerjaan has already been taken.',
                'jenis_kelamin.in' => 'Gender is invalid.',
                'status_pernikahan.in' => 'Marital status is invalid.',
                'password.min' => 'Password must be at least 6 characters.',
            ],
        ];

        $request->validate([
            'nama' => 'nullable|string|max:255',
            'peran_id' => 'nullable|exists:peran,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'gaji_per_hari' => 'numeric|min:0',
            'npwp' => [
                'nullable',
                'string',
                Rule::unique('users', 'npwp')->ignore($user->id),
                'regex:/^[0-9.\-\/]+$/', // angka, titik, strip, atau garis miring
            ],
            'bpjs_kesehatan' => [
                'nullable',
                'string',
                Rule::unique('users', 'bpjs_kesehatan')->ignore($user->id),
                'regex:/^[0-9]+$/', // hanya angka
            ],
            'bpjs_ketenagakerjaan' => [
                'nullable',
                'string',
                Rule::unique('users', 'bpjs_ketenagakerjaan')->ignore($user->id),
                'regex:/^[0-9]+$/', // hanya angka
            ],
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'status_pernikahan' => 'nullable|in:Menikah,Belum Menikah',
            'password' => 'nullable|string|min:6',
        ], $messages[$bahasa]);

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
            'message' => $bahasa === 'indonesia' ? 'User berhasil diperbarui' : 'User updated successfully',
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
