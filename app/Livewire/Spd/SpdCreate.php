<?php

namespace App\Livewire\Spd;

use App\Models\Budget;
use App\Models\Employee;
use App\Models\Spd;
use Livewire\Component;

class SpdCreate extends Component
{
    public int $step = 1;
    
    // Step 1: Employee selection
    public string $employee_id = '';
    public ?Employee $selectedEmployee = null;

    // Step 2: Travel details
    public string $destination = '';
    public string $purpose = '';
    public string $invitation_number = '';
    public string $departure_date = '';
    public string $return_date = '';
    public string $transport_type = 'pesawat';
    public bool $needs_accommodation = true;

    // Step 3: Budget selection
    public string $budget_id = '';

    public function rules()
    {
        return [
            'employee_id' => 'required',
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string',
            'departure_date' => 'required|date|after:today',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'transport_type' => 'required',
            'budget_id' => 'required',
        ];
    }

    public function updatedEmployeeId($value)
    {
        $this->selectedEmployee = Employee::find($value);
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validate(['employee_id' => 'required']);
        } elseif ($this->step === 2) {
            $this->validate([
                'destination' => 'required|string|max:255',
                'purpose' => 'required|string',
                'departure_date' => 'required|date',
                'return_date' => 'required|date|after_or_equal:departure_date',
            ]);
        }
        
        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function calculateDuration(): int
    {
        if (!$this->departure_date || !$this->return_date) {
            return 0;
        }
        
        $departure = new \DateTime($this->departure_date);
        $return = new \DateTime($this->return_date);
        return $departure->diff($return)->days + 1;
    }

    public function calculateEstimatedCost(): float
    {
        // Simplified calculation - would be more complex with SBM lookup
        $duration = $this->calculateDuration();
        $dailyAllowance = 380000; // Default Jawa Tengah rate
        $accommodation = $this->needs_accommodation ? 700000 * max(0, $duration - 1) : 0;
        $transport = 1500000; // Estimated round-trip
        
        return ($dailyAllowance * $duration) + $accommodation + $transport;
    }

    public function submit()
    {
        $this->validate();

        $user = auth()->user();
        $duration = $this->calculateDuration();
        $year = date('Y');
        
        // Generate SPT and SPD numbers
        $lastSpd = Spd::whereYear('created_at', $year)->latest()->first();
        $counter = $lastSpd ? intval(substr($lastSpd->spt_number, 0, 4)) + 1 : 1;
        
        $sptNumber = str_pad($counter, 4, '0', STR_PAD_LEFT) . "/Un.19/K.AUPK/SPT/01/{$year}";
        $spdNumber = str_pad($counter, 4, '0', STR_PAD_LEFT) . "/Un.19/K.AUPK/SPD/01/{$year}";

        $spd = Spd::create([
            'organization_id' => $user->organization_id,
            'unit_id' => $this->selectedEmployee->unit_id,
            'employee_id' => $this->employee_id,
            'spt_number' => $sptNumber,
            'spd_number' => $spdNumber,
            'destination' => $this->destination,
            'purpose' => $this->purpose,
            'invitation_number' => $this->invitation_number,
            'departure_date' => $this->departure_date,
            'return_date' => $this->return_date,
            'duration' => $duration,
            'budget_id' => $this->budget_id,
            'estimated_cost' => $this->calculateEstimatedCost(),
            'transport_type' => $this->transport_type,
            'needs_accommodation' => $this->needs_accommodation,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        // Create cost items
        $dailyAllowance = 380000;
        $spd->costs()->create([
            'category' => 'uang_harian',
            'description' => "Uang Harian {$duration} hari",
            'estimated_amount' => $dailyAllowance * $duration,
        ]);

        if ($this->needs_accommodation && $duration > 1) {
            $spd->costs()->create([
                'category' => 'penginapan',
                'description' => "Penginapan " . ($duration - 1) . " malam",
                'estimated_amount' => 700000 * ($duration - 1),
            ]);
        }

        $spd->costs()->create([
            'category' => 'transport',
            'description' => 'Transportasi PP',
            'estimated_amount' => 1500000,
        ]);

        return $this->redirect(route('spd.show', $spd), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();
        
        $employees = Employee::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $budgets = Budget::where('organization_id', $user->organization_id)
            ->where('year', now()->year)
            ->where('is_active', true)
            ->get();

        return view('livewire.spd.spd-create', [
            'employees' => $employees,
            'budgets' => $budgets,
        ])->layout('layouts.app', ['header' => 'Buat SPD Baru']);
    }
}
