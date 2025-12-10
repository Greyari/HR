<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\NotificationHelper;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengaturan;

class LemburController extends Controller
{

    // Method dummy untuk testing
    public function submitDummy(Request $request)
    {
        return response()->json([
            'message' => 'Dummy lembur submitted successfully',
            'data' => $request->all()
        ], 200);
    }


    // Menampilkan daftar lembur
    public function index()
    {
        $user = Auth::user();
        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // base query
        $query = Lembur::with(['user.peran'])->latest();

        if (in_array('lihat_semua_lembur', $fiturUser)) {
            if (in_array('approve_lembur_step2', $fiturUser)) {
                // ✅ Pengecualian:
                // kalau punya lihat_semua_lembur + approve_step2 → hanya lembur yg sudah lolos step1 ke atas
                $query->whereIn('approval_step', [1, 2, 3]);
            }
            // kalau cuma punya lihat_semua_lembur → semua lembur
        }
        else if (in_array('lihat_lembur_sendiri', $fiturUser)) {
            $query->where('user_id', $user->id);
        }
        else if (in_array('approve_lembur_step1', $fiturUser)) {
            // Step1 bisa lihat semua lembur (tanpa filter approval_step)
        }
        else if (in_array('approve_lembur_step2', $fiturUser)) {
            // Step2 hanya lembur yg sudah step1 ke atas
            $query->whereIn('approval_step', [1, 2, 3]);
        }
        else {
            return response()->json([
                'message' => 'Anda belum diberikan akses untuk melihat lembur. Hubungi admin.',
                'data' => [],
            ], 403);
        }

        $lembur = $query->get();

        return response()->json([
            'message' => 'Data lembur berhasil diambil',
            'data' => $lembur,
        ]);
    }

    // Menyimpan pengajuan lembur
    public function store(Request $request)
    {
        $user = Auth::user();
        $bahasa = Pengaturan::where('user_id', $user->id)->value('bahasa') ?? 'indonesia';

        // Messages bilingual
        $messages = [
            'indonesia' => [
                'tanggal.required' => 'Tanggal lembur wajib diisi.',
                'tanggal.date' => 'Format tanggal tidak valid.',
                'jam_mulai.required' => 'Jam mulai wajib diisi.',
                'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
                'jam_selesai.required' => 'Jam selesai wajib diisi.',
                'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
                'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
                'deskripsi.max' => 'Deskripsi tidak boleh lebih dari 255 karakter.',
            ],
            'inggris' => [
                'tanggal.required' => 'Overtime date is required.',
                'tanggal.date' => 'Invalid date format.',
                'jam_mulai.required' => 'Start time is required.',
                'jam_mulai.date_format' => 'Invalid start time format.',
                'jam_selesai.required' => 'End time is required.',
                'jam_selesai.date_format' => 'Invalid end time format.',
                'jam_selesai.after' => 'End time must be after start time.',
                'deskripsi.max' => 'Description must not exceed 255 characters.',
            ]
        ];

        // Validasi
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'deskripsi' => 'nullable|string|max:255',
        ], $messages[$bahasa]);

        // Cek lembur aktif
        $masihAdaLembur = Lembur::where('user_id', $user->id)
            ->whereIn('status', ['Pending', 'Proses'])
            ->exists();

        if ($masihAdaLembur) {
            return response()->json([
                'message' => $bahasa === 'inggris'
                    ? 'You still have an unprocessed overtime request. Finish it before creating a new one.'
                    : 'Anda masih memiliki pengajuan lembur yang belum diproses. Selesaikan dulu sebelum mengajukan lembur baru.'
            ], 400);
        }

        // Buat lembur baru
        $lembur = Lembur::create([
            'user_id' => $user->id,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'deskripsi' => $request->deskripsi,
            'status' => 'Pending',
        ]);

        // Kirim ke user pemohon (notifikasi lokal di HP-nya)
        NotificationHelper::sendLemburDiajukan($user, $lembur);

        // Kirim ke semua user dengan fitur approve tahap 1
        NotificationHelper::sendToFitur(
            'approve_lembur_step1',
            '📤 Pengajuan Lembur Baru',
            $user->nama . ' mengajukan lembur pada tanggal ' . $lembur->tanggal,
            'lembur_perlu_approval'
        );

        return response()->json([
            'message' => $bahasa === 'inggris'
                ? 'Overtime request submitted successfully'
                : 'Pengajuan lembur berhasil dikirim',
            'data' => $lembur
        ], 201);
    }

    // Approve lembur
    public function approve($id)
    {
        $user = Auth::user();
        $lembur = Lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Lembur tidak ditemukan'], 404);
        }

        // Ambil fitur approve yang dimiliki user
        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // ====== STEP 1 APPROVE ======
        if (in_array('approve_lembur_step1', $fiturUser)) {
            if (!in_array($lembur->approval_step, [0, 3])) {
                return response()->json(['message' => 'Lembur sudah diproses tahap awal'], 400);
            }
            $lembur->approval_step = 1;
            $lembur->status = 'Proses';
            $lembur->save();

            // Kirim ke pemohon
            NotificationHelper::sendLemburDisetujuiStep1($lembur->user, $lembur);

            // Kirim ke semua user yang punya fitur approve step2
            NotificationHelper::sendToFitur(
                'approve_lembur_step2',
                '📤 Lembur Perlu Persetujuan Final',
                $lembur->user->nama . ' lemburnya disetujui tahap awal, perlu persetujuan final.',
                'lembur_perlu_approval_final'
            );

            return response()->json([
                'message' => 'Lembur disetujui tahap awal',
                'step'    => $lembur->approval_step,
                'status'  => $lembur->status,
                'data'    => $lembur
            ]);
        }

        // ====== STEP 2 APPROVE ======
        if (in_array('approve_lembur_step2', $fiturUser)) {
            if ($lembur->approval_step !== 1) {
                return response()->json(['message' => 'Lembur harus disetujui tahap awal dulu'], 400);
            }

            $lembur->approval_step = 2;
            $lembur->status = 'Disetujui';
            $lembur->save();


            // Kirim ke pemohon bahwa lemburnya disetujui final
            NotificationHelper::sendLemburDisetujuiFinal($lembur->user, $lembur);

            return response()->json([
                'message' => 'Lembur disetujui final',
                'step'    => $lembur->approval_step,
                'status'  => $lembur->status,
                'data'    => $lembur
            ]);
        }

        return response()->json(['message' => 'Tidak memiliki izin approve'], 403);
    }

    // Decline lembur
    public function decline(Request $request, $id)
    {
        $user = Auth::user();
        $lembur = Lembur::find($id);

        if (!$lembur) {
            return response()->json(['message' => 'Lembur tidak ditemukan'], 404);
        }

        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // cek apakah user punya fitur menolak lembur
        if (!in_array('decline_lembur', $fiturUser)) {
            return response()->json(['message' => 'Tidak memiliki izin menolak lembur'], 403);
        }

        // Validasi catatan penolakan wajib diisi
        $request->validate([
            'catatan_penolakan' => 'required|string|max:255',
        ]);

        // Hanya bisa ditolak sebelum final approval
        if ($lembur->approval_step < 2) {
            $lembur->approval_step = 3;
            $lembur->status = 'Ditolak';
            $lembur->catatan_penolakan = $request->catatan_penolakan;
            $lembur->save();

            // Kirim ke pemohon bahwa lemburnya ditolak
            NotificationHelper::sendLemburDitolak($lembur->user, $lembur);

            return response()->json([
                'message'            => 'Lembur ditolak dengan catatan revisi',
                'step'               => $lembur->approval_step,
                'status'             => $lembur->status,
                'catatan_penolakan'  => $lembur->catatan_penolakan,
                'data'               => $lembur
            ]);
        }

        return response()->json(['message' => 'Lembur sudah final, tidak bisa ditolak'], 400);
    }
}
