<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    protected string $serverKey;
    protected string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key', '');
    }

    /**
     * Send push notification to device
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        if (empty($this->serverKey)) {
            Log::warning('Firebase server key not configured');
            return false;
        }

        return $this->send([
            'to' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => 1,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Send push notification to topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (empty($this->serverKey)) {
            Log::warning('Firebase server key not configured');
            return false;
        }

        return $this->send([
            'to' => "/topics/{$topic}",
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ]);
    }

    /**
     * Send push notification to multiple devices
     */
    public function sendToDevices(array $deviceTokens, string $title, string $body, array $data = []): bool
    {
        if (empty($this->serverKey) || empty($deviceTokens)) {
            return false;
        }

        return $this->send([
            'registration_ids' => $deviceTokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ]);
    }

    /**
     * Send SPPD status notification
     */
    public function notifySppdStatus(string $deviceToken, string $spdNumber, string $status, string $message): bool
    {
        $titles = [
            'submitted' => 'SPPD Diajukan',
            'approved' => 'SPPD Disetujui',
            'rejected' => 'SPPD Ditolak',
            'completed' => 'SPPD Selesai',
        ];

        return $this->sendToDevice(
            $deviceToken,
            $titles[$status] ?? 'Update SPPD',
            $message,
            [
                'type' => 'sppd_status',
                'spd_number' => $spdNumber,
                'status' => $status,
            ]
        );
    }

    /**
     * Send approval request notification
     */
    public function notifyApprovalRequest(string $deviceToken, string $spdNumber, string $employeeName): bool
    {
        return $this->sendToDevice(
            $deviceToken,
            'Persetujuan SPPD',
            "SPPD dari {$employeeName} menunggu persetujuan Anda",
            [
                'type' => 'approval_request',
                'spd_number' => $spdNumber,
            ]
        );
    }

    /**
     * Send notification via FCM
     */
    protected function send(array $payload): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            $result = $response->json();

            if ($response->successful() && ($result['success'] ?? 0) > 0) {
                Log::info('Firebase push sent', ['success' => $result['success']]);
                return true;
            }

            Log::error('Firebase push failed', ['response' => $result]);
            return false;

        } catch (\Exception $e) {
            Log::error('Firebase push error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Subscribe device to topic
     */
    public function subscribeToTopic(string $deviceToken, string $topic): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
            ])->post("https://iid.googleapis.com/iid/v1/{$deviceToken}/rel/topics/{$topic}");

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Firebase subscribe error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
