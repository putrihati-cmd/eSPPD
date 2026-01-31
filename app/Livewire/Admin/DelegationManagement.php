<?php

namespace App\Livewire\Admin;

use App\Models\ApprovalDelegation;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class DelegationManagement extends Component
{
    use WithPagination;

    #[Validate('required|numeric|exists:users,id')]
    public int $delegator_id = 0;

    #[Validate('required|numeric|exists:users,id')]
    public int $delegate_id = 0;

    #[Validate('required|date|after:today')]
    public string $start_date = '';

    #[Validate('required|date|after:start_date')]
    public string $end_date = '';

    #[Validate('string')]
    public string $reason = '';

    public string $search = '';
    public ?int $editingId = null;
    public bool $showModal = false;

    #[Computed]
    public function delegations()
    {
        return ApprovalDelegation::query()
            ->with(['delegator', 'delegate'])
            ->when($this->search, fn($q) =>
                $q->whereHas('delegator', fn($sq) =>
                    $sq->where('name', 'like', "%{$this->search}%")
                )
                ->orWhereHas('delegate', fn($sq) =>
                    $sq->where('name', 'like', "%{$this->search}%")
                )
            )
            ->orderBy('start_date', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }

    public function openModal(?int $delegationId = null): void
    {
        if ($delegationId) {
            $delegation = ApprovalDelegation::findOrFail($delegationId);
            $this->editingId = $delegation->id;
            $this->delegator_id = $delegation->delegator_id;
            $this->delegate_id = $delegation->delegate_id;
            $this->start_date = $delegation->start_date->format('Y-m-d');
            $this->end_date = $delegation->end_date->format('Y-m-d');
            $this->reason = $delegation->reason ?? '';
        } else {
            $this->reset(['delegator_id', 'delegate_id', 'start_date', 'end_date', 'reason', 'editingId']);
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['delegator_id', 'delegate_id', 'start_date', 'end_date', 'reason', 'editingId']);
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            ApprovalDelegation::findOrFail($this->editingId)->update([
                'delegator_id' => $this->delegator_id,
                'delegate_id' => $this->delegate_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'reason' => $this->reason,
            ]);

            session()->flash('success', 'Delegasi berhasil diperbarui');
        } else {
            ApprovalDelegation::create([
                'delegator_id' => $this->delegator_id,
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
        ApprovalDelegation::findOrFail($delegationId)->delete();
        session()->flash('success', 'Delegasi berhasil dihapus');
    }

    public function toggleActive(int $delegationId): void
    {
        $delegation = ApprovalDelegation::findOrFail($delegationId);
        $delegation->update(['is_active' => !$delegation->is_active]);
        session()->flash('success', 'Status delegasi berhasil diubah');
    }

    public function render()
    {
        return view('livewire.admin.delegation-management');
    }
}
