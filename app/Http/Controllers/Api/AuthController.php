<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
