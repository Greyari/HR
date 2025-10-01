<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Auth;

class PengaturanController extends Controller
{
    /**
     * Ambil pengaturan user saat ini
     */
    public function show()
    {
        $user = Auth::user();

        $pengaturan = Pengaturan::firstOrCreate(
            ['user_id' => $user->id],
            ['tema' => 'gelap', 'bahasa' => 'indonesia']
        );

        return response()->json([
            'message' => 'Pengaturan berhasil diambil',
            'data' => $pengaturan,
        ]);
    }

    /**
     * Update tema dan bahasa
     */
    public function update(Request $request)
    {
        $request->validate([
            'tema' => 'required|in:terang,gelap',
            'bahasa' => 'required|in:indonesia,inggris',
        ]);

        $user = Auth::user();

        $pengaturan = Pengaturan::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tema' => $request->tema,
                'bahasa' => $request->bahasa,
            ]
        );

        return response()->json([
            'message' => 'Pengaturan berhasil diperbarui',
            'data' => $pengaturan,
        ]);
    }
}
