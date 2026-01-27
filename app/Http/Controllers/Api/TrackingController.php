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

        // Update lokasi + timestamp
        $request->user()->update([
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'updated_at' => now(), // Force update timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil diupdate',
            'data' => [
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'updated_at' => $request->user()->updated_at->toDateTimeString(),
            ]
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
                'email',
                'jabatan_id',
                'departemen_id',
                'latitude',
                'longitude',
                'updated_at'
            )
            ->with(['jabatan:id,nama_jabatan', 'departemen:id,nama_departemen']) // Load relasi
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($user) {
                $lastUpdateMinutes = $user->updated_at->diffInMinutes(now());

                return [
                    'id'        => $user->id,
                    'nama'      => $user->nama,
                    'email'     => $user->email,
                    'jabatan'   => $user->jabatan?->nama_jabatan,
                    'departemen' => $user->departemen?->nama_departemen,
                    'latitude'  => (float) $user->latitude,
                    'longitude' => (float) $user->longitude,

                    // Status: aktif jika update dalam 5 menit terakhir
                    'status'    => $lastUpdateMinutes <= 5
                        ? 'aktif'
                        : 'tidak_aktif',

                    'last_update' => $user->updated_at->toDateTimeString(),
                    'last_update_minutes' => $lastUpdateMinutes,
                    'last_update_human' => $this->getHumanReadableTime($lastUpdateMinutes),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $users,
            'summary' => [
                'total' => $users->count(),
                'aktif' => $users->where('status', 'aktif')->count(),
                'tidak_aktif' => $users->where('status', 'tidak_aktif')->count(),
            ]
        ]);
    }

    /**
     * Get detail user location by ID
     */
    public function show($id)
    {
        $user = User::select(
                'id',
                'nama',
                'email',
                'jabatan_id',
                'departemen_id',
                'latitude',
                'longitude',
                'updated_at',
                'created_at'
            )
            ->with(['jabatan:id,nama_jabatan', 'departemen:id,nama_departemen'])
            ->findOrFail($id);

        $lastUpdateMinutes = $user->updated_at->diffInMinutes(now());

        return response()->json([
            'status' => 'success',
            'data' => [
                'id'        => $user->id,
                'nama'      => $user->nama,
                'email'     => $user->email,
                'jabatan'   => $user->jabatan?->nama_jabatan,
                'departemen' => $user->departemen?->nama_departemen,
                'latitude'  => (float) $user->latitude,
                'longitude' => (float) $user->longitude,
                'status'    => $lastUpdateMinutes <= 5 ? 'aktif' : 'tidak_aktif',
                'last_update' => $user->updated_at->toDateTimeString(),
                'last_update_minutes' => $lastUpdateMinutes,
                'last_update_human' => $this->getHumanReadableTime($lastUpdateMinutes),
            ]
        ]);
    }

    /**
     * Get users with filter status
     */
    public function filtered(Request $request)
    {
        $filter = $request->query('status', 'all'); // all, aktif, tidak_aktif

        $users = User::select(
                'id',
                'nama',
                'email',
                'jabatan_id',
                'departemen_id',
                'latitude',
                'longitude',
                'updated_at'
            )
            ->with(['jabatan:id,nama_jabatan', 'departemen:id,nama_departemen'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($user) {
                $lastUpdateMinutes = $user->updated_at->diffInMinutes(now());

                return [
                    'id'        => $user->id,
                    'nama'      => $user->nama,
                    'email'     => $user->email,
                    'jabatan'   => $user->jabatan?->nama_jabatan,
                    'departemen' => $user->departemen?->nama_departemen,
                    'latitude'  => (float) $user->latitude,
                    'longitude' => (float) $user->longitude,
                    'status'    => $lastUpdateMinutes <= 5 ? 'aktif' : 'tidak_aktif',
                    'last_update' => $user->updated_at->toDateTimeString(),
                    'last_update_minutes' => $lastUpdateMinutes,
                    'last_update_human' => $this->getHumanReadableTime($lastUpdateMinutes),
                ];
            });

        // Apply filter
        if ($filter === 'aktif') {
            $users = $users->where('status', 'aktif')->values();
        } elseif ($filter === 'tidak_aktif') {
            $users = $users->where('status', 'tidak_aktif')->values();
        }

        return response()->json([
            'status' => 'success',
            'filter' => $filter,
            'data' => $users,
            'summary' => [
                'total' => $users->count(),
            ]
        ]);
    }

    /**
     * Helper: Convert minutes to human readable format
     */
    private function getHumanReadableTime($minutes)
    {
        if ($minutes < 1) {
            return 'Baru saja';
        } elseif ($minutes < 60) {
            return $minutes . ' menit yang lalu';
        } elseif ($minutes < 1440) { // < 24 jam
            $hours = floor($minutes / 60);
            return $hours . ' jam yang lalu';
        } else {
            $days = floor($minutes / 1440);
            return $days . ' hari yang lalu';
        }
    }
}
