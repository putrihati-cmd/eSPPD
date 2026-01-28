<?php

namespace App\Livewire\Spd;

use App\Models\Spd;
use Livewire\Component;
use Livewire\WithPagination;

class SpdIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        
        $spds = Spd::where('organization_id', $user->organization_id)
            ->with(['employee', 'budget', 'unit'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('destination', 'like', "%{$this->search}%")
                        ->orWhere('spt_number', 'like', "%{$this->search}%")
                        ->orWhere('purpose', 'like', "%{$this->search}%")
                        ->orWhereHas('employee', function ($eq) {
                            $eq->where('name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.spd.spd-index', [
            'spds' => $spds,
        ])->layout('layouts.app', ['header' => 'Daftar SPD']);
    }
}
