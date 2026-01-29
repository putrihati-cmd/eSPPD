<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Document Service - Communicates with Python Document Generator
 */
class DocumentService
{
    private string $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = config('services.document_service.url', 'http://localhost:8001');
    }
    
    /**
     * Generate SPPD document
     */
    public function generateSppd(array $data): array
    {
        return $this->callEndpoint('/generate-sppd', $data);
    }
    
    /**
     * Generate Surat Tugas document
     */
    public function generateSuratTugas(array $data): array
    {
        return $this->callEndpoint('/generate-surat-tugas', $data);
    }
    
    /**
     * Generate Laporan document
     */
    public function generateLaporan(array $data): array
    {
        return $this->callEndpoint('/generate-laporan', $data);
    }
    
    /**
     * Generate LPJ (Laporan Perjalanan Dinas) - Word format
     */
    public function generateLpj(array $sppdData): array
    {
        $data = $this->prepareLpjData($sppdData);
        return $this->callEndpoint('/generate-lpj', $data);
    }
    
    /**
     * Generate LPJ (Laporan Perjalanan Dinas) - PDF format
     */
    public function generateLpjPdf(array $sppdData): array
    {
        $data = $this->prepareLpjData($sppdData);
        return $this->callEndpoint('/generate-lpj-pdf', $data);
    }
    
    /**
     * Get download URL for a document
     */
    public function getDownloadUrl(string $filename): string
    {
        return "{$this->baseUrl}/download/{$filename}";
    }
    
    /**
     * Download document as stream
     */
    public function downloadDocument(string $filename)
    {
        try {
            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/download/{$filename}");
            
            if ($response->successful()) {
                return $response->body();
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("Document download error", ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Check service health
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful() && 
                   ($response->json()['status'] ?? '') === 'healthy';
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Prepare LPJ data from SPPD model
     */
    private function prepareLpjData(array $sppd): array
    {
        return [
            'employee' => [
                'nama' => $sppd['employee']['name'] ?? $sppd['nama'] ?? '',
                'nip' => $sppd['employee']['nip'] ?? $sppd['nip'] ?? '',
                'jabatan' => $sppd['employee']['position'] ?? $sppd['jabatan'] ?? '',
                'pangkat' => $sppd['employee']['rank'] ?? $sppd['pangkat'] ?? '',
                'golongan' => $sppd['employee']['class'] ?? $sppd['golongan'] ?? '',
                'unit_kerja' => $sppd['employee']['unit'] ?? $sppd['unit_kerja'] ?? '',
            ],
            'nomor_sppd' => $sppd['sppd_number'] ?? $sppd['nomor_sppd'] ?? '',
            'nomor_surat_tugas' => $sppd['assignment_letter_number'] ?? $sppd['nomor_surat_tugas'] ?? '',
            'tujuan' => $sppd['destination'] ?? $sppd['tujuan'] ?? '',
            'keperluan' => $sppd['purpose'] ?? $sppd['keperluan'] ?? '',
            'tanggal_berangkat' => $sppd['start_date'] ?? $sppd['tanggal_berangkat'] ?? '',
            'tanggal_kembali' => $sppd['end_date'] ?? $sppd['tanggal_kembali'] ?? '',
            'lama_perjalanan' => $sppd['duration'] ?? $sppd['lama_perjalanan'] ?? 1,
            'hari' => $sppd['day'] ?? $sppd['hari'] ?? '',
            'undangan' => $sppd['invitation'] ?? $sppd['undangan'] ?? '-',
            'kegiatan' => $sppd['report_content'] ?? $sppd['kegiatan'] ?? '',
            'outputs' => $sppd['outputs'] ?? [],
            'tempat_lapor' => $sppd['report_place'] ?? 'Purwokerto',
            'tanggal_lapor' => $sppd['report_date'] ?? now()->format('d F Y'),
            'atasan_nama' => $sppd['superior']['name'] ?? $sppd['atasan_nama'] ?? '',
            'atasan_nip' => $sppd['superior']['nip'] ?? $sppd['atasan_nip'] ?? '',
        ];
    }
    
    /**
     * Call Python service endpoint
     */
    private function callEndpoint(string $endpoint, array $data): array
    {
        try {
            $response = Http::timeout(60)
                ->post("{$this->baseUrl}{$endpoint}", $data);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error("Document service error", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            return [
                'success' => false,
                'message' => 'Document generation failed: ' . $response->body()
            ];
            
        } catch (\Exception $e) {
            Log::error("Document service exception", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
