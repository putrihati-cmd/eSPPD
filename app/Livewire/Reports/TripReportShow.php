<?php

namespace App\Livewire\Reports;

use App\Models\TripReport;
use Livewire\Component;

class TripReportShow extends Component
{
    public TripReport $report;

    public function mount(TripReport $report)
    {
        $this->report = $report->load(['spd', 'employee.unit', 'activities', 'outputs', 'verifier']);
    }

    public function submit()
    {
        if ($this->report->submitted_at) {
            session()->flash('error', 'Laporan sudah disubmit.');
            return;
        }

        $this->report->update(['submitted_at' => now()]);
        session()->flash('success', 'Laporan berhasil diajukan untuk verifikasi.');
        $this->report->refresh();
    }

    public function verify()
    {
        // Check if user is authorized (e.g., supervisor or admin)
        $user = auth()->user();
        
        $this->report->update([
            'is_verified' => true,
            'verified_by' => $user->employee->id ?? null,
            'verified_at' => now(),
        ]);

        session()->flash('success', 'Laporan berhasil diverifikasi.');
        $this->report->refresh();
    }

    public function render()
    {
        return view('livewire.reports.trip-report-show')
            ->layout('layouts.app', ['header' => 'Detail Laporan Perjalanan']);
    }
}
