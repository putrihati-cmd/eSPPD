<?php

namespace App\Http\Controllers;

use App\Exports\SppdDataExport;
use App\Exports\SppdTemplateExport;
use App\Imports\SppdImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    /**
     * Download template Excel untuk bulk upload
     */
    public function template()
    {
        return Excel::download(new SppdTemplateExport, 'Template_Import_SPPD.xlsx');
    }

    /**
     * Import SPPD dari file Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        $import = new SppdImport();
        Excel::import($import, $request->file('file'));

        $successCount = $import->getSuccessCount();
        $errorCount = $import->getErrorCount();
        $errors = $import->getImportErrors();

        if ($errorCount > 0) {
            return redirect()->back()->with([
                'success' => "Berhasil import $successCount data.",
                'warning' => "Gagal import $errorCount data.",
                'import_errors' => $errors,
            ]);
        }

        return redirect()->back()->with('success', "Berhasil import $successCount data SPPD.");
    }

    /**
     * Export data SPPD ke Excel
     */
    public function export(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'unit_id' => $request->get('unit_id'),
        ];

        $filename = 'Data_SPPD_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new SppdDataExport($filters), $filename);
    }
}
