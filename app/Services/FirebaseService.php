<?php

namespace App\Services;

use Google\Client;
use Google\Service\FirebaseCloudMessaging;
use Google\Service\FirebaseCloudMessaging\SendMessageRequest;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $client;
    protected $messaging;

    public function __construct()
    {
        // Inisialisasi Google Client
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
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }
}
