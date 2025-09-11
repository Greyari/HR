<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Kantor;
use App\Models\UserJatahCuti;
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

        // Ambil fitur yang dimiliki user
        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        // Cek fitur
        if (in_array('lihat_semua_cuti', $fiturUser)) {
            // Super Admin & Admin Office melihat semua cuti
            $cuti = Cuti::with(['user.peran'])->latest()->get();
        } else if (in_array('lihat_cuti_sendiri', $fiturUser)) {
            // User biasa hanya melihat cuti miliknya
            $cuti = Cuti::with(['user.peran'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        } else {
            return response()->json([
                'message' => 'Anda belum diberikan akses untuk melihat cuti. Hubungi admin.',
                'data' => [],
            ], 403);
        }

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
        $lamaCuti = Carbon::parse($request->tanggal_mulai)
                     ->diffInDays(Carbon::parse($request->tanggal_selesai)) + 1;
        $tahun = Carbon::parse($request->tanggal_mulai)->year;

        // Jika tipe cuti Tahunan, cek jatah
        if ($request->tipe_cuti === 'Tahunan') {
            $kantor = Kantor::first();
            $jatah = UserJatahCuti::firstOrCreate(
                ['user_id' => $user->id, 'tahun' => $tahun],
                [
                    'jatah' => $kantor->jatah_cuti_tahunan,
                    'terpakai' => 0,
                    'sisa' => $kantor->jatah_cuti_tahunan
                ]
            );

            if ($lamaCuti > $jatah->sisa) {
                return response()->json([
                    'message' => 'Pengajuan cuti gagal. Sisa cuti tahunan: ' . $jatah->sisa
                ], 400);
            }
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

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim',
            'data' => $cuti
        ], 201);
    }

    // Update cuti (hanya pemilik cuti)
    public function update(Request $request, $id)
    {
        $cuti = Cuti::find($id);
        if (!$cuti) return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        if ($cuti->user_id !== Auth::id()) return response()->json(['message' => 'Tidak memiliki izin'], 403);

        $request->validate([
            'tipe_cuti' => 'required|string|max:50',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'nullable|string|max:255',
        ]);

        $cuti->update([
            'tipe_cuti' => $request->tipe_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
        ]);

        return response()->json(['message' => 'Cuti berhasil diperbarui', 'data' => $cuti]);
    }

    // Hapus cuti (hanya pemilik cuti)
    public function destroy($id)
    {
        $cuti = Cuti::find($id);
        if (!$cuti) return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        if ($cuti->user_id !== Auth::id()) return response()->json(['message' => 'Tidak memiliki izin'], 403);

        $cuti->delete();
        return response()->json(['message' => 'Cuti berhasil dihapus']);
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

            $lamaCuti = Carbon::parse($cuti->tanggal_mulai)
                ->diffInDays(Carbon::parse($cuti->tanggal_selesai)) + 1;
            $tahun = Carbon::parse($cuti->tanggal_mulai)->year;

            if ($cuti->tipe_cuti === 'Tahunan') {
                $jatah = UserJatahCuti::firstOrCreate(
                    ['user_id' => $cuti->user_id, 'tahun' => $tahun],
                    [
                        'jatah' => $cuti->user->kantor->jatah_cuti_tahunan ?? 12,
                        'terpakai' => 0,
                        'sisa' => $cuti->user->kantor->jatah_cuti_tahunan ?? 12
                    ]
                );
                $jatah->terpakai += $lamaCuti;
                $jatah->sisa -= $lamaCuti;
                $jatah->save();
            }

            $cuti->approval_step = 2;
            $cuti->status = 'Disetujui';
            $cuti->save();

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
    public function decline($id)
    {
        $user = Auth::user();
        $cuti = Cuti::find($id);
        if (!$cuti) return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        if (!in_array($user->peran_id, [1, 2])) return response()->json(['message' => 'Tidak memiliki izin'], 403);

        // Hanya bisa ditolak sebelum final approval
        if ($cuti->approval_step < 2) {
            $cuti->approval_step = 3;
            $cuti->status = 'Ditolak';
            $cuti->save();

            return response()->json([
                'message' => 'Cuti ditolak',
                'step' => $cuti->approval_step,
                'status' => $cuti->status,
                'data' => $cuti
            ]);
        }

        return response()->json(['message' => 'Cuti sudah final, tidak bisa ditolak'], 400);
    }
}
