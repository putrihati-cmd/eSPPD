<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomReportExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;
    protected array $headers;
    protected array $fields;

    public function __construct($data, array $headers, array $fields)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->fields = $fields;
    }

    public function collection()
    {
        return $this->data->map(function ($row) {
            $result = [];
            foreach ($this->fields as $field) {
                $value = $row[$field] ?? ($row->{$field} ?? '-');
                
                // Format dates
                if (in_array($field, ['departure_date', 'return_date', 'created_at']) && $value !== '-') {
                    $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
                }
                // Format currency
                if (in_array($field, ['estimated_cost', 'actual_cost']) && $value !== '-') {
                    $value = 'Rp ' . number_format($value, 0, ',', '.');
                }
                
                $result[] = $value;
            }
            return $result;
        });
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ]],
        ];
    }
}
