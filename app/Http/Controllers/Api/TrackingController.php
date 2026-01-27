<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class TrackingController extends Controller
{
    /**
     * Update lokasi user (dipanggil dari mobile)
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $request->user()->update($data);

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Ambil data lokasi user untuk page tracking (admin)
     */
    public function index()
    {
        $users = User::select(
                'id',
                'nama',
                'latitude',
                'longitude',
                'updated_at'
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($user) {
                return [
                    'id'        => $user->id,
                    'nama'      => $user->nama,
                    'latitude'  => $user->latitude,
                    'longitude' => $user->longitude,
                    'status'    => $user->updated_at->diffInMinutes(now()) <= 2
                        ? 'aktif'
                        : 'tidak_aktif',
                    'last_update' => $user->updated_at->toDateTimeString(),
                ];
            });

        return response()->json($users);
    }
}
