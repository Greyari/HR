<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ManajemenAkunController extends Controller
{
    public function lockedUsers()
    {
        $users = User::where('terkunci', true)->get();

        return response()->json([
            'message' => 'Daftar akun terkunci',
            'data'    => $users
        ]);
    }

    // Endpoint untuk reset akun terkunci
    public function unlockUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'coba_login' => 0,
            'terkunci'   => false,
        ]);

        return response()->json([
            'message' => "Akun {$user->nama} berhasil dibuka kembali",
            'data'    => $user
        ]);
    }
}
