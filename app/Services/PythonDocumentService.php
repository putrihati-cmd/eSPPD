<?php

namespace App\Services;

use App\Models\Spd;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonDocumentService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.document.url', 'http://localhost:8001');
    }

    /**
     * Fetch SPT PDF from Python Service
     */
    public function getSptPdf(Spd $spd): ?string
    {
        $data = [
            'nomor_surat' => $spd->spt_number,
            'employee' => [
                'nama' => $spd->employee->name,
                'nip' => $spd->employee->nip,
                'jabatan' => $spd->employee->position ?? '',
                'pangkat' => $spd->employee->rank ?? '',
                'golongan' => $spd->employee->grade ?? '',
                'unit_kerja' => $spd->unit->name ?? '',
            ],
            'perihal' => $spd->purpose,
            'tujuan' => $spd->destination,
            'tanggal_mulai' => $spd->departure_date->format('d F Y'),
            'tanggal_selesai' => $spd->return_date->format('d F Y'),
            'pejabat_penandatangan' => 'Dr. Rektor UIN', // Should be dynamic
            'jabatan_penandatangan' => 'Rektor',
            'nip_penandatangan' => '195301011988031006',
            'tanggal_surat' => $spd->approved_at ? $spd->approved_at->format('d F Y') : now()->format('d F Y'),
        ];

        return $this->generateAndDownload('/generate-surat-tugas-pdf', $data);
    }

    /**
     * Fetch SPD PDF from Python Service
     */
    public function getSpdPdf(Spd $spd): ?string
    {
        $data = [
            'nomor_sppd' => $spd->spd_number,
            'employee' => [
                'nama' => $spd->employee->name,
                'nip' => $spd->employee->nip,
                'jabatan' => $spd->employee->position ?? '',
                'pangkat' => $spd->employee->rank ?? '',
                'golongan' => $spd->employee->grade ?? '',
                'unit_kerja' => $spd->unit->name ?? '',
            ],
            'tujuan' => $spd->destination,
            'keperluan' => $spd->purpose,
            'tanggal_berangkat' => $spd->departure_date->format('d F Y'),
            'tanggal_kembali' => $spd->return_date->format('d F Y'),
            'lama_perjalanan' => $spd->duration,
            'sumber_dana' => $spd->budget->name ?? '',
            'pejabat_penandatangan' => 'Dr. Rektor UIN', // Should be dynamic
            'jabatan_penandatangan' => 'Rektor',
            'nip_penandatangan' => '195301011988031006',
        ];

        return $this->generateAndDownload('/generate-sppd-pdf', $data);
    }

    /**
     * Fetch Trip Report PDF from Python Service
     */
    public function getTripReportPdf($report): ?string
    {
        $data = [
            'nomor_laporan' => $report->id,
            'employee' => [
                'nama' => $report->employee->name,
                'nip' => $report->employee->nip,
                'jabatan' => $report->employee->position ?? '',
                'pangkat' => $report->employee->rank ?? '',
                'unit_kerja' => $report->employee->unit->name ?? '',
            ],
            'nomor_sppd' => $report->spd->spd_number,
            'tujuan' => $report->spd->destination,
            'tanggal_berangkat' => $report->spd->departure_date->format('d F Y'),
            'tanggal_kembali' => $report->spd->return_date->format('d F Y'),
            'kegiatan' => $report->activities->pluck('description')->implode("\n"),
            'hasil' => $report->outputs->pluck('description')->implode("\n"),
            'kesimpulan' => $report->verification_notes ?? '',
            'saran' => '',
            'tanggal_laporan' => $report->submitted_at ? $report->submitted_at->format('d F Y') : now()->format('d F Y'),
        ];

        return $this->generateAndDownload('/generate-laporan-pdf', $data);
    }

    /**
     * Fetch LPJ PDF (Formatted Table) from Python Service
     */
    public function getLpjPdf($report): ?string
    {
        $spd = $report->spd;
        $data = [
            'employee' => [
                'nama' => $report->employee->name,
                'nip' => $report->employee->nip,
                'jabatan' => $report->employee->position ?? '',
                'pangkat' => $report->employee->rank ?? '',
                'golongan' => $report->employee->grade ?? '',
                'unit_kerja' => $report->employee->unit->name ?? '',
            ],
            'nomor_sppd' => $spd->spd_number,
            'nomor_surat_tugas' => $spd->spt_number,
            'tujuan' => $spd->destination,
            'keperluan' => $spd->purpose,
            'tanggal_berangkat' => $spd->departure_date->format('d F Y'),
            'tanggal_kembali' => $spd->return_date->format('d F Y'),
            'lama_perjalanan' => $spd->duration,
            'hari' => $spd->departure_date->isoFormat('dddd'),
            'undangan' => $spd->invitation_number ?? '-',
            'kegiatan' => $report->activities->pluck('description')->implode("\n"),
            'outputs' => $report->outputs->pluck('description')->toArray(),
            'tempat_lapor' => 'Purwokerto',
            'tanggal_lapor' => $report->submitted_at ? $report->submitted_at->format('d F Y') : now()->format('d F Y'),
            'atasan_nama' => $spd->approvedByEmployee->name ?? 'Dr. Rektor UIN',
            'atasan_nip' => $spd->approvedByEmployee->nip ?? '195301011988031006',
        ];

        return $this->generateAndDownload('/generate-lpj-pdf', $data);
    }

    protected function generateAndDownload(string $endpoint, array $data): ?string
    {
        try {
            $response = Http::timeout(60)->post($this->baseUrl . $endpoint, $data);

            if ($response->successful()) {
                $downloadUrl = $response->json('download_url');
                if ($downloadUrl) {
                    $fileResponse = Http::timeout(60)->get($this->baseUrl . $downloadUrl);
                    if ($fileResponse->successful()) {
                        return $fileResponse->body();
                    }
                }
            }
            
            Log::error("Python Document Service Error [{$endpoint}]: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Python Document Service Exception: " . $e->getMessage());
        }

        return null;
    }
}
