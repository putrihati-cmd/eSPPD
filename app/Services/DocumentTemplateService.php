<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\Spd;
use Illuminate\Support\Facades\Log;

/**
 * Service for generating DOCX document templates for SPPD
 */
class DocumentTemplateService
{
    /**
     * Generate SPPD document from template
     */
    public static function generateSpdDocument(Spd $spd): string
    {
        try {
            $phpWord = new PhpWord();

            // Set document properties
            $properties = $phpWord->getDocInfo();
            $properties->setTitle('SPPD: ' . $spd->nomor_sppd);
            $properties->setCreator(auth()->user()->name ?? 'System');

            // Add sections and content
            $section = $phpWord->addSection();

            // Header
            self::addHeader($section, $spd);

            // Body
            self::addBody($section, $spd);

            // Signatures
            self::addSignatures($section, $spd);

            // Save document
            $filename = 'SPPD_' . $spd->nomor_sppd . '_' . now()->format('YmdHis') . '.docx';
            $filepath = storage_path('documents/' . $filename);

            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($filepath);

            Log::info('document_generated', [
                'spd_id' => $spd->id,
                'filename' => $filename,
            ]);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('document_generation_failed', [
                'spd_id' => $spd->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate monthly report document
     */
    public static function generateMonthlyReportDocument($reportData): string
    {
        try {
            $phpWord = new PhpWord();

            // Set document properties
            $properties = $phpWord->getDocInfo();
            $properties->setTitle('Laporan Bulanan: ' . $reportData['month']);

            $section = $phpWord->addSection();

            // Title
            $section->addText('LAPORAN BULANAN e-SPPD', ['name' => 'Calibri', 'size' => 14, 'bold' => true]);
            $section->addText('Periode: ' . $reportData['month'], ['name' => 'Calibri', 'size' => 12]);

            // Summary
            $section->addHeading('Ringkasan', 2);
            $table = $section->addTable();
            $table->addRow();
            $table->addCell(4000)->addText('Metrik');
            $table->addCell(2000)->addText('Nilai');

            foreach ($reportData['summary'] as $key => $value) {
                $table->addRow();
                $table->addCell(4000)->addText($key);
                $table->addCell(2000)->addText((string)$value);
            }

            // Detailed records
            if (!empty($reportData['records'])) {
                $section->addHeading('Detail Pengajuan', 2);
                $detailTable = $section->addTable();
                $detailTable->addRow();
                $detailTable->addCell(1000)->addText('No.');
                $detailTable->addCell(2000)->addText('Nomor SPPD');
                $detailTable->addCell(2000)->addText('Pemohon');
                $detailTable->addCell(2000)->addText('Status');

                foreach ($reportData['records'] as $index => $record) {
                    $detailTable->addRow();
                    $detailTable->addCell(1000)->addText((string)($index + 1));
                    $detailTable->addCell(2000)->addText($record['nomor_sppd'] ?? '-');
                    $detailTable->addCell(2000)->addText($record['pemohon'] ?? '-');
                    $detailTable->addCell(2000)->addText($record['status'] ?? '-');
                }
            }

            // Save document
            $filename = 'Laporan_Bulanan_' . $reportData['month'] . '_' . now()->format('YmdHis') . '.docx';
            $filepath = storage_path('documents/' . $filename);

            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($filepath);

            Log::info('report_generated', [
                'month' => $reportData['month'],
                'filename' => $filename,
            ]);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('report_generation_failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Add header to document
     */
    private static function addHeader($section, Spd $spd): void
    {
        // Logo and header info
        $section->addText('KEMENTERIAN / LEMBAGA', ['name' => 'Calibri', 'size' => 10]);
        $section->addText($spd->organisasi?->nama ?? 'N/A', ['name' => 'Calibri', 'size' => 11, 'bold' => true]);

        $section->addTextBreak(1);

        // Title
        $section->addText('SURAT PERINTAH PERJALANAN DINAS', ['name' => 'Calibri', 'size' => 12, 'bold' => true, 'alignment' => 'center']);

        $section->addTextBreak(1);

        // Document number
        $section->addText('Nomor : ' . $spd->nomor_sppd, ['name' => 'Calibri', 'size' => 10]);
    }

    /**
     * Add body content to document
     */
    private static function addBody($section, Spd $spd): void
    {
        $section->addHeading('Data Pengajuan', 2);

        $infoTable = $section->addTable();

        // Employee info
        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Nama Pegawai', ['bold' => true]);
        $infoTable->addCell(4000)->addText($spd->employee?->nama ?? 'N/A');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('NIP/NRP', ['bold' => true]);
        $infoTable->addCell(4000)->addText($spd->employee?->nip ?? 'N/A');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Jabatan', ['bold' => true]);
        $infoTable->addCell(4000)->addText($spd->employee?->jabatan ?? 'N/A');

        // Travel info
        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Tujuan Perjalanan', ['bold' => true]);
        $infoTable->addCell(4000)->addText($spd->tujuan_perjalanan ?? 'N/A');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Tanggal Keberangkatan', ['bold' => true]);
        $infoTable->addCell(4000)->addText($spd->tanggal_keberangkatan?->format('d-m-Y') ?? 'N/A');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Tanggal Kepulangan', ['bold' => true]);
        $infoTable->addCell(4000)->addText($spd->tanggal_kepulangan?->format('d-m-Y') ?? 'N/A');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Perkiraan Biaya', ['bold' => true]);
        $infoTable->addCell(4000)->addText('Rp. ' . number_format($spd->perkiraan_biaya ?? 0, 0, ',', '.'));

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Keperluan', ['bold' => true]);
        $infoTable->addCell(4000)->addText($spd->keperluan ?? 'N/A');
    }

    /**
     * Add signature section to document
     */
    private static function addSignatures($section, Spd $spd): void
    {
        $section->addTextBreak(2);
        $section->addHeading('Pengesahan', 2);

        $sigTable = $section->addTable();

        // Get the latest approver
        $latestApproval = $spd->approvals()
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestApproval) {
            $sigTable->addRow();
            $sigTable->addCell(2000)->addText('Disetujui Oleh:');
            $sigTable->addCell(2000)->addText($latestApproval->approver?->nama ?? 'N/A');

            $sigTable->addRow();
            $sigTable->addCell(2000)->addText('Tanggal:');
            $sigTable->addCell(2000)->addText($latestApproval->created_at->format('d-m-Y'));
        }

        $section->addTextBreak(3);
        $section->addText('Dokumen ini dihasilkan secara otomatis oleh Sistem e-SPPD', [
            'name' => 'Calibri', 'size' => 9, 'italic' => true
        ]);
    }
}
