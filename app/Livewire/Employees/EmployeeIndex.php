<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        
        $employees = Employee::where('organization_id', $user->organization_id)
            ->with(['unit'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nip', 'like', "%{$this->search}%")
                        ->orWhere('position', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.employees.employee-index', [
            'employees' => $employees,
        ])->layout('layouts.app', ['header' => 'Data Pegawai']);
    }
}
