<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class RoleManagement extends Component
{
    use WithPagination;

    #[Validate('required|string|unique:roles,name')]
    public string $name = '';

    #[Validate('required|string')]
    public string $label = '';

    #[Validate('required|numeric|min:1|max:99')]
    public int $level = 0;

    #[Validate('array')]
    public array $permissions = [];

    public string $search = '';
    public ?int $editingId = null;
    public bool $showModal = false;

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('label', 'like', "%{$this->search}%")
            )
            ->orderBy('level', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function availablePermissions()
    {
        return Permission::orderBy('category')->get();
    }

    public function openModal(?int $roleId = null): void
    {
        if ($roleId) {
            $role = Role::with('permissions')->findOrFail($roleId);
            $this->editingId = $role->id;
            $this->name = $role->name;
            $this->label = $role->label;
            $this->level = $role->level;
            $this->permissions = $role->permissions->pluck('id')->toArray();
        } else {
            $this->reset(['name', 'label', 'level', 'permissions', 'editingId']);
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'label', 'level', 'permissions', 'editingId']);
    }

    public function save(): void
    {
        if ($this->editingId) {
            $this->validateOnly(['label', 'level', 'permissions'], [
                'name' => 'required|string|unique:roles,name,' . $this->editingId,
            ]);

            $role = Role::findOrFail($this->editingId);
            $role->update([
                'label' => $this->label,
                'level' => $this->level,
            ]);
            $role->permissions()->sync($this->permissions);

            session()->flash('success', 'Role berhasil diperbarui');
        } else {
            $this->validate();

            $role = Role::create([
                'name' => strtolower(str_replace(' ', '_', $this->name)),
                'label' => $this->label,
                'level' => $this->level,
            ]);
            $role->permissions()->sync($this->permissions);

            session()->flash('success', 'Role berhasil dibuat');
        }

        $this->closeModal();
    }

    public function delete(int $roleId): void
    {
        if (Role::find($roleId)->users()->count() > 0) {
            session()->flash('error', 'Role tidak dapat dihapus karena masih digunakan');
            return;
        }

        Role::findOrFail($roleId)->delete();
        session()->flash('success', 'Role berhasil dihapus');
    }

    public function render()
    {
        return view('livewire.admin.role-management');
    }
}
