<?php

namespace App\Livewire\Reports;

use App\Models\Spd;
use App\Models\TripReport;
use App\Models\TripActivity;
use App\Models\TripOutput;
use Livewire\Component;
use Livewire\WithFileUploads;

class TripReportCreate extends Component
{
    use WithFileUploads;

    public Spd $spd;
    public $actual_departure_date;
    public $actual_return_date;
    public $activities = [];
    public $outputs = [];
    public $attachments = [];

    public function mount(Spd $spd)
    {
        // Check if SPD is approved
        if ($spd->approval_status !== 'approved') {
            session()->flash('error', 'SPD belum disetujui.');
            return redirect()->route('spd.show', $spd);
        }

        // Check if report already exists
        if ($spd->tripReport) {
            return redirect()->route('reports.show', $spd->tripReport);
        }

        $this->spd = $spd;
        $this->actual_departure_date = $spd->departure_date->format('Y-m-d');
        $this->actual_return_date = $spd->return_date->format('Y-m-d');
        
        // Initialize with one activity and output
        $this->activities = [['date' => '', 'time' => '', 'location' => '', 'description' => '']];
        $this->outputs = [['description' => '']];
    }

    public function addActivity()
    {
        $this->activities[] = ['date' => '', 'time' => '', 'location' => '', 'description' => ''];
    }

    public function removeActivity($index)
    {
        unset($this->activities[$index]);
        $this->activities = array_values($this->activities);
    }

    public function addOutput()
    {
        $this->outputs[] = ['description' => ''];
    }

    public function removeOutput($index)
    {
        unset($this->outputs[$index]);
        $this->outputs = array_values($this->outputs);
    }

    public function save()
    {
        $this->validate([
            'actual_departure_date' => 'required|date',
            'actual_return_date' => 'required|date|after_or_equal:actual_departure_date',
            'activities' => 'required|array|min:1',
            'activities.*.date' => 'required|date',
            'activities.*.time' => 'required|string',
            'activities.*.location' => 'required|string',
            'activities.*.description' => 'required|string',
            'outputs' => 'required|array|min:1',
            'outputs.*.description' => 'required|string',
        ]);

        // Calculate actual duration
        $actualDuration = \Carbon\Carbon::parse($this->actual_departure_date)
            ->diffInDays(\Carbon\Carbon::parse($this->actual_return_date)) + 1;

        // Create report
        $report = TripReport::create([
            'spd_id' => $this->spd->id,
            'employee_id' => $this->spd->employee_id,
            'actual_departure_date' => $this->actual_departure_date,
            'actual_return_date' => $this->actual_return_date,
            'actual_duration' => $actualDuration,
        ]);

        // Save activities
        foreach ($this->activities as $index => $activity) {
            TripActivity::create([
                'report_id' => $report->id,
                'date' => $activity['date'],
                'time' => $activity['time'],
                'location' => $activity['location'],
                'description' => $activity['description'],
                'order' => $index,
            ]);
        }

        // Save outputs
        foreach ($this->outputs as $index => $output) {
            TripOutput::create([
                'report_id' => $report->id,
                'description' => $output['description'],
                'order' => $index,
            ]);
        }

        session()->flash('success', 'Laporan perjalanan berhasil disimpan sebagai draft.');
        return redirect()->route('reports.show', $report);
    }

    public function render()
    {
        return view('livewire.reports.trip-report-create')
            ->layout('layouts.app', ['header' => 'Buat Laporan Perjalanan']);
    }
}
