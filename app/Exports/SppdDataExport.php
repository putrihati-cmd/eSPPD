<?php

namespace App\Exports;

use App\Models\Spd;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SppdDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Spd::with(['employee', 'unit', 'budget']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (isset($this->filters['from_date'])) {
            $query->where('departure_date', '>=', $this->filters['from_date']);
        }
        if (isset($this->filters['to_date'])) {
            $query->where('departure_date', '<=', $this->filters['to_date']);
        }
        if (isset($this->filters['unit_id'])) {
            $query->where('unit_id', $this->filters['unit_id']);
        }

        return $query->orderBy('departure_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor SPT',
            'Nomor SPD',
            'Nama Pegawai',
            'NIP',
            'Unit Kerja',
            'Tujuan',
            'Keperluan',
            'Tgl Berangkat',
            'Tgl Kembali',
            'Durasi (Hari)',
            'Transportasi',
            'Estimasi Biaya',
            'Biaya Aktual',
            'Status',
            'Anggaran',
        ];
    }

    public function map($spd): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $spd->spt_number,
            $spd->spd_number,
            $spd->employee->name ?? '-',
            $spd->employee->nip ?? '-',
            $spd->unit->name ?? '-',
            $spd->destination,
            $spd->purpose,
            $spd->departure_date?->format('d/m/Y'),
            $spd->return_date?->format('d/m/Y'),
            $spd->duration,
            $spd->transport_type,
            number_format($spd->estimated_cost, 0, ',', '.'),
            number_format($spd->actual_cost ?? 0, 0, ',', '.'),
            $spd->status_label,
            $spd->budget->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
                'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
            ],
        ];
    }
}
