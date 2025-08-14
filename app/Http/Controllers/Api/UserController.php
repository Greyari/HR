<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // List semua user
    public function index() {
        $user = User::with(['peran', 'departemen', 'jabatan'],)->get();

        return response()->json([
            'message' => 'Data user berhasil diambil',
            'data' => $user
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
            'gaji_pokok' => 'required|numeric|min:0',
            'npwp' => 'required|string|unique:users,npwp',
            'bpjs_kesehatan' => 'required|string|unique:users,bpjs_kesehatan',
            'bpjs_ketenagakerjaan' => 'required|string|unique:users,bpjs_ketenagakerjaan',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'status_pernikahan' => 'required|in:Menikah,Belum Menikah',
            'password' => 'required|string|min:8',
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
            'gaji_pokok' => $request->gaji_pokok,
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


}
