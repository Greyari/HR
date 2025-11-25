<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kantor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Pengaturan;

class KantorController extends Controller
{
    /**
     * Ambil bahasa user
     */
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    /**
     * Ambil data kantor
     */
    public function index()
    {
        $kantor = Kantor::first();

        if ($kantor) {
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
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        // === PESAN VALIDASI ===
        $messages = [
            'indonesia' => [
                'jam_masuk.required' => 'Jam masuk wajib diisi.',
                'jam_masuk.date_format' => 'Format jam masuk harus HH:mm.',
                'jam_keluar.required' => 'Jam keluar wajib diisi.',
                'jam_keluar.date_format' => 'Format jam keluar harus HH:mm.',
                'minimal_keterlambatan.required' => 'Minimal keterlambatan wajib diisi.',
                'minimal_keterlambatan.integer' => 'Minimal keterlambatan harus berupa angka.',
                'minimal_keterlambatan.min' => 'Minimal keterlambatan tidak boleh kurang dari 0.',
                'lat.required' => 'Latitude wajib diisi.',
                'lat.numeric' => 'Latitude harus berupa angka.',
                'lng.required' => 'Longitude wajib diisi.',
                'lng.numeric' => 'Longitude harus berupa angka.',
                'radius_meter.required' => 'Radius wajib diisi.',
                'radius_meter.integer' => 'Radius harus berupa angka.',
            ],
            'inggris' => [
                'jam_masuk.required' => 'Check-in time is required.',
                'jam_masuk.date_format' => 'Check-in time must be in HH:mm format.',
                'jam_keluar.required' => 'Check-out time is required.',
                'jam_keluar.date_format' => 'Check-out time must be in HH:mm format.',
                'minimal_keterlambatan.required' => 'Minimum lateness is required.',
                'minimal_keterlambatan.integer' => 'Minimum lateness must be a number.',
                'minimal_keterlambatan.min' => 'Minimum lateness cannot be less than 0.',
                'lat.required' => 'Latitude is required.',
                'lat.numeric' => 'Latitude must be a number.',
                'lng.required' => 'Longitude is required.',
                'lng.numeric' => 'Longitude must be a number.',
                'radius_meter.required' => 'Radius is required.',
                'radius_meter.integer' => 'Radius must be a number.',
            ]
        ];

        try {
            // === VALIDASI ===
            $validated = $request->validate([
                'jam_masuk' => 'required|date_format:H:i',
                'jam_keluar' => 'required|date_format:H:i',
                'minimal_keterlambatan' => 'required|integer|min:0',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'radius_meter' => 'required|integer',
            ], $messages[$bahasa]);

        } catch (ValidationException $e) {
            $errorMessages = $e->errors();
            $firstError = collect($errorMessages)->flatten()->first();

            return response()->json([
                'message' => $firstError,
                'errors' => $errorMessages,
            ], 422);
        }

        // === SIMPAN ATAU UPDATE ===
        $kantor = Kantor::first();

        if ($kantor) {
            $kantor->update($validated);
            $message = $bahasa === 'indonesia'
                ? 'Data kantor berhasil diperbarui'
                : 'Office profile updated successfully';
            $status = 200;
        } else {
            $kantor = Kantor::create($validated);
            $message = $bahasa === 'indonesia'
                ? 'Data kantor berhasil ditambahkan'
                : 'Office profile created successfully';
            $status = 201;
        }

        // Format jam
        $kantor->jam_masuk = substr($kantor->jam_masuk, 0, 5);
        $kantor->jam_keluar = substr($kantor->jam_keluar, 0, 5);

        return response()->json([
            'message' => $message,
            'data' => $kantor
        ], $status);
    }
}
