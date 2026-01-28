<?php

namespace App\Http\Controllers;

use App\Models\Spd;
use Barryvdh\DomPDF\Facade\Pdf;

class SpdPdfController extends Controller
{

    public function downloadSpt(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'followers.employee']);
        
        if ($spd->followers->count() > 0) {
            return $this->downloadZip($spd, 'spt');
        }
        
        $pdf = Pdf::loadView('pdf.spt', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        $filename = "SPT-" . str_replace(['/', '\\'], '-', $spd->spt_number) . ".pdf";
        return $pdf->download($filename);
    }
    
    public function downloadSpd(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'costs', 'followers.employee']);
        
        if ($spd->followers->count() > 0) {
            return $this->downloadZip($spd, 'spd');
        }
        
        $pdf = Pdf::loadView('pdf.spd', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        $filename = "SPD-" . str_replace(['/', '\\'], '-', $spd->spd_number) . ".pdf";
        return $pdf->download($filename);
    }
    
    protected function downloadZip(Spd $spd, string $type)
    {
        $zipName = strtoupper($type) . "-Group-" . str_replace(['/', '\\'], '-', $spd->{$type . '_number'}) . ".zip";
        $zipPath = storage_path('app/public/' . $zipName);
        
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            
            // 1. Add Main Employee Document
            $pdfContent = Pdf::loadView("pdf.{$type}", ['spd' => $spd])->setPaper('a4', 'portrait')->output();
            $filename = "Utama-" . $spd->employee->name . ".pdf";
            $zip->addFromString($filename, $pdfContent);
            
            // 2. Add Followers Documents
            foreach ($spd->followers as $follower) {
                // Clone SPD and limit relations to avoid side effects (though clone is shallow for models)
                // Better to simple temporarily swap the employee relation
                $followerSpd = clone $spd;
                $followerSpd->setRelation('employee', $follower->employee);
                
                // For SPD, costs might need adjustment (e.g. followers might not get transport cost if shared?)
                // For now, assuming full entitlement or copied entitlement as per requirement "generate for each"
                
                $pdfContent = Pdf::loadView("pdf.{$type}", ['spd' => $followerSpd])->setPaper('a4', 'portrait')->output();
                $filename = "Pengikut-" . $follower->employee->name . ".pdf";
                $zip->addFromString($filename, $pdfContent);
            }
            
            $zip->close();
        }
        
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function viewSpt(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget']);
        
        $pdf = Pdf::loadView('pdf.spt', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        $filename = "SPT-" . str_replace(['/', '\\'], '-', $spd->spt_number) . ".pdf";
        return $pdf->stream($filename);
    }
    
    public function viewSpd(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'costs']);
        
        $pdf = Pdf::loadView('pdf.spd', [
            'spd' => $spd,
        ])->setPaper('a4', 'portrait');
        
        $filename = "SPD-" . str_replace(['/', '\\'], '-', $spd->spd_number) . ".pdf";
        return $pdf->stream($filename);
    }
}
