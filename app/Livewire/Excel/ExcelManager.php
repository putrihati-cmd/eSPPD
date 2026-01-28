<?php

namespace App\Livewire\Excel;

use App\Jobs\ImportSppdJob;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SppdTemplateExport;
use App\Exports\SppdDataExport;

class ExcelManager extends Component
{
    use WithFileUploads;

    public $file;
    public bool $isUploading = false;
    public int $progress = 0;
    public ?string $uploadMessage = null;
    public ?string $uploadError = null;

    // Export filters
    public string $exportStatus = '';
    public string $exportFromDate = '';
    public string $exportToDate = '';
    public string $exportUnitId = '';

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
    ];

    public function render()
    {
        return view('livewire.excel.excel-manager')
            ->layout('layouts.app', ['header' => 'Import/Export Excel']);
    }

    public function downloadTemplate()
    {
        return Excel::download(new SppdTemplateExport, 'Template_Import_SPPD.xlsx');
    }

    public function upload()
    {
        $this->validate();
        
        $this->isUploading = true;
        $this->progress = 10;
        $this->uploadMessage = 'Mengupload file...';
        $this->uploadError = null;

        try {
            // Store file temporarily
            $path = $this->file->store('imports');
            $this->progress = 30;
            $this->uploadMessage = 'File berhasil diupload. Memproses data...';

            // Check file size for queue decision
            $fileSize = Storage::size($path);
            
            if ($fileSize > 1024 * 1024) { // > 1MB, use queue
                $this->progress = 50;
                $this->uploadMessage = 'File besar terdeteksi. Memproses di background...';
                
                ImportSppdJob::dispatch($path, auth()->id());
                
                $this->progress = 100;
                $this->uploadMessage = 'Import dijadwalkan! Anda akan menerima notifikasi setelah selesai.';
            } else {
                // Small file, process directly
                $this->progress = 50;
                $import = new \App\Imports\SppdImport();
                Excel::import($import, Storage::path($path));
                
                $this->progress = 100;
                $success = $import->getSuccessCount();
                $error = $import->getErrorCount();
                $this->uploadMessage = "Import selesai! Berhasil: $success, Gagal: $error";
                
                if ($error > 0) {
                    $this->uploadError = implode("\n", array_slice($import->getImportErrors(), 0, 5));
                }
                
                Storage::delete($path);
            }
        } catch (\Exception $e) {
            $this->progress = 0;
            $this->uploadMessage = null;
            $this->uploadError = 'Error: ' . $e->getMessage();
        }

        $this->isUploading = false;
        $this->file = null;
    }

    public function export()
    {
        $filters = array_filter([
            'status' => $this->exportStatus,
            'from_date' => $this->exportFromDate,
            'to_date' => $this->exportToDate,
            'unit_id' => $this->exportUnitId,
        ]);

        $filename = 'Data_SPPD_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new SppdDataExport($filters), $filename);
    }

    public function resetUpload()
    {
        $this->file = null;
        $this->progress = 0;
        $this->uploadMessage = null;
        $this->uploadError = null;
        $this->isUploading = false;
    }
}
