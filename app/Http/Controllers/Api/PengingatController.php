<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengingat;
use Illuminate\Http\Request;

class PengingatController extends Controller
{
    // Helper untuk format sisa hari & jam
    private function formatSisa($tanggal)
    {
        $now = now();

        // pakai floor biar bulat
        $diffInDays = $now->diffInDays($tanggal, false);
        $diffInHours = $now->diffInHours($tanggal, false);

        $hari = (int) $diffInDays;
        $jam = (int) $diffInHours;

        // Buat pesan sisa hari
        $sisaHari = $hari > 0
            ? $hari . ' hari lagi'
            : ($hari == 0
                ? 'Hari ini'
                : abs($hari) . ' hari yang lalu');

        // Buat pesan sisa jam (hanya kalau < 24 jam)
        $sisaJam = abs($jam) < 24
            ? ($jam > 0
                ? $jam . ' jam lagi'
                : abs($jam) . ' jam yang lalu')
            : null;

        // Relative custom (pakai jam kalau hari ini, selain itu pakai hari)
        $relative = ($hari == 0 && $sisaJam)
            ? $sisaJam
            : $sisaHari;

        return [
            'sisa_hari' => $sisaHari,
            'sisa_jam' => $sisaJam,
            'relative' => $relative,
        ];
    }

    // Nampilin data pengingat
    public function index()
    {
        $pengingat = Pengingat::with('peran')
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get()
            ->map(function ($item) {
                $tanggal = $item->tanggal_jatuh_tempo;
                $info = $this->formatSisa($tanggal);

                return [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'deskripsi' => $item->deskripsi,
                    'tanggal_jatuh_tempo' => $tanggal->format('d-m-Y H:i:s'),
                    'status' => $item->status,
                    'PIC' => $item->peran->nama_peran ?? null,
                ] + $info;
            });

        return response()->json([
            'message' => 'Data pengingat berhasil diambil',
            'data' => $pengingat
        ]);
    }

    // Nambahin pengingat
    public function store(Request $request)
    {
        $validated = $request->validate([
            'peran_id' => 'required|exists:peran,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_jatuh_tempo' => 'required|date',
            'status' => 'in:Pending,Selesai,Terlambat',
        ]);

        $validated['tanggal_jatuh_tempo'] = \Carbon\Carbon::parse($validated['tanggal_jatuh_tempo'])
            ->setTime(23, 59, 59);
            
        $pengingat = Pengingat::create($validated);

        return response()->json([
            'message' => 'Pengingat berhasil dibuat',
            'data' => $pengingat
        ], 201);
    }

    // Edit pengingat
    public function update(Request $request, $id)
    {
        $pengingat = Pengingat::findOrFail($id);

        $validated = $request->validate([
            'peran_id' => 'sometimes|exists:peran,id',
            'judul' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_jatuh_tempo' => 'sometimes|date',
            'status' => 'sometimes|in:Pending,Selesai,Terlambat',
        ]);

        $pengingat->update($validated);

        return response()->json([
            'message' => 'Pengingat berhasil diperbarui',
            'data' => $pengingat
        ]);
    }

    // Hapus pengingat
    public function destroy($id)
    {
        $pengingat = Pengingat::findOrFail($id);
        $pengingat->delete();

        return response()->json(['message' => 'Pengingat berhasil dihapus']);
    }
}
