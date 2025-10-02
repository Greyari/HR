<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    // cek email
    public function sendResetLink(Request $request)
    {
        // Validasi input email
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email ini belum terdaftar di sistem.'
        ]);

        // Kirim link reset password
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Response JSON ke Flutter
        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'status' => 'success',
                'message' => 'Link reset password sudah dikirim ke email Anda.'
            ])
            : response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengirim email reset password.'
            ], 500);
    }

    // Reset password user berdasarkan token
    public function reset(Request $request)
    {
        // Validasi form web dengan pesan khusus
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
        ], [
            'token.required' => 'Token reset password tidak ditemukan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email ini belum terdaftar di sistem.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sama dengan password baru.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Proses reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('password.reset.success');
        }

        // Kalau gagal, kembali ke form dengan error
        return back()->withErrors(['email' => 'Token tidak valid atau sudah kadaluarsa.']);
    }


    // tampilkan form
    public function showResetForm(Request $request)
    {
        return view('form.reset-password', [
            'token' => $request->token,
            'email' => $request->email
        ]);
    }

    public function pagesucces()
    {
        return view('form.succes-reset-password');
    }
}

