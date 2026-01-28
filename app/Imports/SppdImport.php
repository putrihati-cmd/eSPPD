<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Spd;
use App\Models\Budget;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;

class SppdImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];

    public function model(array $row)
    {
        // Find employee by NIP
        $employee = Employee::where('nip', $row['nip_pegawai'])->first();
        
        if (!$employee) {
            $this->errorCount++;
            $this->errors[] = "NIP {$row['nip_pegawai']} tidak ditemukan";
            return null;
        }

        // Get default budget
        $budget = Budget::where('year', now()->year)->first();

        // Generate numbers
        $year = now()->format('Y');
        $month = now()->format('m');
        $count = Spd::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        $spdNumber = sprintf("SPD/%s/%s/%03d", $year, $month, $count);
        $sptNumber = sprintf("SPT/%s/%s/%03d", $year, $month, $count);

        // Calculate duration
        $departureDate = Carbon::parse($row['tanggal_berangkat_yyyy_mm_dd']);
        $returnDate = Carbon::parse($row['tanggal_kembali_yyyy_mm_dd']);
        $duration = $departureDate->diffInDays($returnDate) + 1;

        $this->successCount++;

        return new Spd([
            'organization_id' => $employee->unit->organization_id ?? null,
            'unit_id' => $employee->unit_id,
            'employee_id' => $employee->id,
            'spt_number' => $sptNumber,
            'spd_number' => $spdNumber,
            'destination' => $row['tujuan'],
            'purpose' => $row['keperluanmaksud'],
            'invitation_number' => $row['nomor_undangan'] ?? null,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'duration' => $duration,
            'transport_type' => $row['transportasi_pesawatkeretabusmobil_dinaskapal'],
            'needs_accommodation' => strtolower($row['perlu_akomodasi_yatidak'] ?? 'tidak') === 'ya',
            'budget_id' => $budget?->id,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
    }

    public function rules(): array
    {
        return [
            'nip_pegawai' => 'required|string',
            'tujuan' => 'required|string',
            'keperluanmaksud' => 'required|string',
            'tanggal_berangkat_yyyy_mm_dd' => 'required|date',
            'tanggal_kembali_yyyy_mm_dd' => 'required|date|after_or_equal:tanggal_berangkat_yyyy_mm_dd',
            'transportasi_pesawatkeretabusmobil_dinaskapal' => 'required|in:pesawat,kereta,bus,mobil_dinas,kapal',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getImportErrors(): array
    {
        return $this->errors;
    }
}
