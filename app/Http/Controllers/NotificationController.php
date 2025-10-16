<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\FcmToken;
use App\Models\User;

class NotificationController extends Controller
{
    // Kirim notif ke satu token

    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        // ambil token dari tabel fcm_tokens
        $user = User::with('fcmToken')->find($request->user_id);
        if (!$user || !$user->fcmToken) {
            return response()->json(['error' => 'User atau token tidak ditemukan'], 404);
        }

        $token = $user->fcmToken->token;

        $messaging = app('firebase.messaging');

        $message = CloudMessage::fromArray([
            'token' => $token,
            'data' => [
                'title' => $request->title,
                'body' => $request->body,
                'icon' => '/assets/business_center.png', // optional
            ],

        ]);


        try {
            $messaging->send($message);
            return response()->json(['success' => true, 'sent_to' => $token]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Simpan/update token FCM dari Flutter
    public function saveToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'token' => 'required|string',
        ]);
        try {
            FcmToken::updateOrCreate(
                ['user_id' => $request->user_id],
                ['token' => $request->token]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $deleted = \App\Models\FcmToken::where('user_id', $request->user_id)->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? 'Token berhasil dihapus' : 'Token tidak ditemukan',
        ]);
    }
}
