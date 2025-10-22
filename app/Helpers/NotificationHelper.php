<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Notification;
use App\Services\FirebaseService;

class NotificationHelper
{
    /**
     * Kirim notifikasi ke semua user yang memiliki fitur tertentu.
     */
    public static function sendToFitur($namaFitur, $title, $message, $type = null)
    {
        $fcm = app(FirebaseService::class);

        // Ambil semua user dengan fitur target
        $users = User::whereHas('peran.fitur', function ($q) use ($namaFitur) {
            $q->where('nama_fitur', $namaFitur);
        })->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
            ]);

            if ($user->device_token) {
                $fcm->sendMessage($user->device_token, $title, $message, ['type' => $type]);
            }
        }

        return count($users);
    }

    /**
     * Kirim notifikasi langsung ke user tertentu.
     */
    public static function sendToUser($user, $title, $message, $type = null)
    {
        $fcm = app(FirebaseService::class);

        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);

        if ($user->device_token) {
            $fcm->sendMessage($user->device_token, $title, $message, ['type' => $type]);
        }
    }
}
