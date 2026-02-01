<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $spd = Spd::with(['employee', 'costs', 'unit', 'budget'])->find($this->spdId);

        if (!$spd) {
            Log::error("GenerateDocumentJob: SPD not found", ['spd_id' => $this->spdId]);
            return;
        }

        $pythonService = app(\App\Services\PythonDocumentService::class);

        try {
            if ($this->documentType === 'spt') {
                $content = $pythonService->getSptPdf($spd);
            } else {
                $content = $pythonService->getSpdPdf($spd);
            }

            if ($content) {
                $spd->update([
                    "{$this->documentType}_generated_at" => now(),
                    // Note: We don't save the full content in the DB, 
                    // usually we save to storage and keep the path.
                    // But for this "full python" implementation, 
                    // we'll assume the files are handled by the controller on-demand 
                    // or we save them locally now.
                ]);
                
                $filename = "{$this->documentType}_{$spd->id}.pdf";
                Storage::put("public/documents/{$filename}", $content);
                
                $spd->update([
                    "{$this->documentType}_file_path" => "documents/{$filename}",
                ]);

                Log::info("Document generated and saved successfully", [
                    'spd_id' => $this->spdId,
                    'type' => $this->documentType,
                ]);
            } else {
                Log::error("Document service failed to return content", [
                    'spd_id' => $this->spdId,
                    'type' => $this->documentType,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("GenerateDocumentJob failed", [
                'spd_id' => $this->spdId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

