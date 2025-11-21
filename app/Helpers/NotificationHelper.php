<?php

namespace App\Helpers;

use App\Models\User;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Kirim notifikasi ke semua user yang memiliki fitur tertentu.
     */
    public static function sendToFitur(string $namaFitur, string $title, string $message, ?string $type = null): int
    {
        $fcm = app(FirebaseService::class);

        $users = User::whereHas('peran.fitur', function ($q) use ($namaFitur) {
            $q->where('nama_fitur', $namaFitur);
        })->get();

        Log::info("=== DEBUG SEND TO FITUR ===");
        Log::info("Fitur dicari: " . $namaFitur);
        Log::info("Total user ditemukan: " . $users->count());

        if ($users->isEmpty()) {
            Log::warning("⚠️ TIDAK ADA USER dengan fitur: " . $namaFitur);
            return 0;
        }

        Log::info("User ditemukan:", $users->pluck('nama', 'id')->toArray());
        Log::info("Device tokens:", $users->mapWithKeys(function($user) {
            return [$user->nama => substr($user->device_token ?? 'null', 0, 30) . '...'];
        })->toArray());

        $sentCount = 0;
        foreach ($users as $user) {
            Log::info("--- Processing user: {$user->nama} (ID: {$user->id}) ---");

            if ($user->device_token) {
                Log::info("✅ Device token ada: " . substr($user->device_token, 0, 30) . "...");

                try {
                    $result = $fcm->sendMessage($user->device_token, $title, $message, [
                        'tipe' => $type,
                    ]);

                    if ($result) {
                        Log::info("📤 FCM berhasil dikirim ke {$user->nama}");
                        $sentCount++;
                    } else {
                        Log::error("❌ FCM GAGAL (return false) ke {$user->nama}");
                    }

                    Log::info("FCM Response: " . json_encode($result));

                } catch (\Exception $e) {
                    Log::error("❌ FCM EXCEPTION ke {$user->nama}: " . $e->getMessage());
                }
            } else {
                Log::warning("⚠️ User {$user->nama} TIDAK PUNYA device_token");
            }
        }

        Log::info("=== TOTAL NOTIFIKASI TERKIRIM: $sentCount dari {$users->count()} user ===");

        return $users->count();
    }

    /**
     * Kirim notifikasi langsung ke user tertentu.
     */
    public static function sendToUser($user, string $title, string $message, ?string $type = null, $tugas = null): void
    {
        if (!$user->device_token) return;

        $data = ['tipe' => $type];

        if ($type === 'tugas_update' && $tugas) {
            $data = array_merge($data, [
                'tugas_id' => (string) $tugas->id,
                'status' => $tugas->status,
                'judul' => $tugas->nama_tugas,
                'batas_penugasan' => Carbon::parse($tugas->batas_penugasan)->toIso8601String(),
            ]);
        }

        app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, $data);
    }

    /**
     * Kirim notifikasi saat tugas baru dibuat.
     */
    public static function sendTugasBaru($user, string $title, string $message, $tugas): void
    {
        if (!$user->device_token) return;

        Log::info('📤 Kirim Notifikasi Tugas Baru', [
            'kepada' => $user->nama . ' (ID: ' . $user->id . ')',
            'tugas' => $tugas->nama_tugas,
        ]);

        app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
            'tipe' => 'tugas_baru',
            'judul' => $tugas->nama_tugas,
            'tugas_id' => (string) $tugas->id,
            'target_user_id' => (string) $user->id, // ✅ Validasi di Flutter
            'batas_penugasan' => Carbon::parse($tugas->batas_penugasan)->toIso8601String(),
        ]);
    }

    /**
     * Kirim notifikasi saat tugas diperbarui oleh admin.
     */
    public static function sendTugasUpdate($user, $tugas): void
    {
        $title = 'Tugas Diperbarui';
        $message = 'Tugas "' . $tugas->nama_tugas . '" telah diperbarui oleh admin.';

        Log::info('📤 Kirim Notifikasi Tugas Update', [
            'kepada' => $user->nama . ' (ID: ' . $user->id . ')',
            'tugas' => $tugas->nama_tugas,
        ]);

        self::logAndSend($user, $title, $message, 'tugas_update', [
            'tugas_id' => (string) $tugas->id,
            'target_user_id' => (string) $user->id, // ✅ Validasi di Flutter
            'status' => $tugas->status,
            'judul' => $tugas->nama_tugas,
            'batas_penugasan' => Carbon::parse($tugas->batas_penugasan)->toIso8601String(),
        ]);
    }

    /**
     * Kirim notifikasi saat tugas dialihkan ke user lain.
     */
    public static function sendTugasDialihkan($userLama, $tugas): void
    {
        $title = 'Tugas Dipindahkan';
        $message = 'Tugas "' . $tugas->nama_tugas . '" telah dipindahkan ke user lain.';

        if ($userLama->device_token) {
            Log::info('📤 Kirim Notifikasi Tugas Dialihkan', [
                'kepada' => $userLama->nama . ' (ID: ' . $userLama->id . ')',
                'tugas' => $tugas->nama_tugas,
            ]);

            app(FirebaseService::class)->sendMessage(
                $userLama->device_token,
                $title,
                $message,
                [
                    'tipe' => 'tugas_pindah',
                    'tugas_id' => (string) $tugas->id,
                    'target_user_id' => (string) $userLama->id, // ✅ Validasi
                    'judul' => $tugas->nama_tugas,
                    'hapus_progress' => 'true',
                ]
            );
        }
    }

    /**
     * Kirim notifikasi saat user upload lampiran.
     */
    public static function sendLampiranDikirim($user, $tugas): void
    {
        // Kirim ke ADMIN
        $admins = User::whereHas('peran.fitur', function ($q) {
            $q->where('nama_fitur', 'lihat_semua_tugas');
        })->get();

        $title = 'Lampiran Baru Dikirim';
        $message = 'User ' . $tugas->user->nama . ' mengunggah hasil tugas "' . $tugas->nama_tugas . '".';

        foreach ($admins as $admin) {
            if ($admin->device_token) {
                Log::info('📤 Kirim Notifikasi Lampiran ke Admin', [
                    'kepada' => $admin->nama . ' (ID: ' . $admin->id . ')',
                    'dari_user' => $user->nama,
                ]);

                app(FirebaseService::class)->sendMessage($admin->device_token, $title, $message, [
                    'tipe' => 'tugas_lampiran',
                    'tugas_id' => (string) $tugas->id,
                    'target_user_id' => (string) $admin->id, // ✅ Admin sebagai target
                    'judul' => $tugas->nama_tugas,
                ]);
            }
        }

        // Kirim ke USER
        $selfTitle = 'Tugas Dikirim';
        $selfMessage = 'Kamu telah mengirim hasil tugas "' . $tugas->nama_tugas . '". Menunggu verifikasi admin.';

        if ($user->device_token) {
            Log::info('📤 Kirim Notifikasi Lampiran Terkirim ke User', [
                'kepada' => $user->nama . ' (ID: ' . $user->id . ')',
            ]);

            app(FirebaseService::class)->sendMessage($user->device_token, $selfTitle, $selfMessage, [
                'tipe' => 'tugas_lampiran_dikirim',
                'tugas_id' => (string) $tugas->id,
                'target_user_id' => (string) $user->id, // ✅ Validasi
                'judul' => $tugas->nama_tugas,
                'hapus_progress' => 'true',
            ]);
        }
    }

    /**
     * Kirim notifikasi saat tugas dihapus.
     */
    public static function sendTugasDihapus($user, $tugas): void
    {
        if ($tugas->status === 'Selesai') return;

        $title = 'Tugas Dihapus';
        $message = 'Tugas "' . $tugas->nama_tugas . '" telah dihapus oleh admin.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'tugas_hapus',
                'tugas_id' => (string) $tugas->id,
                'target_user_id' => (string) $user->id, // ✅ Validasi
                'judul' => $tugas->nama_tugas,
                'hapus_progress' => 'true',
            ]);
        }
    }

    /**
     * Kirim notifikasi saat tugas selesai.
     */
    public static function sendTugasSelesai($user, $tugas): void
    {
        $title = '✅ Tugas Selesai';
        $message = 'Kerja bagus! Tugas "' . $tugas->nama_tugas . '" telah disetujui dan statusnya diubah menjadi Selesai.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'tugas_selesai',
                'tugas_id' => (string) $tugas->id,
                'target_user_id' => (string) $user->id, // ✅ Validasi
                'judul' => $tugas->nama_tugas,
                'hapus_progress' => 'true',
            ]);
        }
    }

    /**
     * Kirim notifikasi saat tugas diproses.
     */
    public static function sendTugasDiproses($user, $tugas): void
    {
        $title = 'Tugas Dalam Proses';
        $message = 'Status tugas yang Anda telah upload lampiran diubah menjadi Proses. Tolong hubungi admin untuk menanyakan kejelasan.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'tugas_update_proses',
                'tugas_id' => (string) $tugas->id,
                'target_user_id' => (string) $user->id, // ✅ Validasi
                'judul' => $tugas->nama_tugas,
            ]);
        }
    }

    private static function logAndSend($user, string $title, string $message, string $type, array $data = []): void
    {
        if (!$user->device_token) return;
        $data['tipe'] = $type;
        app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, $data);
    }

    // ==========================================
    // NOTIFIKASI CUTI
    // ==========================================

    /**
     * Kirim notifikasi saat cuti diajukan (ke pemohon).
     */
    public static function sendCutiDiajukan($user, $cuti): void
    {
        $title = '📝 Pengajuan Cuti Diterima';
        $message = 'Pengajuan cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' berhasil dikirim.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'cuti_diajukan',
                'cuti_id' => (string) $cuti->id,
                'tanggal_mulai' => $cuti->tanggal_mulai,
                'tanggal_selesai' => $cuti->tanggal_selesai,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat cuti disetujui tahap 1.
     */
    public static function sendCutiDisetujuiStep1($user, $cuti): void
    {
        $title = '✅ Cuti Disetujui Tahap Awal';
        $message = 'Cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' disetujui tahap awal.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'cuti_step1',
                'cuti_id' => (string) $cuti->id,
                'tanggal_mulai' => $cuti->tanggal_mulai,
                'tanggal_selesai' => $cuti->tanggal_selesai,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat cuti disetujui final (step 2).
     */
    public static function sendCutiDisetujuiFinal($user, $cuti): void
    {
        $title = '🎉 Cuti Disetujui Final';
        $message = 'Selamat! Cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' telah disetujui.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'cuti_disetujui',
                'cuti_id' => (string) $cuti->id,
                'tanggal_mulai' => $cuti->tanggal_mulai,
                'tanggal_selesai' => $cuti->tanggal_selesai,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat cuti ditolak.
     */
    public static function sendCutiDitolak($user, $cuti): void
    {
        $title = '❌ Cuti Ditolak';
        $message = 'Cuti Anda tanggal ' . $cuti->tanggal_mulai . ' s/d ' . $cuti->tanggal_selesai . ' ditolak. Catatan: ' . ($cuti->catatan_penolakan ?? '-');

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'cuti_ditolak',
                'cuti_id' => (string) $cuti->id,
                'catatan_penolakan' => $cuti->catatan_penolakan ?? '-',
            ]);
        }
    }

    // ==========================================
    // NOTIFIKASI LEMBUR
    // ==========================================

    /**
     * Kirim notifikasi saat lembur diajukan (ke pemohon).
     */
    public static function sendLemburDiajukan($user, $lembur): void
    {
        $title = '📝 Pengajuan Lembur Diterima';
        $message = 'Pengajuan lembur Anda tanggal ' . $lembur->tanggal . ' berhasil dikirim.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'lembur_diajukan',
                'lembur_id' => (string) $lembur->id,
                'tanggal' => $lembur->tanggal,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat lembur disetujui tahap 1.
     */
    public static function sendLemburDisetujuiStep1($user, $lembur): void
    {
        $title = '✅ Lembur Disetujui Tahap Awal';
        $message = 'Lembur Anda tanggal ' . $lembur->tanggal . ' disetujui tahap awal.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'lembur_step1',
                'lembur_id' => (string) $lembur->id,
                'tanggal' => $lembur->tanggal,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat lembur disetujui final (step 2).
     */
    public static function sendLemburDisetujuiFinal($user, $lembur): void
    {
        $title = '🎉 Lembur Disetujui Final';
        $message = 'Selamat! Lembur Anda tanggal ' . $lembur->tanggal . ' telah disetujui.';

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'lembur_disetujui',
                'lembur_id' => (string) $lembur->id,
                'tanggal' => $lembur->tanggal,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat lembur ditolak.
     */
    public static function sendLemburDitolak($user, $lembur): void
    {
        $title = '❌ Lembur Ditolak';
        $message = 'Lembur Anda tanggal ' . $lembur->tanggal . ' ditolak. Catatan: ' . ($lembur->catatan_penolakan ?? '-');

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'lembur_ditolak',
                'lembur_id' => (string) $lembur->id,
                'catatan_penolakan' => $lembur->catatan_penolakan ?? '-',
            ]);
        }
    }
}
