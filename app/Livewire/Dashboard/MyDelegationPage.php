<?php

namespace App\Livewire\Dashboard;

use App\Models\ApprovalDelegation;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class MyDelegationPage extends Component
{
    #[Validate('required|numeric|exists:users,id')]
    public int $delegate_id = 0;

    #[Validate('required|date|after:today')]
    public string $start_date = '';

    #[Validate('required|date|after:start_date')]
    public string $end_date = '';

    #[Validate('string')]
    public string $reason = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Computed]
    public function activeDelegations()
    {
        return ApprovalDelegation::query()
            ->where('delegator_id', auth()->id())
            ->with('delegate')
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get();
    }

    #[Computed]
    public function inactiveDelegations()
    {
        return ApprovalDelegation::query()
            ->where('delegator_id', auth()->id())
            ->with('delegate')
            ->where('is_active', false)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    #[Computed]
    public function stats()
    {
        return [
            'active' => $this->activeDelegations->count(),
            'inactive' => $this->inactiveDelegations->count(),
        ];
    }

    public function openModal(?int $delegationId = null): void
    {
        if ($delegationId) {
            $delegation = ApprovalDelegation::findOrFail($delegationId);
            if ($delegation->delegator_id !== auth()->id()) {
                abort(403);
            }
            $this->editingId = $delegation->id;
            $this->delegate_id = $delegation->delegate_id;
            $this->start_date = $delegation->start_date->format('Y-m-d');
            $this->end_date = $delegation->end_date->format('Y-m-d');
            $this->reason = $delegation->reason ?? '';
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['delegate_id', 'start_date', 'end_date', 'reason', 'editingId']);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $delegation = ApprovalDelegation::findOrFail($this->editingId);
            if ($delegation->delegator_id !== auth()->id()) {
                abort(403);
            }
            $delegation->update([
                'delegate_id' => $this->delegate_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'reason' => $this->reason,
            ]);
            session()->flash('success', 'Delegasi berhasil diperbarui');
        } else {
            ApprovalDelegation::create([
                'delegator_id' => auth()->id(),
                'delegate_id' => $this->delegate_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'reason' => $this->reason,
                'is_active' => true,
            ]);
            session()->flash('success', 'Delegasi berhasil dibuat');
        }

        $this->closeModal();
    }

    public function delete(int $delegationId): void
    {
        $delegation = ApprovalDelegation::findOrFail($delegationId);
        if ($delegation->delegator_id !== auth()->id()) {
            abort(403);
        }
        $delegation->delete();
        session()->flash('success', 'Delegasi berhasil dihapus');
    }

    public function toggleActive(int $delegationId): void
    {
        $delegation = ApprovalDelegation::findOrFail($delegationId);
        if ($delegation->delegator_id !== auth()->id()) {
            abort(403);
        }
        $delegation->update(['is_active' => !$delegation->is_active]);
        session()->flash('success', 'Status delegasi berhasil diubah');
    }

    public function render()
    {
        return view('livewire.dashboard.my-delegation-page');
    }
}
