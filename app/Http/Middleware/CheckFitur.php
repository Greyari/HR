<?php

// namespace App\Http\Middleware;
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFitur
{
    public function handle(Request $request, Closure $next, $fitur)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User tidak login']);
        }

        if (!$user->peran) {
            return response()->json(['message' => 'User tidak punya peran']);
        }

        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        if (!in_array($fitur, $fiturUser)) {
            return response()->json([
                'message' => 'Fitur tidak ditemukan pada peran user',
                'fitur_diminta' => $fitur,
                'fitur_user' => $fiturUser
            ], 403);
        }

        return $next($request);
    }
}
