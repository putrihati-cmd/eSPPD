<?php

namespace App\Livewire\Reports;

use App\Models\Spd;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportBuilder extends Component
{
    // Report Config
    public string $reportType = 'sppd_list';
    public array $selectedFields = ['spd_number', 'employee_name', 'destination', 'departure_date', 'status'];
    
    // Filters
    public string $filterStatus = '';
    public string $filterUnitId = '';
    public string $filterFromDate = '';
    public string $filterToDate = '';
    public string $groupBy = '';
    
    // Preview
    public array $previewData = [];
    public int $previewLimit = 10;

    protected array $availableFields = [
        'spd_number' => 'Nomor SPPD',
        'spt_number' => 'Nomor SPT',
        'employee_name' => 'Nama Pegawai',
        'employee_nip' => 'NIP',
        'unit_name' => 'Unit Kerja',
        'destination' => 'Tujuan',
        'purpose' => 'Keperluan',
        'departure_date' => 'Tgl Berangkat',
        'return_date' => 'Tgl Kembali',
        'duration' => 'Durasi (Hari)',
        'transport_type' => 'Transportasi',
        'estimated_cost' => 'Biaya Estimasi',
        'actual_cost' => 'Biaya Aktual',
        'status' => 'Status',
        'created_at' => 'Tanggal Dibuat',
    ];

    protected array $reportTypes = [
        'sppd_list' => 'Daftar SPPD',
        'sppd_summary' => 'Ringkasan SPPD per Unit',
        'budget_usage' => 'Penggunaan Anggaran',
        'status_distribution' => 'Distribusi Status',
    ];

    public function mount()
    {
        $this->generatePreview();
    }

    public function render()
    {
        $units = Unit::orderBy('name')->get();
        
        return view('livewire.reports.report-builder', [
            'availableFields' => $this->availableFields,
            'reportTypes' => $this->reportTypes,
            'units' => $units,
        ])->layout('layouts.app', ['header' => 'Report Builder']);
    }

    public function generatePreview()
    {
        $this->previewData = $this->buildQuery()->limit($this->previewLimit)->get()->toArray();
    }

    public function updated($property)
    {
        if (in_array($property, ['reportType', 'filterStatus', 'filterUnitId', 'filterFromDate', 'filterToDate', 'groupBy', 'selectedFields'])) {
            $this->generatePreview();
        }
    }

    protected function buildQuery()
    {
        $query = Spd::query()
            ->select('spds.*')
            ->join('employees', 'spds.employee_id', '=', 'employees.id')
            ->join('units', 'employees.unit_id', '=', 'units.id');

        // Apply filters
        if ($this->filterStatus) {
            $query->where('spds.status', $this->filterStatus);
        }
        if ($this->filterUnitId) {
            $query->where('units.id', $this->filterUnitId);
        }
        if ($this->filterFromDate) {
            $query->whereDate('spds.departure_date', '>=', $this->filterFromDate);
        }
        if ($this->filterToDate) {
            $query->whereDate('spds.departure_date', '<=', $this->filterToDate);
        }

        // Select fields based on type
        $selectFields = ['spds.id'];
        foreach ($this->selectedFields as $field) {
            switch ($field) {
                case 'employee_name':
                    $selectFields[] = 'employees.name as employee_name';
                    break;
                case 'employee_nip':
                    $selectFields[] = 'employees.nip as employee_nip';
                    break;
                case 'unit_name':
                    $selectFields[] = 'units.name as unit_name';
                    break;
                default:
                    $selectFields[] = "spds.$field";
            }
        }
        $query->select($selectFields);

        // Grouping for summary reports
        if ($this->groupBy && $this->reportType !== 'sppd_list') {
            $query->groupBy($this->groupBy);
        }

        return $query->orderBy('spds.departure_date', 'desc');
    }

    public function exportExcel()
    {
        $data = $this->buildQuery()->get();
        $headers = array_map(fn($f) => $this->availableFields[$f] ?? $f, $this->selectedFields);
        
        return Excel::download(
            new \App\Exports\CustomReportExport($data, $headers, $this->selectedFields),
            'Report_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $data = $this->buildQuery()->get();
        $headers = array_map(fn($f) => $this->availableFields[$f] ?? $f, $this->selectedFields);
        
        $pdf = Pdf::loadView('pdf.custom-report', [
            'data' => $data,
            'headers' => $headers,
            'fields' => $this->selectedFields,
            'reportType' => $this->reportTypes[$this->reportType] ?? 'Laporan',
            'filters' => [
                'status' => $this->filterStatus,
                'from_date' => $this->filterFromDate,
                'to_date' => $this->filterToDate,
            ],
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Report_' . now()->format('Y-m-d_His') . '.pdf'
        );
    }

    public function exportCsv()
    {
        $data = $this->buildQuery()->get();
        $headers = array_map(fn($f) => $this->availableFields[$f] ?? $f, $this->selectedFields);
        
        return Excel::download(
            new \App\Exports\CustomReportExport($data, $headers, $this->selectedFields),
            'Report_' . now()->format('Y-m-d_His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}
