<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use App\Models\Spd;

class GenerateDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public int $spdId,
        public string $documentType // 'spt' or 'spd'
    ) {}

    public function handle(): void
    {
        $spd = Spd::with(['employee', 'costs', 'unit'])->find($this->spdId);

        if (!$spd) {
            Log::error("GenerateDocumentJob: SPD not found", ['spd_id' => $this->spdId]);
            return;
        }

        $serviceUrl = config('services.document.url', 'http://localhost:8001');

        try {
            /** @var Response $response */
            $response = Http::timeout(60)->post("{$serviceUrl}/generate/{$this->documentType}", [
                'spd_id' => $spd->id,
                'employee_name' => $spd->employee->name ?? 'N/A',
                'employee_nip' => $spd->employee->nip ?? 'N/A',
                'destination' => $spd->destination,
                'purpose' => $spd->purpose,
                'departure_date' => $spd->departure_date->format('Y-m-d'),
                'return_date' => $spd->return_date->format('Y-m-d'),
                'costs' => $spd->costs->map(fn($c) => [
                    'category' => $c->category,
                    'amount' => $c->amount,
                ])->toArray(),
            ]);

            if ($response->successful()) {
                $spd->update([
                    "{$this->documentType}_generated_at" => now(),
                    "{$this->documentType}_file_path" => $response->json('file_path'),
                ]);
                Log::info("Document generated successfully", [
                    'spd_id' => $this->spdId,
                    'type' => $this->documentType,
                ]);
            } else {
                Log::error("Document service error", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->fail(new \Exception("Document service returned: " . $response->status()));
            }
        } catch (\Exception $e) {
            Log::error("GenerateDocumentJob failed", [
                'spd_id' => $this->spdId,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw to trigger retry
        }
    }
}

