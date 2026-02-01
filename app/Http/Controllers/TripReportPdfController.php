<?php

namespace App\Http\Controllers;

use App\Models\TripReport;
use App\Services\PythonDocumentService;

class TripReportPdfController extends Controller
{
    protected $pythonService;

    public function __construct(PythonDocumentService $pythonService)
    {
        $this->pythonService = $pythonService;
    }

    public function download(TripReport $report)
    {
        $report->load(['spd', 'employee.unit', 'activities', 'outputs']);
        
        $pdfContent = $this->pythonService->getTripReportPdf($report);
        
        if (!$pdfContent) {
            return back()->with('error', 'Gagal generate Laporan via Document Service.');
        }

        $filename = "Laporan-" . str_replace(['/', '\\'], '-', $report->spd->spd_number) . ".pdf";
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}

