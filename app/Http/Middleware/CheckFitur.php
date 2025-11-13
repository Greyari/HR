<?php

// namespace App\Http\Middleware;
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFitur
{
    public function handle(Request $request, Closure $next, ...$fiturNames)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User tidak login'], 401);
        }

        if (!$user->peran) {
            return response()->json(['message' => 'User tidak punya peran'], 403);
        }

        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // cek apakah user punya salah satu fitur yang diminta
        $punyaIzin = false;
        foreach ($fiturNames as $fitur) {
            if (in_array($fitur, $fiturUser)) {
                $punyaIzin = true;
                break;
            }
        }

        if (!$punyaIzin) {
            return response()->json([
                'message' => 'Fitur tidak ditemukan pada peran user',
                'fitur_diminta' => $fiturNames,
                'fitur_user' => $fiturUser
            ], 403);
        }

        return $next($request);
    }
}
