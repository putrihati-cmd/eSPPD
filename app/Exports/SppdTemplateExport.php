<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SppdTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        // Sample row for reference
        return [
            [
                '198001012010011001',
                'Jakarta',
                'Rapat Koordinasi Program',
                '2024-02-15',
                '2024-02-17',
                'pesawat',
                'Ya',
                'Undangan_001',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIP Pegawai*',
            'Tujuan*',
            'Keperluan/Maksud*',
            'Tanggal Berangkat* (YYYY-MM-DD)',
            'Tanggal Kembali* (YYYY-MM-DD)',
            'Transportasi* (pesawat/kereta/bus/mobil_dinas/kapal)',
            'Perlu Akomodasi (Ya/Tidak)',
            'Nomor Undangan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4472C4'],
            ],
        ]);

        // Add instruction row
        $sheet->insertNewRowBefore(1);
        $sheet->setCellValue('A1', 'Template Import SPPD - Isi data mulai baris 3. Kolom bertanda * wajib diisi.');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['argb' => 'FF666666']],
        ]);

        return [];
    }
}
