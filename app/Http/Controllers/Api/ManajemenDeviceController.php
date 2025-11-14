<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;

class ManajemenDeviceController extends Controller
{
    // Ambil device
    public function allDevices()
    {
        $devices = Device::with('user')->get();
        return response()->json([
            'message' => "Daftar semua device terdaftar",
            'data'    => $devices,
        ]);
    }

    // Reset device milik user tertentu (admin action)
    public function resetDevice($id)
    {
        $user = User::findOrFail($id);

        if ($user->device) {
            $user->device->delete(); // ini akan memicu observer
        }

        return response()->json([
            'message' => "Device untuk akun {$user->nama} berhasil direset. User bisa login dari device baru.",
        ]);
    }
}
