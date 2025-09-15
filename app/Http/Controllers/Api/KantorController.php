<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kantor;
use Illuminate\Http\Request;

class KantorController extends Controller
{
    /**
     * Ambil data kantor
     */
    public function index()
    {
        $kantor = Kantor::first();

        if ($kantor) {
            // format biar hanya HH:mm
            $kantor->jam_masuk = substr($kantor->jam_masuk, 0, 5);
            $kantor->jam_keluar = substr($kantor->jam_keluar, 0, 5);
        }

        return response()->json([
            'message' => 'Data kantor berhasil diambil',
            'data' => $kantor
        ]);
    }

    /**
     * Simpan atau update profil kantor
     */
    public function saveProfile(Request $request)
    {
        $request->validate([
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'minimal_keterlambatan' => 'required|integer|min:0',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius_meter' => 'required|integer',
            'jatah_cuti_tahunan' => 'required|integer|min:0'
        ]);

        $kantor = Kantor::first();

        if ($kantor) {
            $kantor->update($request->only([
                'jam_masuk',
                'jam_keluar',
                'minimal_keterlambatan',
                'lat',
                'lng',
                'radius_meter',
                'jatah_cuti_tahunan'
            ]));
            $message = 'Data kantor berhasil diperbarui';
            $status = 200;
        } else {
            $kantor = Kantor::create($request->only([
                'jam_masuk',
                'jam_keluar',
                'minimal_keterlambatan',
                'lat',
                'lng',
                'radius_meter',
                'jatah_cuti_tahunan'
            ]));
            $message = 'Data kantor berhasil ditambahkan';
            $status = 201;
        }

        // format biar hanya HH:mm saat dikirim
        $kantor->jam_masuk = substr($kantor->jam_masuk, 0, 5);
        $kantor->jam_keluar = substr($kantor->jam_keluar, 0, 5);

        return response()->json([
            'message' => $message,
            'data' => $kantor
        ], $status);
    }
}

