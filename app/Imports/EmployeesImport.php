<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class EmployeesImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    private $imported = 0;
    private $updated = 0;
    private $failed = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Get NIP - support multiple column names
                $nip = $this->getNip($row);
                
                if (empty($nip) || strlen($nip) < 10) {
                    $this->failed[] = [
                        'row' => $index + 2,
                        'nip' => $nip ?: 'kosong',
                        'error' => 'NIP tidak valid (harus minimal 10 digit)'
                    ];
                    continue;
                }

                // Parse tanggal lahir
                $birthDate = $this->parseDate($row['tanggal_lahir'] ?? $row['tgl_lahir'] ?? null);

                // Data untuk tabel employees
                $employeeData = [
                    'nip' => $nip,
                    'name' => $row['nama'] ?? $row['nama_dengan_gelar'] ?? $row['name'] ?? 'Unknown',
                    'birth_date' => $birthDate,
                    'position' => $row['jabatan'] ?? $row['position'] ?? '',
                    'class' => $row['golongan'] ?? $row['gol'] ?? $row['pangkat'] ?? '',
                    'structural_position' => $row['tugas_tambahan'] ?? $row['structural'] ?? '',
                    'faculty' => $this->extractFaculty($row),
                    'status' => $row['status'] ?? 'PNS',
                    'is_active' => true,
                    'updated_at' => now(),
                ];

                // UPSERT: Insert atau Update kalau NIP sudah ada
                $employee = Employee::updateOrCreate(
                    ['nip' => $nip],
                    $employeeData
                );

                if ($employee->wasRecentlyCreated) {
                    $this->imported++;
                    // Buat user account untuk yang baru
                    $this->createUserAccount($employee, $birthDate);
                } else {
                    $this->updated++;
                    // Update user account yang sudah ada
                    $this->updateUserIfExists($employee);
                }

            } catch (\Exception $e) {
                Log::error("Import error row " . ($index + 2) . ": " . $e->getMessage());
                $this->failed[] = [
                    'row' => $index + 2,
                    'nip' => $row['nip'] ?? $row['nip_spaceless'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    private function getNip($row): string
    {
        // Try different column names
        $nipColumns = ['nip_spaceless', 'nip', 'NIP', 'Nip', 'NIP Spaceless'];
        
        foreach ($nipColumns as $col) {
            if (isset($row[$col]) && !empty($row[$col])) {
                return preg_replace('/\s+/', '', (string) $row[$col]);
            }
        }
        
        // Also try first column if it looks like NIP
        $firstValue = $row->first();
        if (is_numeric($firstValue) && strlen((string) $firstValue) >= 10) {
            return (string) $firstValue;
        }
        
        return '';
    }

    private function createUserAccount($employee, $birthDate)
    {
        // Check if user already exists
        if (User::where('nip', $employee->nip)->exists()) {
            return;
        }

        // Password default: DDMMYYYY from birth date
        $defaultPassword = $birthDate 
            ? Carbon::parse($birthDate)->format('dmY') 
            : '12345678';

        try {
            User::create([
                'name' => $employee->name,
                'nip' => $employee->nip,
                'email' => $this->generateEmail($employee),
                'password' => Hash::make($defaultPassword),
                'role' => $this->determineRole($employee->structural_position),
                'is_password_reset' => false,
                'employee_id' => $employee->id,
            ]);
        } catch (\Exception $e) {
            Log::warning("Could not create user for {$employee->nip}: " . $e->getMessage());
        }
    }

    private function updateUserIfExists($employee)
    {
        User::where('nip', $employee->nip)->update([
            'name' => $employee->name,
            'role' => $this->determineRole($employee->structural_position),
        ]);
    }

    private function parseDate($dateValue)
    {
        if (empty($dateValue)) return null;

        // Handle Excel date serial number
        if (is_numeric($dateValue)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Handle string formats
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y', 'd M Y', 'd F Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, trim($dateValue))->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's flexible parsing
        try {
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractFaculty($row)
    {
        $text = ($row['tugas_tambahan'] ?? '') . ' ' . ($row['jabatan'] ?? '') . ' ' . ($row['unit'] ?? '');
        
        $faculties = [
            'FTIK' => ['ftik', 'tarbiyah', 'ilmu tarbiyah', 'pendidikan'],
            'FEBI' => ['febi', 'ekonomi', 'bisnis islam'],
            'FAI' => ['fai', 'agama islam', 'ushuluddin'],
            'FISIP' => ['fisip', 'ilmu sosial', 'politik'],
            'FH' => ['hukum', 'syariah'],
            'FDK' => ['dakwah', 'komunikasi'],
            'FUAD' => ['fuad', 'ushuluddin', 'adab'],
            'FASYA' => ['fasya', 'syariah'],
        ];

        $textLower = strtolower($text);
        foreach ($faculties as $code => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($textLower, $keyword) !== false) {
                    return $code;
                }
            }
        }
        
        return 'UMUM';
    }

    private function determineRole($structural)
    {
        if (empty($structural)) return 'pegawai';
        
        $pos = strtolower($structural);
        
        if (strpos($pos, 'wakil rektor') !== false) return 'warek';
        if (strpos($pos, 'rektor') !== false) return 'rektor';
        if (strpos($pos, 'wakil dekan') !== false) return 'wadek';
        if (strpos($pos, 'dekan') !== false) return 'dekan';
        if (strpos($pos, 'ketua') !== false || strpos($pos, 'kepala') !== false) return 'kabag';
        if (strpos($pos, 'sekretaris') !== false) return 'sekretaris';
        
        return 'pegawai';
    }

    private function generateEmail($employee)
    {
        $cleanName = strtolower(preg_replace('/[^a-zA-Z]/', '', $employee->name));
        $cleanName = substr($cleanName, 0, 15); // Limit length
        $suffix = substr($employee->nip, -4);
        
        return $cleanName . '.' . $suffix . '@uinsaizu.ac.id';
    }

    // Getters untuk laporan
    public function getImportedCount() { return $this->imported; }
    public function getUpdatedCount() { return $this->updated; }
    public function getFailed() { return $this->failed; }
}
