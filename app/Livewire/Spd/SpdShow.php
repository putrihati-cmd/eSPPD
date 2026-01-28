<?php

namespace App\Livewire\Spd;

use App\Models\Spd;
use Livewire\Component;

class SpdShow extends Component
{
    public Spd $spd;

    public function mount(Spd $spd)
    {
        $this->spd = $spd->load(['employee', 'unit', 'budget', 'costs', 'approvals.approver']);
    }

    public function render()
    {
        return view('livewire.spd.spd-show')
            ->layout('layouts.app', ['header' => 'Detail SPD']);
    }
}
