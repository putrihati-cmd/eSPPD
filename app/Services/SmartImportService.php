<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

/**
 * Service to integrate with Python Smart Import Service
 */
class SmartImportService
{
    private string $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = config('services.python_import.url', 'http://localhost:8002');
    }
    
    /**
     * Upload file for analysis
     */
    public function uploadFile(UploadedFile $file): array
    {
        try {
            $response = Http::timeout(60)
                ->attach('file', $file->get(), $file->getClientOriginalName())
                ->post("{$this->baseUrl}/api/import/upload");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error("Smart Import upload failed", ['response' => $response->body()]);
            return ['error' => 'Upload failed: ' . $response->body()];
            
        } catch (\Exception $e) {
            Log::error("Smart Import upload error", ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Update column mapping
     */
    public function updateMapping(string $jobId, array $mapping): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/api/import/mapping", [
                    'job_id' => $jobId,
                    'mapping' => $mapping
                ]);
            
            return $response->json();
            
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Validate data
     */
    public function validateData(string $jobId): array
    {
        try {
            $response = Http::timeout(120)
                ->post("{$this->baseUrl}/api/import/validate/{$jobId}");
            
            return $response->json();
            
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Process import
     */
    public function processImport(string $jobId, bool $skipErrors = false): array
    {
        try {
            $response = Http::timeout(300)
                ->post("{$this->baseUrl}/api/import/process", [
                    'job_id' => $jobId,
                    'skip_errors' => $skipErrors
                ]);
            
            return $response->json();
            
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Rollback import
     */
    public function rollback(string $jobId): array
    {
        try {
            $response = Http::timeout(60)
                ->post("{$this->baseUrl}/api/import/rollback/{$jobId}");
            
            return $response->json();
            
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Get job status
     */
    public function getStatus(string $jobId): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/api/import/status/{$jobId}");
            
            return $response->json();
            
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Check service health
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/health");
            
            return $response->successful() && 
                   ($response->json()['status'] ?? '') === 'healthy';
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
