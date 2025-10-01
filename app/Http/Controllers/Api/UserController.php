<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // List semua user
    public function index() {
        $users = User::with(['peran', 'departemen', 'jabatan'])->get()
            ->map(function ($u) {
                $u->gaji_per_hari = (fmod($u->gaji_per_hari, 1) == 0.0)
                    ? (int) $u->gaji_per_hari
                    : (float) $u->gaji_per_hari;
                return $u;
            });

        return response()->json([
            'message' => 'Data user berhasil diambil',
            'data' => $users
        ]);
    }

    // Simpan user baru
    public function store(Request $request)
    {
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
            'password' => 'required|string|min:1',
        ]);

        // Untuk generate email otomatis
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
            'message' => 'Karyawan berhasil dibuat',
            'data' => $karyawan
        ], 201);
    }

    // Update User
    public function update (Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $request->validate([
            'nama' => 'nullable|string|max:255',
            'peran_id' => 'nullable|exists:peran,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'gaji_per_hari' => 'numeric|min:0',
            'npwp' => ['nullable', 'string', Rule::unique('users','npwp')->ignore($user->id)],
            'bpjs_kesehatan' => ['nullable', 'string', Rule::unique('users','bpjs_kesehatan')->ignore($user->id)],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', Rule::unique('users','bpjs_ketenagakerjaan')->ignore($user->id)],
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'status_pernikahan' => 'nullable|in:Menikah,Belum Menikah',
        ]);

        $user->update($request->only([
            'nama',
            'peran_id',
            'jabatan_id',
            'departemen_id',
            'gaji_per_hari',
            'npwp',
            'bpjs_kesehatan',
            'bpjs_ketenagakerjaan',
            'jenis_kelamin',
            'status_pernikahan'
        ]));

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data' => $user
        ]);
    }

    // Hapus user
    public function destroy ($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['User tidak ditemukan']);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ]);
    }

}
