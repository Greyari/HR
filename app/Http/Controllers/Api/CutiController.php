<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengaturan;

class CutiController extends Controller
{
    // Menampilkan daftar cuti
    public function index()
    {
        $user = Auth::user();
        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // base query
        $query = Cuti::with(['user.peran'])->latest();

        if (in_array('lihat_semua_cuti', $fiturUser)) {
            if (in_array('approve_cuti_step2', $fiturUser)) {
                // kalau punya kedua fitur, batasi hanya cuti yg sudah step1 ke atas
                $query->whereIn('approval_step', [1, 2, 3]);
            }
            // kalau hanya punya lihat_semua_cuti (tanpa approve step2) → semua cuti
        }
        else if (in_array('lihat_cuti_sendiri', $fiturUser)) {
            $query->where('user_id', $user->id);
        }
        else if (in_array('approve_cuti_step1', $fiturUser)) {
            // Step1 bisa lihat SEMUA cuti (tanpa filter approval_step)
        }
        else if (in_array('approve_cuti_step2', $fiturUser)) {
            // Step2 hanya lihat cuti yg sudah lolos step1, final, atau ditolak
            $query->whereIn('approval_step', [1, 2, 3]);
        }
        else {
            return response()->json([
                'message' => 'Anda belum diberikan akses untuk melihat cuti. Hubungi admin.',
                'data' => [],
            ], 403);
        }

        $cuti = $query->get();

        return response()->json([
            'message' => 'Data cuti berhasil diambil',
            'data' => $cuti
        ]);
    }

    // Menyimpan pengajuan cuti
    public function store(Request $request)
    {
        $user = Auth::user();
        $bahasa = Pengaturan::where('user_id', $user->id)->value('bahasa') ?? 'indonesia';

        // Bilingual messages
        $messages = [
            'indonesia' => [
                'tipe_cuti.required' => 'Tipe cuti wajib diisi.',
                'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
                'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
                'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
                'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
                'alasan.max' => 'Alasan tidak boleh lebih dari 255 karakter.',
            ],
            'inggris' => [
                'tipe_cuti.required' => 'Leave type is required.',
                'tanggal_mulai.required' => 'Start date is required.',
                'tanggal_mulai.date' => 'Invalid start date format.',
                'tanggal_selesai.required' => 'End date is required.',
                'tanggal_selesai.date' => 'Invalid end date format.',
                'tanggal_selesai.after_or_equal' => 'End date must be after or equal to start date.',
                'alasan.max' => 'Reason must not exceed 255 characters.',
            ]
        ];

        // Validasi
        $request->validate([
            'tipe_cuti' => 'required|string|max:50',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'nullable|string|max:255',
        ], $messages[$bahasa]);

        // Cek cuti aktif
        $masihAdaCuti = Cuti::where('user_id', $user->id)
            ->whereIn('status', ['Pending', 'Proses'])
            ->exists();

        if ($masihAdaCuti) {
            return response()->json([
                'message' => $bahasa === 'inggris'
                    ? 'You still have an unprocessed leave request. Finish it before creating a new one.'
                    : 'Anda masih memiliki pengajuan cuti yang belum diproses. Selesaikan dulu sebelum mengajukan cuti baru.'
            ], 400);
        }

        // Buat cuti baru
        $cuti = Cuti::create([
            'user_id' => $user->id,
            'tipe_cuti' => $request->tipe_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'status' => 'Pending'
        ]);

        // Kirim ke user pemohon (notifikasi lokal di HP-nya)
        NotificationHelper::sendCutiDiajukan($user, $cuti);

        // Kirim ke semua user dengan fitur approve tahap 1
        NotificationHelper::sendToFitur(
            'approve_cuti_step1',
            '📤 Pengajuan Cuti Baru',
            $user->nama . ' mengajukan cuti dari ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai,
            'cuti_perlu_approval'
        );

        return response()->json([
            'message' => $bahasa === 'inggris'
                ? 'Leave request submitted successfully'
                : 'Pengajuan cuti berhasil dikirim',
            'data' => $cuti
        ], 201);
    }


    // Approve cuti
    public function approve($id)
    {
        $user = Auth::user();
        $cuti = Cuti::find($id);
        if (!$cuti) return response()->json(['message' => 'Cuti tidak ditemukan'], 404);

        // Ambil fitur approve yang dimiliki user
        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // ====== STEP 1 APPROVE ======
        if (in_array('approve_cuti_step1', $fiturUser)) {
            // hanya bisa approve step 1
            if (!in_array($cuti->approval_step, [0, 3])) {
                return response()->json(['message' => 'Cuti sudah diproses tahap awal'], 400);
            }
            $cuti->approval_step = 1;
            $cuti->status = 'Proses';
            $cuti->save();

            // Kirim ke pemohon bahwa cutinya disetujui tahap awal
            NotificationHelper::sendCutiDisetujuiStep1($cuti->user, $cuti);

            // Kirim ke semua user yang punya fitur approve step2
            NotificationHelper::sendToFitur(
                'approve_cuti_step2',
                '📤 Cuti Perlu Persetujuan Final',
                $cuti->user->nama . ' cutinya disetujui tahap awal, perlu persetujuan final.',
                'cuti_perlu_approval_final'
            );

            return response()->json([
                'message' => 'Cuti disetujui tahap awal',
                'step' => $cuti->approval_step,
                'status' => $cuti->status,
                'data' => $cuti
            ]);
        }

        // ====== STEP 2 APPROVE ======
        if (in_array('approve_cuti_step2', $fiturUser)) {
            // hanya bisa approve step 2
            if ($cuti->approval_step !== 1) {
                return response()->json(['message' => 'Cuti harus disetujui tahap awal dulu'], 400);
            }

            $cuti->approval_step = 2;
            $cuti->status = 'Disetujui';
            $cuti->save();

            // Kirim ke pemohon bahwa cutinya disetujui final
            NotificationHelper::sendCutiDisetujuiFinal($cuti->user, $cuti);

            return response()->json([
                'message' => 'Cuti disetujui final',
                'step' => $cuti->approval_step,
                'status' => $cuti->status,
                'data' => $cuti
            ]);
        }

        return response()->json(['message' => 'Tidak memiliki izin approve'], 403);
    }


    // Decline cuti
    public function decline(Request $request, $id)
    {
        $user = Auth::user();
        $cuti = Cuti::find($id);

        if (!$cuti) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }

        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // cek apakah user punya fitur menolak cuti
        if (!in_array('decline_cuti', $fiturUser)) {
            return response()->json(['message' => 'Tidak memiliki izin menolak cuti'], 403);
        }

        // Validasi catatan revisi wajib diisi
        $request->validate([
            'catatan_penolakan' => 'required|string|max:255',
        ]);

        // Hanya bisa ditolak sebelum final approval
        if ($cuti->approval_step < 2) {
            $cuti->approval_step = 3;
            $cuti->status = 'Ditolak';
            $cuti->catatan_penolakan = $request->catatan_penolakan;
            $cuti->save();

            // Kirim ke pemohon bahwa cutinya ditolak
            NotificationHelper::sendCutiDitolak($cuti->user, $cuti);

            return response()->json([
                'message' => 'Cuti ditolak dengan catatan revisi',
                'step' => $cuti->approval_step,
                'status' => $cuti->status,
                'catatan_penolakan' => $cuti->catatan_penolakan,
                'data' => $cuti
            ]);
        }

        return response()->json(['message' => 'Cuti sudah final, tidak bisa ditolak'], 400);
    }
}
