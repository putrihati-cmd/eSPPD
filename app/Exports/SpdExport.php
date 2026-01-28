<?php

namespace App\Exports;

use App\Models\Spd;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SpdExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Simple implementation of applying filters
        // In production, this would be more complex
        return Spd::query()
            ->when(isset($this->filters['status']), function($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when(isset($this->filters['start_date']), function($q) {
                $q->whereDate('created_at', '>=', $this->filters['start_date']);
            })
             ->when(isset($this->filters['end_date']), function($q) {
                $q->whereDate('created_at', '<=', $this->filters['end_date']);
            })
            ->with(['employee'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'NIP',
            'Destination',
            'Purpose',
            'Status',
            'Departure Date',
            'Return Date',
            'Total Cost',
        ];
    }

    public function map($spd): array
    {
        return [
            $spd->spd_number,
            $spd->employee->name ?? '-',
            $spd->employee->nip ?? '-',
            $spd->destination,
            $spd->purpose,
            $spd->status_label,
            $spd->departure_date->format('d/m/Y'),
            $spd->return_date->format('d/m/Y'),
            $spd->estimated_cost,
        ];
    }
}
