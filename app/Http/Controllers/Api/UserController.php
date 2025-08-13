<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // List semua user
    public function index() {
        $user = User::with(['peran', 'departemen'],)->get();

        return response()->json([
            'message' => 'Data user berhasil diambil',
            'data' => $user
        ]);
    }

    // Simpan user baru
    public function store (request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'peran_id' => 'required|exists:peran,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'departemen_id' => 'nullable|exists:departemen,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $karyawan = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'peran_id' => $request->peran_id,
            'jabatan_id' => $request->jabatan_id,
            'departemen_id' => $request->departemen_id,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'Karyawan berhasil dibuat',
            'data' => $karyawan
        ]);
    }
}
