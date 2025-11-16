<?php

namespace App\Services;

use Google\Client;
use Google\Service\FirebaseCloudMessaging;
use Google\Service\FirebaseCloudMessaging\SendMessageRequest;
use Illuminate\Support\Facades\Log;
use Google\Service\Exception as GoogleException;

class FirebaseService
{
    protected $client;
    protected $messaging;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(config('firebase.projects.app.credentials'));
        $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $this->messaging = new FirebaseCloudMessaging($this->client);
    }

    public function sendMessage($deviceToken, $title, $body, $data = [])
    {
        $projectId = config('services.firebase.project_id');

        $message = new SendMessageRequest([
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ],
        ]);

        try {
            $response = $this->messaging->projects_messages->send("projects/{$projectId}", $message);
            return $response;

        } catch (GoogleException $e) {
            $errorBody = json_decode($e->getMessage(), true);

            // ✅ Cek apakah error UNREGISTERED atau NOT_FOUND
            if (isset($errorBody['error']['status']) &&
                in_array($errorBody['error']['status'], ['NOT_FOUND', 'UNREGISTERED'])) {

                Log::error("🔴 FCM Token UNREGISTERED/NOT_FOUND: " . substr($deviceToken, 0, 30) . "...");

                // ✅ Hapus token dari database
                \App\Models\User::where('device_token', $deviceToken)->update(['device_token' => null]);

                return false;
            }

            // ✅ Cek error code 404
            if ($e->getCode() === 404) {
                Log::error("🔴 FCM Token NOT_FOUND (404): " . substr($deviceToken, 0, 30) . "...");

                // ✅ Hapus token dari database
                \App\Models\User::where('device_token', $deviceToken)->update(['device_token' => null]);

                return false;
            }

            // Error lain
            Log::error('FCM Error: ' . $e->getMessage());
            return false;

        } catch (\Exception $e) {
            Log::error('FCM General Error: ' . $e->getMessage());
            return false;
        }
    }
}
