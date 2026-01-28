<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PythonDocumentService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.python_document.url', 'http://localhost:8001');
        $this->timeout = config('services.python_document.timeout', 30);
    }

    /**
     * Check if Python service is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Python document service not available: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate SPPD document
     */
    public function generateSPPD(array $data): ?string
    {
        return $this->generateDocument('/generate-sppd', $data, 'sppd');
    }

    /**
     * Generate Surat Tugas document
     */
    public function generateSuratTugas(array $data): ?string
    {
        return $this->generateDocument('/generate-surat-tugas', $data, 'surat_tugas');
    }

    /**
     * Generate Laporan document
     */
    public function generateLaporan(array $data): ?string
    {
        return $this->generateDocument('/generate-laporan', $data, 'laporan');
    }

    /**
     * Internal method to generate document via Python service
     */
    protected function generateDocument(string $endpoint, array $data, string $type): ?string
    {
        try {
            // Check service availability
            if (!$this->isAvailable()) {
                Log::warning("Python service unavailable, using fallback for {$type}");
                return $this->fallbackGenerate($type, $data);
            }

            // Send request to Python service
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}{$endpoint}", $data);

            if (!$response->successful()) {
                Log::error("Python service error: " . $response->body());
                return $this->fallbackGenerate($type, $data);
            }

            $result = $response->json();

            if (!$result['success']) {
                Log::error("Document generation failed: " . ($result['message'] ?? 'Unknown error'));
                return null;
            }

            // Download the generated file
            $filename = $result['filename'];
            $downloadUrl = "{$this->baseUrl}/download/{$filename}";

            $fileResponse = Http::timeout($this->timeout)->get($downloadUrl);

            if (!$fileResponse->successful()) {
                Log::error("Failed to download generated document");
                return null;
            }

            // Save to Laravel storage
            $storagePath = "documents/{$type}/{$filename}";
            Storage::put($storagePath, $fileResponse->body());

            Log::info("Document generated successfully: {$storagePath}");

            return $storagePath;

        } catch (\Exception $e) {
            Log::error("Error generating {$type} document: " . $e->getMessage());
            return $this->fallbackGenerate($type, $data);
        }
    }

    /**
     * Fallback to PHPWord if Python service is unavailable
     */
    protected function fallbackGenerate(string $type, array $data): ?string
    {
        Log::info("Using PHPWord fallback for {$type}");

        // This would call the existing PHPWord-based generation
        // For now, return null to indicate fallback not implemented
        // You can integrate with existing TripReportPdfController or similar

        return null;
    }

    /**
     * Format data for SPPD generation
     */
    public static function formatSppdData($spd): array
    {
        $employee = $spd->employee;

        return [
            'nomor_sppd' => $spd->spd_number,
            'employee' => [
                'nama' => $employee->name ?? '',
                'nip' => $employee->nip ?? '',
                'jabatan' => $employee->position ?? '',
                'pangkat' => $employee->rank ?? '',
                'golongan' => $employee->grade ?? '',
                'unit_kerja' => $employee->unit->name ?? '',
            ],
            'tujuan' => $spd->destination,
            'keperluan' => $spd->purpose,
            'tanggal_berangkat' => $spd->departure_date?->format('d F Y'),
            'tanggal_kembali' => $spd->return_date?->format('d F Y'),
            'lama_perjalanan' => $spd->departure_date?->diffInDays($spd->return_date) + 1,
            'sumber_dana' => $spd->budget?->name ?? '',
            'pejabat_penandatangan' => config('esppd.signing_officer.name', ''),
            'jabatan_penandatangan' => config('esppd.signing_officer.position', ''),
            'nip_penandatangan' => config('esppd.signing_officer.nip', ''),
        ];
    }

    /**
     * Format data for Laporan generation
     */
    public static function formatLaporanData($tripReport): array
    {
        $spd = $tripReport->spd;
        $employee = $spd->employee;

        return [
            'nomor_laporan' => '',
            'employee' => [
                'nama' => $employee->name ?? '',
                'nip' => $employee->nip ?? '',
                'jabatan' => $employee->position ?? '',
                'pangkat' => $employee->rank ?? '',
                'unit_kerja' => $employee->unit->name ?? '',
            ],
            'nomor_sppd' => $spd->spd_number,
            'tujuan' => $spd->destination,
            'tanggal_berangkat' => $spd->departure_date?->format('d F Y'),
            'tanggal_kembali' => $spd->return_date?->format('d F Y'),
            'kegiatan' => $tripReport->activities ?? '',
            'hasil' => $tripReport->outputs ?? '',
            'kesimpulan' => '',
            'saran' => '',
        ];
    }
}
