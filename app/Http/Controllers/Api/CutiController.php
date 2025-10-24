<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Helpers\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// note cuti semisal nilai id data cuti bukan 1 akan ada erro next gimana misal kalo gak 1 pun id dadatanya gak usa muncul error
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
        $request->validate([
            'tipe_cuti' => 'required|string|max:50',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Cek apakah user masih ada cuti yg belum selesai
        $masihAdaCuti = Cuti::where('user_id', $user->id)
            ->whereIn('status', ['Pending', 'Proses']) // cuti yg belum final
            ->exists();

        if ($masihAdaCuti) {
            return response()->json([
                'message' => 'Anda masih memiliki pengajuan cuti yang belum diproses. Selesaikan dulu sebelum mengajukan cuti baru.'
            ], 400);
        }

        // coba liat ini boleh di hapus gakkkkkkkkkkkkkkkkkkk?////////////////
        $lamaCuti = Carbon::parse($request->tanggal_mulai)->diffInDays(Carbon::parse($request->tanggal_selesai)) + 1;
        // coba liat ini boleh di hapus gakkkkkkkkkkkkkkkkkkk?////////////////

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
        NotificationHelper::sendToUser(
            $user,
            'Pengajuan Cuti Diterima',
            'Pengajuan cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' berhasil dikirim',
            'cuti'
        );

        // Kirim ke semua user dengan fitur approve tahap 1
        NotificationHelper::sendToFitur(
            'approve_cuti_step1',
            'Pengajuan Cuti Baru',
            $user->name . ' mengajukan cuti dari ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai,
            'cuti'
        );

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim',
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

        if (in_array('approve_cuti_step1', $fiturUser)) {
            // hanya bisa approve step 1
            if (!in_array($cuti->approval_step, [0, 3])) {
                return response()->json(['message' => 'Cuti sudah diproses tahap awal'], 400);
            }
            $cuti->approval_step = 1;
            $cuti->status = 'Proses';
            $cuti->save();

            // Kirim ke pemohon bahwa cutinya disetujui tahap awal
            NotificationHelper::sendToUser(
                $cuti->user,
                'Cuti Disetujui Tahap Awal',
                'Cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' disetujui tahap awal',
                'cuti'
            );

            // Kirim ke semua user yang punya fitur approve step2
            NotificationHelper::sendToFitur(
                'approve_cuti_step2',
                'Cuti Perlu Persetujuan Final',
                $cuti->user->name . ' cutinya disetujui tahap awal, perlu persetujuan final.',
                'cuti'
            );

            return response()->json([
                'message' => 'Cuti disetujui tahap awal',
                'step' => $cuti->approval_step,
                'status' => $cuti->status,
                'data' => $cuti
            ]);
        }

        if (in_array('approve_cuti_step2', $fiturUser)) {
            // hanya bisa approve step 2
            if ($cuti->approval_step !== 1) {
                return response()->json(['message' => 'Cuti harus disetujui tahap awal dulu'], 400);
            }

            // coba liat ini boleh di hapus gakkkkkkkkkkkkkkkkkkk?////////////////
            $lamaCuti = Carbon::parse($cuti->tanggal_mulai)->diffInDays(Carbon::parse($cuti->tanggal_selesai)) + 1;
            $tahun = Carbon::parse($cuti->tanggal_mulai)->year;
            // coba liat ini boleh di hapus gakkkkkkkkkkkkkkkkkkk?////////////////

            $cuti->approval_step = 2;
            $cuti->status = 'Disetujui';
            $cuti->save();

            // Kirim ke pemohon bahwa cutinya disetujui final
            NotificationHelper::sendToUser(
                $cuti->user,
                'Cuti Disetujui Final',
                'Cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' telah disetujui.',
                'cuti'
            );

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
            NotificationHelper::sendToUser(
                $cuti->user,
                'Cuti Ditolak',
                'Cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' ditolak. Catatan: ' . $cuti->catatan_penolakan,
                'cuti'
            );

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
