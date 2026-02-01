<?php

namespace App\Http\Controllers;

use App\Models\Spd;
use App\Services\PythonDocumentService;
use Illuminate\Http\Response;

class SpdPdfController extends Controller
{
    protected $pythonService;

    public function __construct(PythonDocumentService $pythonService)
    {
        $this->pythonService = $pythonService;
    }

    public function downloadSpt(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'followers.employee']);
        
        if ($spd->followers->count() > 0) {
            return $this->downloadZip($spd, 'spt');
        }
        
        $pdfContent = $this->pythonService->getSptPdf($spd);
        
        if (!$pdfContent) {
            return back()->with('error', 'Gagal generate SPT via Document Service.');
        }

        $filename = "SPT-" . str_replace(['/', '\\'], '-', $spd->spt_number) . ".pdf";
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
    
    public function downloadSpd(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'costs', 'followers.employee']);
        
        if ($spd->followers->count() > 0) {
            return $this->downloadZip($spd, 'spd');
        }
        
        $pdfContent = $this->pythonService->getSpdPdf($spd);
        
        if (!$pdfContent) {
            return back()->with('error', 'Gagal generate SPD via Document Service.');
        }

        $filename = "SPD-" . str_replace(['/', '\\'], '-', $spd->spd_number) . ".pdf";
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
    
    protected function downloadZip(Spd $spd, string $type)
    {
        $zipName = strtoupper($type) . "-Group-" . str_replace(['/', '\\'], '-', $spd->{$type . '_number'}) . ".zip";
        $zipPath = storage_path('app/public/' . $zipName);
        
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            
            // 1. Add Main Employee Document
            $pdfContent = ($type === 'spt') ? $this->pythonService->getSptPdf($spd) : $this->pythonService->getSpdPdf($spd);
            $filename = "Utama-" . $spd->employee->name . ".pdf";
            $zip->addFromString($filename, $pdfContent);
            
            // 2. Add Followers Documents
            foreach ($spd->followers as $follower) {
                $followerSpd = clone $spd;
                $followerSpd->setRelation('employee', $follower->employee);
                
                $pdfContent = ($type === 'spt') ? $this->pythonService->getSptPdf($followerSpd) : $this->pythonService->getSpdPdf($followerSpd);
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
        
        $pdfContent = $this->pythonService->getSptPdf($spd);
        
        if (!$pdfContent) {
            return response('Gagal generate SPT', 500);
        }

        $filename = "SPT-" . str_replace(['/', '\\'], '-', $spd->spt_number) . ".pdf";
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }
    
    public function viewSpd(Spd $spd)
    {
        $spd->load(['employee', 'unit', 'budget', 'costs']);
        
        $pdfContent = $this->pythonService->getSpdPdf($spd);
        
        if (!$pdfContent) {
            return response('Gagal generate SPD', 500);
        }

        $filename = "SPD-" . str_replace(['/', '\\'], '-', $spd->spd_number) . ".pdf";
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }
}

