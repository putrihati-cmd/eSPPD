<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class UserManagement extends Component
{
    use WithPagination;

    #[Validate('required|string')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|unique:users,nip')]
    public string $nip = '';

    #[Validate('required|numeric|exists:roles,id')]
    public int $role_id = 0;

    #[Validate('numeric|exists:organizations,id')]
    public ?int $organization_id = null;

    #[Validate('required|string|min:8')]
    public string $password = '';

    public string $search = '';
    public ?int $editingId = null;
    public bool $showModal = false;

    #[Computed]
    public function users()
    {
        return User::query()
            ->with(['roleModel', 'organization'])
            ->when($this->search, fn($q) => 
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('nip', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function roles()
    {
        return Role::orderBy('level', 'desc')->get();
    }

    #[Computed]
    public function organizations()
    {
        return Organization::orderBy('name')->get();
    }

    public function openModal(?int $userId = null): void
    {
        if ($userId) {
            $user = User::findOrFail($userId);
            $this->editingId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->nip = $user->nip;
            $this->role_id = $user->role_id;
            $this->organization_id = $user->organization_id;
            $this->password = '';
            
            $this->validateOnly(['name', 'email', 'nip', 'role_id', 'organization_id'], [
                'email' => 'required|email|unique:users,email,' . $userId,
                'nip' => 'required|string|unique:users,nip,' . $userId,
            ]);
        } else {
            $this->reset(['name', 'email', 'nip', 'role_id', 'organization_id', 'password', 'editingId']);
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'nip', 'role_id', 'organization_id', 'password', 'editingId']);
    }

    public function save(): void
    {
        if ($this->editingId) {
            $this->validateOnly(['name', 'role_id', 'organization_id'], [
                'email' => 'required|email|unique:users,email,' . $this->editingId,
                'nip' => 'required|string|unique:users,nip,' . $this->editingId,
            ]);

            $user = User::findOrFail($this->editingId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'nip' => $this->nip,
                'role_id' => $this->role_id,
                'organization_id' => $this->organization_id,
            ]);

            if ($this->password) {
                $user->update(['password' => bcrypt($this->password)]);
            }

            session()->flash('success', 'User berhasil diperbarui');
        } else {
            $this->validate();

            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'nip' => $this->nip,
                'role_id' => $this->role_id,
                'organization_id' => $this->organization_id,
                'password' => bcrypt($this->password),
            ]);

            session()->flash('success', 'User berhasil dibuat');
        }

        $this->closeModal();
    }

    public function delete(int $userId): void
    {
        User::findOrFail($userId)->delete();
        session()->flash('success', 'User berhasil dihapus');
    }

    public function render()
    {
        return view('livewire.admin.user-management');
    }
}
