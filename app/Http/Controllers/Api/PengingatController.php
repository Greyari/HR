<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengingat;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengingatController extends Controller
{
    // Beri bahasa user
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    // Helper format sisa waktu
    private function formatSisa($tanggal)
    {
        $now = now();
        $diffInDays = $now->diffInDays($tanggal, false);
        $diffInHours = $now->diffInHours($tanggal, false);

        $hari = (int) $diffInDays;
        $jam = (int) $diffInHours;

        $sisaHari = $hari > 0
            ? $hari . ' hari lagi'
            : ($hari == 0 ? 'Hari ini' : abs($hari) . ' hari yang lalu');

        $sisaJam = abs($jam) < 24
            ? ($jam > 0 ? $jam . ' jam lagi' : abs($jam) . ' jam yang lalu')
            : null;

        $relative = ($hari == 0 && $sisaJam) ? $sisaJam : $sisaHari;

        return [
            'sisa_hari' => $sisaHari,
            'sisa_jam' => $sisaJam,
            'relative' => $relative,
        ];
    }

    // ========================= INDEX =========================
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

    // ========================= STORE =========================
    public function store(Request $request)
    {
        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $messages = [
            'indonesia' => [
                'peran_id.required' => 'PIC wajib dipilih.',
                'peran_id.exists' => 'PIC tidak ditemukan.',
                'judul.required' => 'Judul wajib diisi.',
                'judul.unique' => 'Judul pengingat sudah digunakan.',
                'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
                'tanggal_jatuh_tempo.date' => 'Format tanggal tidak valid.',
                'status.in' => 'Status harus Pending, Selesai, atau Terlambat.',
            ],
            'inggris' => [
                'peran_id.required' => 'PIC is required.',
                'peran_id.exists' => 'PIC not found.',
                'judul.required' => 'Title is required.',
                'judul.unique' => 'Reminder title already exists.',
                'tanggal_jatuh_tempo.required' => 'Due date is required.',
                'tanggal_jatuh_tempo.date' => 'Invalid date format.',
                'status.in' => 'Status must be Pending, Completed, or Late.',
            ]
        ];

        $validated = $request->validate([
            'peran_id' => 'required|exists:peran,id',
            'judul' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pengingat', 'judul'),
            ],
            'deskripsi' => 'nullable|string',
            'tanggal_jatuh_tempo' => 'required|date',
            'status' => 'in:Pending,Selesai,Terlambat',
        ], $messages[$bahasa]);

        $validated['tanggal_jatuh_tempo'] = \Carbon\Carbon::parse($validated['tanggal_jatuh_tempo'])
            ->setTime(23, 59, 59);

        $pengingat = Pengingat::create($validated);

        return response()->json([
            'message' => $bahasa === 'inggris'
                ? 'Reminder created successfully'
                : 'Pengingat berhasil dibuat',
            'data' => $pengingat
        ], 201);
    }

    // ========================= UPDATE =========================
    public function update(Request $request, $id)
    {
        $pengingat = Pengingat::findOrFail($id);

        $userId = $request->user()->id ?? null;
        $bahasa = $this->getUserLanguage($userId);

        $messages = [
            'indonesia' => [
                'peran_id.exists' => 'PIC tidak ditemukan.',
                'judul.unique' => 'Judul pengingat sudah digunakan.',
                'tanggal_jatuh_tempo.date' => 'Format tanggal tidak valid.',
                'status.in' => 'Status harus Pending, Selesai, atau Terlambat.',
            ],
            'inggris' => [
                'peran_id.exists' => 'PIC not found.',
                'judul.unique' => 'Reminder title already exists.',
                'tanggal_jatuh_tempo.date' => 'Invalid date format.',
                'status.in' => 'Status must be Pending, Completed, or Late.',
            ]
        ];

        $validated = $request->validate([
            'peran_id' => 'sometimes|exists:peran,id',
            'judul' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('pengingat', 'judul')->ignore($pengingat->id),
            ],
            'deskripsi' => 'nullable|string',
            'tanggal_jatuh_tempo' => 'sometimes|date',
            'status' => 'sometimes|in:Pending,Selesai,Terlambat',
        ], $messages[$bahasa]);

        // jika update tanggal
        if (isset($validated['tanggal_jatuh_tempo'])) {
            $validated['tanggal_jatuh_tempo'] = \Carbon\Carbon::parse($validated['tanggal_jatuh_tempo'])
                ->setTime(23, 59, 59);
        }

        $pengingat->update($validated);

        return response()->json([
            'message' => $bahasa === 'inggris'
                ? 'Reminder updated successfully'
                : 'Pengingat berhasil diperbarui',
            'data' => $pengingat
        ]);
    }

    // ========================= DELETE =========================
    public function destroy($id)
    {
        $pengingat = Pengingat::findOrFail($id);
        $pengingat->delete();

        return response()->json(['message' => 'Pengingat berhasil dihapus']);
    }
}
