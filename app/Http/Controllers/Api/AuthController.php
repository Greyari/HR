<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // cek kredensial
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        // ambil user
        $user = User::with(['peran', 'departemen', 'jabatan'])->where('email', $request->email)->first();

        // buat token
        $token = $user->createToken('token_login')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'data' => $user,
        ]);
    }

    // ganti email
    public function updateEmail(Request $request)
    {
        // Dapatkan pengguna yang sedang login
        $user = Auth::user();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verifikasi password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah'
            ], 401);
        }

        // Update email pengguna
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'message' => 'Email berhasil diperbarui',
            'data' => ['email' => $user->email]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
