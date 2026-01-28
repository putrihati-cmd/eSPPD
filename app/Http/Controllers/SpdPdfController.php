<?php

namespace App\Http\Controllers;

use App\Models\Spd;
use Barryvdh\DomPDF\Facade\Pdf;

class SpdPdfController extends Controller
{
    public function downloadSpt(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget']);
        
        $pdf = Pdf::loadView('pdf.spt', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download("SPT-{$spd->spt_number}.pdf");
    }
    
    public function downloadSpd(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'costs']);
        
        $pdf = Pdf::loadView('pdf.spd', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download("SPD-{$spd->spd_number}.pdf");
    }
    
    public function viewSpt(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget']);
        
        $pdf = Pdf::loadView('pdf.spt', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        return $pdf->stream("SPT-{$spd->spt_number}.pdf");
    }
    
    public function viewSpd(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'costs']);
        
        $pdf = Pdf::loadView('pdf.spd', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        return $pdf->stream("SPD-{$spd->spd_number}.pdf");
    }
}
