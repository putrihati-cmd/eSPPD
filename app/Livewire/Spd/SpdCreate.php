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
    
    // Followers
    public array $followers = []; // Array of employee IDs
    public string $followerSearch = '';

    // Auto-fill readonly fields from logged-in user
    public bool $isOwnTrip = false;
    public string $autoFillName = '';
    public string $autoFillNip = '';
    public string $autoFillJabatan = '';
    public string $autoFillPangkat = '';
    public string $autoFillUnitKerja = '';
    public string $autoFillNoRekening = '';
    public string $autoFillBank = '';

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

    /**
     * Mount - Auto-fill form if the logged-in user has employee data
     */
    public function mount()
    {
        $user = auth()->user();
        
        // Check if user has associated employee record
        if ($user && $user->employee) {
            $employee = $user->employee;
            
            // Auto-fill the form with logged-in user's data
            $this->employee_id = $employee->id;
            $this->selectedEmployee = $employee;
            $this->isOwnTrip = true;
            
            // Populate readonly display fields
            $this->autoFillName = $employee->name ?? '';
            $this->autoFillNip = $employee->nip ?? '';
            $this->autoFillJabatan = $employee->position ?? '';
            $this->autoFillPangkat = $employee->rank ?? '';
            $this->autoFillUnitKerja = $employee->unit?->name ?? '';
            $this->autoFillNoRekening = $employee->bank_account ?? '';
            $this->autoFillBank = $employee->bank_name ?? '';
        }
    }


    public function rules()
    {
        return [
            'employee_id' => 'required',
            'followers' => 'array',
            'followers.*' => 'exists:employees,id',
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string',
            'departure_date' => 'required|date|after:today',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'transport_type' => 'required',
            'budget_id' => 'required',
        ];
    }
    
    public function addFollower($employeeId)
    {
        if (!in_array($employeeId, $this->followers) && $employeeId !== $this->employee_id) {
            $this->followers[] = $employeeId;
        }
        $this->followerSearch = '';
    }

    public function removeFollower($index)
    {
        unset($this->followers[$index]);
        $this->followers = array_values($this->followers);
    }
    
    public function getFollowersListProperty()
    {
        if (empty($this->followers)) {
            return collect();
        }
        return Employee::whereIn('id', $this->followers)->get();
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
        $estimatedCost = $this->calculateEstimatedCost();
        
        // Validate budget availability
        $budget = Budget::find($this->budget_id);
        if ($budget->available_budget < $estimatedCost) {
            session()->flash('error', 'Budget tidak mencukupi. Tersedia: Rp ' . number_format($budget->available_budget, 0, ',', '.') . ', Dibutuhkan: Rp ' . number_format($estimatedCost, 0, ',', '.'));
            return;
        }
        
        // Check for double booking (overlapping dates for same employee)
        $hasOverlap = Spd::where('employee_id', $this->employee_id)
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereBetween('departure_date', [$this->departure_date, $this->return_date])
                    ->orWhereBetween('return_date', [$this->departure_date, $this->return_date])
                    ->orWhere(function ($q) {
                        $q->where('departure_date', '<=', $this->departure_date)
                          ->where('return_date', '>=', $this->return_date);
                    });
            })
            ->exists();
            
        if ($hasOverlap) {
            session()->flash('error', 'Pegawai sudah memiliki SPD pada rentang tanggal tersebut.');
            return;
        }
        
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
            'estimated_cost' => $estimatedCost,
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
        
        // Save Followers
        foreach ($this->followers as $followerId) {
            $spd->followers()->create([
                'employee_id' => $followerId
            ]);
        }

        // Dispatch initial document generation for preview
        \App\Jobs\GenerateDocumentJob::dispatch($spd->id, 'spt');
        
        session()->flash('message', 'SPD berhasil dibuat!');

        return $this->redirect(route('spd.show', $spd), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();
        
        $employees = Employee::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $searchableEmployees = collect();
        if (strlen($this->followerSearch) >= 2) {
            $query = Employee::where('organization_id', $user->organization_id)
                ->where('is_active', true)
                ->where('name', 'like', '%' . $this->followerSearch . '%')
                ->whereNotIn('id', $this->followers)
                ->limit(5);
            
            // Only exclude employee_id if it's not empty (fix PostgreSQL UUID error)
            if (!empty($this->employee_id)) {
                $query->where('id', '!=', $this->employee_id);
            }
            
            $searchableEmployees = $query->get();
        }

        $budgets = Budget::where('organization_id', $user->organization_id)
            ->where('year', now()->year)
            ->where('is_active', true)
            ->get();

        return view('livewire.spd.spd-create', [
            'employees' => $employees,
            'searchableEmployees' => $searchableEmployees,
            'budgets' => $budgets,
        ])->layout('layouts.app', ['header' => 'Buat SPD Baru']);
    }
}
