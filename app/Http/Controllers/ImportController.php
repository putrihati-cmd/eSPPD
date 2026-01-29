<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\EmployeesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    /**
     * Show import form
     */
    public function showForm()
    {
        return view('import.excel');
    }

    /**
     * Handle Excel import with enhanced security
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', // Max 10MB
                // MIME type validation for additional security
                function ($attribute, $value, $fail) {
                    $allowedMimes = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
                        'application/vnd.ms-excel', // xls
                        'text/csv',
                        'text/plain',
                    ];
                    
                    // Check both extension and MIME type
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();
                    
                    $validExtension = in_array($extension, ['xlsx', 'xls', 'csv']);
                    $validMime = in_array($mimeType, $allowedMimes);
                    
                    if (!$validExtension || !$validMime) {
                        $fail('File harus berupa Excel (.xlsx, .xls) atau CSV dengan format yang valid.');
                    }
                },
            ]
        ], [
            'file.required' => 'File Excel wajib diupload',
            'file.mimes' => 'Format file harus .xlsx, .xls, atau .csv',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        $import = new EmployeesImport();

        try {
            Excel::import($import, $request->file('file'));
            
            $report = [
                'success' => true,
                'imported' => $import->getImportedCount(),
                'updated' => $import->getUpdatedCount(),
                'failed' => count($import->getFailed()),
                'failed_details' => $import->getFailed(),
                'total' => $import->getImportedCount() + $import->getUpdatedCount()
            ];

            Log::info("Import completed: {$report['imported']} new, {$report['updated']} updated, {$report['failed']} failed");

            return back()->with('report', $report);

        } catch (\Exception $e) {
            Log::error("Import error: " . $e->getMessage());
            return back()->withErrors(['file' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Download sample template
     */
    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_pegawai.csv"',
        ];

        $columns = ['nip', 'nama', 'tanggal_lahir', 'jabatan', 'golongan', 'tugas_tambahan', 'status'];
        $sample = ['196708151992031003', 'Dr. Ahmad Contoh, M.Pd.', '15/08/1967', 'Lektor Kepala', 'IV/b', 'Kepala Bagian', 'PNS'];

        $callback = function() use ($columns, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
