<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    /**
     * List all registered webhooks
     */
    public function index(Request $request): JsonResponse
    {
        $webhooks = \App\Models\Webhook::where('user_id', $request->user()->id)->get();

        return response()->json([
            'success' => true,
            'data' => $webhooks,
        ]);
    }

    /**
     * Register a new webhook
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'events' => 'required|array',
            'events.*' => 'in:sppd.created,sppd.submitted,sppd.approved,sppd.rejected,sppd.completed',
            'secret' => 'nullable|string|min:16',
        ]);

        $webhook = \App\Models\Webhook::create([
            'user_id' => $request->user()->id,
            'url' => $validated['url'],
            'events' => $validated['events'],
            'secret' => $validated['secret'] ?? Str::random(32),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook registered successfully',
            'data' => $webhook,
        ], 201);
    }

    /**
     * Update webhook
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $webhook = \App\Models\Webhook::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'url' => 'sometimes|url',
            'events' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $webhook->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Webhook updated',
            'data' => $webhook,
        ]);
    }

    /**
     * Delete webhook
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $webhook = \App\Models\Webhook::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $webhook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted',
        ]);
    }

    /**
     * Test webhook
     */
    public function test(Request $request, string $id): JsonResponse
    {
        $webhook = \App\Models\Webhook::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $payload = [
            'event' => 'test',
            'data' => [
                'message' => 'This is a test webhook from e-SPPD',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $result = $this->sendWebhook($webhook, $payload);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Webhook test sent successfully' : 'Webhook test failed',
            'response_code' => $result['code'] ?? null,
        ]);
    }

    /**
     * Send webhook to URL
     */
    public static function sendWebhook($webhook, array $payload): array
    {
        try {
            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $payload['event'] ?? 'unknown',
                ])
                ->post($webhook->url, $payload);

            // Log webhook call
            \App\Models\WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $payload['event'] ?? 'unknown',
                'payload' => $payload,
                'response_code' => $response->status(),
                'response_body' => $response->body(),
                'success' => $response->successful(),
            ]);

            return [
                'success' => $response->successful(),
                'code' => $response->status(),
            ];

        } catch (\Exception $e) {
            \App\Models\WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $payload['event'] ?? 'unknown',
                'payload' => $payload,
                'response_code' => 0,
                'response_body' => $e->getMessage(),
                'success' => false,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Dispatch webhook for SPPD event
     */
    public static function dispatch(Spd $spd, string $event): void
    {
        $webhooks = \App\Models\Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        $payload = [
            'event' => $event,
            'data' => [
                'spd_id' => $spd->id,
                'spd_number' => $spd->spd_number,
                'employee_name' => $spd->employee?->name,
                'destination' => $spd->destination,
                'status' => $spd->status,
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        foreach ($webhooks as $webhook) {
            // Dispatch to queue for async processing
            \App\Jobs\SendWebhookJob::dispatch($webhook, $payload);
        }
    }
}
