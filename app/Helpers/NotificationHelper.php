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

        // 🔹 Ambil semua user yang memiliki fitur target
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

        // 🔹 Jika ini notifikasi tugas update, sertakan detail tugas
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
     */
    public static function sendTugasDialihkan($userLama, $tugas): void
    {
        $title = 'Tugas Dipindahkan';
        $message = 'Tugas "' . $tugas->nama_tugas . '" telah dipindahkan ke user lain.';

        self::createLog($userLama->id, $title, $message, 'tugas_pindah');

        if ($userLama->device_token) {
            app(FirebaseService::class)->sendMessage(
                $userLama->device_token,
                $title,
                $message,
                [
                    'tipe' => 'tugas_pindah',
                    'hapus_progress' => true,
                    'tugas_id' => (string) $tugas->id,
                    'judul' => $tugas->nama_tugas,
                ]
            );
        }
    }

    /**
     * Kirim notifikasi saat user upload lampiran (mengganti progres bar dengan notif tunggal).
     */
    public static function sendLampiranDikirim($adminUser, $tugas): void
    {
        $title = 'Lampiran Baru Dikirim';
        $message = 'User ' . $tugas->user->name . ' mengunggah hasil tugas "' . $tugas->nama_tugas . '".';

        // 🔹 Log dan kirim ke admin
        self::logAndSend($adminUser, $title, $message, 'tugas_lampiran', [
            'tugas_id' => (string) $tugas->id,
            'judul' => $tugas->nama_tugas,
        ]);

        // 🔹 Kirim juga ke user bahwa progresnya berakhir
        $selfTitle = 'Tugas Dikirim';
        $selfMessage = 'Kamu telah mengirim hasil tugas "' . $tugas->nama_tugas . '". Menunggu verifikasi admin.';
        self::createLog($tugas->user_id, $selfTitle, $selfMessage, 'tugas_lampiran_dikirim');

        if ($tugas->user->device_token) {
            app(FirebaseService::class)->sendMessage($tugas->user->device_token, $selfTitle, $selfMessage, [
                'tipe' => 'tugas_lampiran_dikirim',
                'hapus_progress' => true,
                'tugas_id' => (string) $tugas->id,
            ]);
        }
    }

    /**
     * Kirim notifikasi saat tugas dihapus oleh admin (jika belum selesai).
     */
    public static function sendTugasDihapus($user, $tugas): void
    {
        if ($tugas->status === 'Selesai') return;

        $title = 'Tugas Dihapus';
        $message = 'Tugas "' . $tugas->nama_tugas . '" telah dihapus oleh admin.';

        self::createLog($user->id, $title, $message, 'tugas_hapus');

        if ($user->device_token) {
            app(FirebaseService::class)->sendMessage($user->device_token, $title, $message, [
                'tipe' => 'tugas_hapus',
                'hapus_progress' => true,
                'tugas_id' => (string) $tugas->id,
                'judul' => $tugas->nama_tugas,
            ]);
        }
    }

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
