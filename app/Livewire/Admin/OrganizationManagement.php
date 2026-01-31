<?php

namespace App\Livewire\Admin;

use App\Models\Organization;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class OrganizationManagement extends Component
{
    use WithPagination;

    public string $tab = 'organizations';

    #[Validate('required|string|unique:organizations,name')]
    public string $org_name = '';

    #[Validate('string')]
    public string $org_code = '';

    #[Validate('string')]
    public string $org_address = '';

    #[Validate('required|string|unique:units,name')]
    public string $unit_name = '';

    #[Validate('required|numeric|exists:organizations,id')]
    public int $organization_id = 0;

    #[Validate('string')]
    public string $unit_code = '';

    public string $search = '';
    public ?int $editingOrgId = null;
    public ?int $editingUnitId = null;
    public bool $showOrgModal = false;
    public bool $showUnitModal = false;

    #[Computed]
    public function organizations()
    {
        return Organization::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
            )
            ->withCount('units')
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function units()
    {
        return Unit::query()
            ->with('organization')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('organization_id', '=', $this->organization_id)
            )
            ->orderBy('name')
            ->paginate(10);
    }

    public function openOrgModal(?int $orgId = null): void
    {
        if ($orgId) {
            $org = Organization::findOrFail($orgId);
            $this->editingOrgId = $org->id;
            $this->org_name = $org->name;
            $this->org_code = $org->code ?? '';
            $this->org_address = $org->address ?? '';
        } else {
            $this->reset(['org_name', 'org_code', 'org_address', 'editingOrgId']);
        }
        $this->showOrgModal = true;
    }

    public function closeOrgModal(): void
    {
        $this->showOrgModal = false;
        $this->reset(['org_name', 'org_code', 'org_address', 'editingOrgId']);
    }

    public function saveOrg(): void
    {
        if ($this->editingOrgId) {
            $this->validateOnly(['org_name', 'org_code', 'org_address'], [
                'org_name' => 'required|string|unique:organizations,name,' . $this->editingOrgId,
            ]);

            Organization::findOrFail($this->editingOrgId)->update([
                'name' => $this->org_name,
                'code' => $this->org_code,
                'address' => $this->org_address,
            ]);

            session()->flash('success', 'Organisasi berhasil diperbarui');
        } else {
            $this->validateOnly(['org_name', 'org_code', 'org_address']);

            Organization::create([
                'name' => $this->org_name,
                'code' => $this->org_code,
                'address' => $this->org_address,
            ]);

            session()->flash('success', 'Organisasi berhasil dibuat');
        }

        $this->closeOrgModal();
    }

    public function deleteOrg(int $orgId): void
    {
        if (Organization::find($orgId)->units()->count() > 0) {
            session()->flash('error', 'Organisasi tidak dapat dihapus karena masih memiliki unit');
            return;
        }

        Organization::findOrFail($orgId)->delete();
        session()->flash('success', 'Organisasi berhasil dihapus');
    }

    public function openUnitModal(?int $unitId = null): void
    {
        if ($unitId) {
            $unit = Unit::findOrFail($unitId);
            $this->editingUnitId = $unit->id;
            $this->unit_name = $unit->name;
            $this->organization_id = $unit->organization_id;
            $this->unit_code = $unit->code ?? '';
        } else {
            $this->reset(['unit_name', 'unit_code', 'organization_id', 'editingUnitId']);
        }
        $this->showUnitModal = true;
    }

    public function closeUnitModal(): void
    {
        $this->showUnitModal = false;
        $this->reset(['unit_name', 'unit_code', 'organization_id', 'editingUnitId']);
    }

    public function saveUnit(): void
    {
        if ($this->editingUnitId) {
            $this->validateOnly(['unit_name', 'organization_id', 'unit_code'], [
                'unit_name' => 'required|string|unique:units,name,' . $this->editingUnitId,
            ]);

            Unit::findOrFail($this->editingUnitId)->update([
                'name' => $this->unit_name,
                'organization_id' => $this->organization_id,
                'code' => $this->unit_code,
            ]);

            session()->flash('success', 'Unit berhasil diperbarui');
        } else {
            $this->validate(['unit_name', 'organization_id', 'unit_code']);

            Unit::create([
                'name' => $this->unit_name,
                'organization_id' => $this->organization_id,
                'code' => $this->unit_code,
            ]);

            session()->flash('success', 'Unit berhasil dibuat');
        }

        $this->closeUnitModal();
    }

    public function deleteUnit(int $unitId): void
    {
        Unit::findOrFail($unitId)->delete();
        session()->flash('success', 'Unit berhasil dihapus');
    }

    public function render()
    {
        return view('livewire.admin.organization-management');
    }
}
