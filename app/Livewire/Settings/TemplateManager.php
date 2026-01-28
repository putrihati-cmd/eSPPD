<?php

namespace App\Livewire\Settings;

use App\Models\ReportTemplate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TemplateManager extends Component
{
    use WithFileUploads;

    public $file;
    public string $templateName = '';
    public string $templateType = 'trip_report';
    public bool $isDefault = false;
    public ?string $selectedTemplateId = null;

    protected $rules = [
        'file' => 'required|mimes:docx|max:5120', // Max 5MB
        'templateName' => 'required|string|max:100',
        'templateType' => 'required|in:trip_report,sppd,spt',
    ];

    public function render()
    {
        $templates = ReportTemplate::orderBy('type')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.settings.template-manager', [
            'templates' => $templates,
        ])->layout('layouts.app', ['header' => 'Kelola Template Laporan']);
    }

    public function upload()
    {
        $this->validate();

        // Store file
        $path = $this->file->storeAs(
            'templates',
            $this->templateType . '_' . time() . '.docx',
            'local'
        );

        // If this is default, unset other defaults of same type
        if ($this->isDefault) {
            ReportTemplate::where('type', $this->templateType)
                ->update(['is_default' => false]);
        }

        // Create template record
        ReportTemplate::create([
            'name' => $this->templateName,
            'type' => $this->templateType,
            'file_path' => $path,
            'is_default' => $this->isDefault,
            'uploaded_by' => auth()->id(),
        ]);

        $this->reset(['file', 'templateName', 'templateType', 'isDefault']);
        session()->flash('success', 'Template berhasil diupload.');
    }

    public function setDefault(string $templateId)
    {
        $template = ReportTemplate::findOrFail($templateId);

        // Unset other defaults of same type
        ReportTemplate::where('type', $template->type)
            ->update(['is_default' => false]);

        // Set this as default
        $template->update(['is_default' => true]);

        session()->flash('success', 'Template berhasil dijadikan default.');
    }

    public function delete(string $templateId)
    {
        $template = ReportTemplate::findOrFail($templateId);

        // Delete file
        if ($template->file_path && Storage::exists($template->file_path)) {
            Storage::delete($template->file_path);
        }

        // Delete record
        $template->delete();

        session()->flash('success', 'Template berhasil dihapus.');
    }

    public function download(string $templateId)
    {
        $template = ReportTemplate::findOrFail($templateId);

        if (!$template->file_path || !Storage::exists($template->file_path)) {
            session()->flash('error', 'File template tidak ditemukan.');
            return;
        }

        return Storage::download(
            $template->file_path,
            $template->name . '.docx'
        );
    }
}
