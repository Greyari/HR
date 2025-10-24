<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Notification;
use App\Services\FirebaseService;
use Carbon\Carbon;

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

        foreach ($users as $user) {
            if ($user->device_token) {
                $fcm->sendMessage($user->device_token, $title, $message, [
                    'tipe' => $type,
                ]);
            }
        }

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
        self::createLog($user->id, $title, $message, 'tugas_baru');

        if (!$user->device_token) return;

        app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
            'tipe' => 'tugas_baru',
            'judul' => $tugas->nama_tugas,
            'tugas_id' => (string) $tugas->id,
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

        self::createLog($user->id, $title, $message, 'tugas_update');

        self::logAndSend($user, $title, $message, 'tugas_update', [
            'tugas_id' => (string) $tugas->id,
            'status' => $tugas->status,
            'judul' => $tugas->nama_tugas,
            'batas_penugasan' => Carbon::parse($tugas->batas_penugasan)->toIso8601String(),
        ]);
    }

    /**
     * Utility internal: log notifikasi ke database dan kirim ke FCM.
     */
    private static function logAndSend($user, string $title, string $message, string $type, array $data = []): void
    {
        if (!$user->device_token) return;

        $data['tipe'] = $type;

        app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, $data);
    }

    /**
     * Kirim notifikasi saat tugas dialihkan ke user lain.
     * ✅ FIXED: Mengirim data FCM lengkap dengan hapus_progress
     */
    public static function sendTugasDialihkan($userLama, $tugas): void
    {
        $title = 'Tugas Dipindahkan';
        $message = 'Tugas "' . $tugas->nama_tugas . '" telah dipindahkan ke user lain.';

        self::createLog($userLama->id, $title, $message, 'tugas_pindah');

        if ($userLama->device_token) {
            // ✅ PERBAIKAN: Kirim dengan data lengkap termasuk hapus_progress
            app(FirebaseService::class)->sendMessage(
                $userLama->device_token,
                $title,
                $message,
                [
                    'tipe' => 'tugas_pindah',
                    'tugas_id' => (string) $tugas->id,
                    'judul' => $tugas->nama_tugas,
                ]
            );
        }
    }

    /**
     * Kirim notifikasi saat user upload lampiran.
     * ✅ FIXED: Parameter salah - seharusnya kirim ke admin, bukan user
     */
    public static function sendLampiranDikirim($user, $tugas): void
    {
        // 🔹 Kirim ke ADMIN yang punya fitur lihat_semua_tugas
        $admins = User::whereHas('peran.fitur', function ($q) {
            $q->where('nama_fitur', 'lihat_semua_tugas');
        })->get();

        $title = 'Lampiran Baru Dikirim';
        $message = 'User ' . $tugas->user->name . ' mengunggah hasil tugas "' . $tugas->nama_tugas . '".';

        foreach ($admins as $admin) {
            self::createLog($admin->id, $title, $message, 'tugas_lampiran');

            if ($admin->device_token) {
                app(FirebaseService::class)->sendMessage($admin->device_token, $title, $message, [
                    'tipe' => 'tugas_lampiran',
                    'tugas_id' => (string) $tugas->id,
                    'judul' => $tugas->nama_tugas,
                ]);
            }
        }

        // 🔹 Kirim juga ke USER bahwa tugasnya berhasil dikirim
        $selfTitle = 'Tugas Dikirim';
        $selfMessage = 'Kamu telah mengirim hasil tugas "' . $tugas->nama_tugas . '". Menunggu verifikasi admin.';
        self::createLog($user->id, $selfTitle, $selfMessage, 'tugas_lampiran_dikirim');

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $selfTitle, $selfMessage, [
                'tipe' => 'tugas_lampiran_dikirim',
                'tugas_id' => (string) $tugas->id,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat tugas dihapus oleh admin.
     * ✅ FIXED: Mengirim data FCM lengkap dengan hapus_progress
     */
    public static function sendTugasDihapus($user, $tugas): void
    {
        if ($tugas->status === 'Selesai') return;

        $title = 'Tugas Dihapus';
        $message = 'Tugas "' . $tugas->nama_tugas . '" telah dihapus oleh admin.';

        self::createLog($user->id, $title, $message, 'tugas_hapus');

        if ($user->device_token) {
            // ✅ PERBAIKAN: Kirim dengan data lengkap
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'tugas_hapus',
                'tugas_id' => (string) $tugas->id,
                'judul' => $tugas->nama_tugas,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat tugas diubah statusnya menjadi Selesai.
     */
    public static function sendTugasSelesai($user, $tugas): void
    {
        $title = '✅ Tugas Selesai';
        $message = 'Kerja bagus! Tugas "' . $tugas->nama_tugas . '" telah disetujui dan statusnya diubah menjadi Selesai.';

        self::createLog($user->id, $title, $message, 'tugas_selesai');

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'tugas_selesai',
                'tugas_id' => (string) $tugas->id,
                'judul' => $tugas->nama_tugas,
            ]);
        }
    }

    // /**
    //  * Kirim notifikasi saat tugas ditolak oleh admin.
    //  */
    // public static function sendTugasDitolak($user, $tugas): void
    // {
    //     $title = '❌ Tugas Ditolak';
    //     $message = 'Tugas "' . $tugas->nama_tugas . '" ditolak. Silakan perbaiki dan upload ulang.';

    //     self::createLog($user->id, $title, $message, 'tugas_ditolak');

    //     if ($user->device_token) {
    //         app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
    //             'tipe' => 'tugas_ditolak',
    //             'tugas_id' => (string) $tugas->id,
    //             'judul' => $tugas->nama_tugas,
    //         ]);
    //     }
    // }

    /**
     * Utility internal: simpan notifikasi ke tabel `notifications`.
     */
    private static function createLog(int $userId, string $title, string $message, ?string $type = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}
