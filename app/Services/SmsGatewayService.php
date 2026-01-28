<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SmsGatewayService
{
    protected Client $client;
    protected string $apiKey;
    protected string $senderId;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);
        $this->apiKey = config('services.sms.api_key', '');
        $this->senderId = config('services.sms.sender_id', 'eSPPD');
    }

    /**
     * Send SMS notification
     */
    public function send(string $phoneNumber, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('SMS Gateway not configured');
            return false;
        }

        try {
            // Format phone number
            $phone = $this->formatPhoneNumber($phoneNumber);

            // Generic SMS Gateway API call (adjust for your provider)
            $response = $this->client->post(config('services.sms.endpoint', 'https://api.sms-gateway.example.com/send'), [
                'json' => [
                    'api_key' => $this->apiKey,
                    'sender_id' => $this->senderId,
                    'to' => $phone,
                    'message' => $message,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($result['status'] ?? false) {
                Log::info('SMS sent successfully', ['phone' => $phone]);
                return true;
            }

            Log::error('SMS send failed', ['phone' => $phone, 'error' => $result['message'] ?? 'Unknown']);
            return false;

        } catch (\Exception $e) {
            Log::error('SMS Gateway error', ['phone' => $phoneNumber, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send urgent notification
     */
    public function sendUrgent(string $phoneNumber, string $title, string $message): bool
    {
        $fullMessage = "[URGENT] $title\n$message\n\n- e-SPPD System";
        return $this->send($phoneNumber, $fullMessage);
    }

    /**
     * Send approval reminder
     */
    public function sendApprovalReminder(string $phoneNumber, string $spdNumber, string $employeeName): bool
    {
        $message = "SPPD {$spdNumber} dari {$employeeName} menunggu persetujuan Anda. Mohon segera diproses.";
        return $this->send($phoneNumber, $message);
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
