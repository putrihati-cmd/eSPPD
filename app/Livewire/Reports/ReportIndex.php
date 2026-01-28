<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class ReportIndex extends Component
{
    public function render()
    {
        return view('livewire.reports.report-index')
            ->layout('layouts.app', ['header' => 'Laporan']);
    }
}
