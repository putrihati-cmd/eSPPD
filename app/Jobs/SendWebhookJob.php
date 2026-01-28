<?php

namespace App\Jobs;

use App\Http\Controllers\Api\WebhookController;
use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Webhook $webhook;
    protected array $payload;

    public int $tries = 3;
    public array $backoff = [10, 60, 300]; // 10s, 1m, 5m

    public function __construct(Webhook $webhook, array $payload)
    {
        $this->webhook = $webhook;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        WebhookController::sendWebhook($this->webhook, $this->payload);
    }

    public function failed(\Throwable $exception): void
    {
        // Log the final failure
        \Illuminate\Support\Facades\Log::error('Webhook delivery failed permanently', [
            'webhook_id' => $this->webhook->id,
            'url' => $this->webhook->url,
            'event' => $this->payload['event'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
