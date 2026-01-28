<?php

namespace App\Http\Controllers;

use App\Models\TripReport;
use Barryvdh\DomPDF\Facade\Pdf;

class TripReportPdfController extends Controller
{
    public function download(TripReport $report)
    {
        $report->load(['spd', 'employee.unit', 'activities', 'outputs']);
        
        $pdf = Pdf::loadView('pdf.trip-report', [
            'report' => $report,
        ])->setPaper('a4', 'portrait');
        
        $filename = "Laporan-" . str_replace(['/', '\\'], '-', $report->spd->spd_number) . ".pdf";
        return $pdf->download($filename);
    }
}
