<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class SettingsIndex extends Component
{
    public function render()
    {
        return view('livewire.settings.settings-index')
            ->layout('layouts.app', ['header' => 'Pengaturan']);
    }
}
