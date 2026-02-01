# 🗂️ eSPPD Code Structure & Patterns Reference

**Date**: Current Session  
**Purpose**: Quick reference for understanding existing code patterns and conventions

---

## 📌 Quick Navigation

- [Livewire Component Pattern](#livewire-component-pattern)
- [Admin CRUD Pattern](#admin-crud-pattern)
- [Dashboard Page Pattern](#dashboard-page-pattern)
- [Service Layer Pattern](#service-layer-pattern)
- [Model Pattern](#model-pattern)
- [Blade Template Pattern](#blade-template-pattern)
- [Form Validation Pattern](#form-validation-pattern)
- [Authorization Pattern](#authorization-pattern)
- [File Locations](#file-locations)

---

## 🧩 Livewire Component Pattern

### Basic Component Structure

**File**: `app/Livewire/FeatureName/ComponentName.php`

```php
<?php

namespace App\Livewire\FeatureName;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Models\Model;

class ComponentName extends Component
{
    // Public properties - synced with frontend
    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;
    
    // Validation attributes
    #[Validate('required|string')]
    public string $name = '';
    
    #[Validate('required|email')]
    public string $email = '';
    
    // Computed properties - cached until dependencies change
    #[Computed]
    public function items()
    {
        return Model::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    
    // Event handlers
    public function mount(): void
    {
        // Initialize component
    }
    
    public function openModal(?int $id = null): void
    {
        if ($id) {
            // Edit mode
            $item = Model::findOrFail($id);
            $this->editingId = $item->id;
            $this->name = $item->name;
            $this->email = $item->email;
        } else {
            // Create mode
            $this->reset(['name', 'email', 'editingId']);
        }
        $this->showModal = true;
    }
    
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'editingId']);
    }
    
    public function save(): void
    {
        $this->validate();
        
        if ($this->editingId) {
            $item = Model::findOrFail($this->editingId);
            $item->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);
            session()->flash('success', 'Item updated successfully');
        } else {
            Model::create([
                'name' => $this->name,
                'email' => $this->email,
            ]);
            session()->flash('success', 'Item created successfully');
        }
        
        $this->closeModal();
    }
    
    public function delete(int $id): void
    {
        Model::findOrFail($id)->delete();
        session()->flash('success', 'Item deleted successfully');
    }
    
    public function render()
    {
        return view('livewire.feature-name.component-name');
    }
}
```

### Key Patterns

1. **Computed Properties**: Use `#[Computed]` for data fetching
   - Automatically cached until properties change
   - Prevents N+1 queries
   - Eager load relationships

2. **Validation**: Use `#[Validate]` attributes
   - Auto-resets after successful save
   - Can use `validateOnly()` for specific fields

3. **Modal Pattern**: 
   - `showModal` boolean to toggle visibility
   - `editingId` to track edit mode
   - `openModal()` / `closeModal()` methods

4. **Search Pattern**:
   - `$search` property with `wire:model.live`
   - Use `.when()` in query builder for conditional searching
   - Search multiple fields with `.orWhere()`

5. **CRUD Methods**:
   - `save()` - Create or update
   - `delete()` - Delete item
   - `openModal()` / `closeModal()` - Toggle UI

---

## 📋 Admin CRUD Pattern

### Example: UserManagement

**Component**: `app/Livewire/Admin/UserManagement.php`  
**View**: `resources/views/livewire/admin/user-management.blade.php`

### Component Class

```php
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
    use WithPagination;  // Enable pagination

    // Form fields with validation
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

    // UI State
    public string $search = '';
    public ?int $editingId = null;
    public bool $showModal = false;

    // Computed properties for data fetching
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

    // CRUD Methods
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
        $this->validate();

        if ($this->editingId) {
            // Update
            User::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'email' => $this->email,
                'nip' => $this->nip,
                'role_id' => $this->role_id,
                'organization_id' => $this->organization_id,
                'password' => $this->password ? bcrypt($this->password) : null,
            ]);
            session()->flash('success', 'User updated successfully');
        } else {
            // Create
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'nip' => $this->nip,
                'role_id' => $this->role_id,
                'organization_id' => $this->organization_id,
                'password' => bcrypt($this->password),
            ]);
            session()->flash('success', 'User created successfully');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        User::findOrFail($id)->delete();
        session()->flash('success', 'User deleted successfully');
    }

    public function render()
    {
        return view('livewire.admin.user-management');
    }
}
```

### View Template

```blade
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">User Management</h1>
        <p class="text-slate-600 mt-1">Kelola pengguna sistem eSPPD</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    <!-- Toolbar -->
    <div class="mb-6 flex gap-3">
        <button wire:click="openModal" class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah User</span>
        </button>
        <input type="text" wire:model.live="search" placeholder="Cari user..." class="px-4 py-2.5 rounded-xl border border-slate-200 flex-1" />
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Nama</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">NIP</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Role</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->users as $user)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->nip }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                {{ $user->roleModel?->label ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <button wire:click="openModal({{ $user->id }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $user->id }})" onclick="return confirm('Yakin?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            Tidak ada user ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->users->links() }}
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ $editingId ? 'Edit User' : 'Tambah User' }}
                    </h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="name" placeholder="Nama lengkap" 
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('name') border-red-500 @enderror" />
                        @error('name') 
                            <span class="text-xs text-red-600">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                            NIP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="nip" placeholder="Nomor Induk Pegawai"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('nip') border-red-500 @enderror" />
                        @error('nip')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model="email" placeholder="Email address"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('email') border-red-500 @enderror" />
                        @error('email')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="role_id" 
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('role_id') border-red-500 @enderror">
                            <option value="">Pilih Role</option>
                            @foreach($this->roles as $role)
                                <option value="{{ $role->id }}">{{ $role->label }}</option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Password</label>
                        <input type="password" wire:model="password" 
                            placeholder="{{ $editingId ? '(Leave blank to keep current)' : 'Min 8 characters' }}"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('password') border-red-500 @enderror" />
                        @error('password')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold py-2.5 rounded-lg transition-all">
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                        <button type="button" wire:click="closeModal" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-900 font-bold py-2.5 rounded-lg transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
```

---

## 📊 Dashboard Page Pattern

### Example: ApprovalStatusPage

**Component**: `app/Livewire/Dashboard/ApprovalStatusPage.php`  
**View**: `resources/views/livewire/dashboard/approval-status-page.blade.php`

### Component Class

```php
<?php

namespace App\Livewire\Dashboard;

use App\Models\Spd;
use App\Models\Approval;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ApprovalStatusPage extends Component
{
    public ?int $selectedSpdId = null;
    public string $searchQuery = '';

    #[Computed]
    public function pendingApprovals()
    {
        return Approval::query()
            ->with(['spd', 'approver'])
            ->where('status', 'pending')
            ->when($this->searchQuery, fn($q) =>
                $q->whereHas('spd', fn($sq) =>
                    $sq->where('spt_number', 'like', "%{$this->searchQuery}%")
                      ->orWhere('spd_number', 'like', "%{$this->searchQuery}%")
                )
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function stats()
    {
        $userId = auth()->id();
        return [
            'pending' => Approval::where('status', 'pending')
                ->whereHas('spd', fn($q) => $q->where('user_id', $userId))
                ->count(),
            'approved' => Approval::where('status', 'approved')
                ->whereHas('spd', fn($q) => $q->where('user_id', $userId))
                ->count(),
            'rejected' => Approval::where('status', 'rejected')
                ->whereHas('spd', fn($q) => $q->where('user_id', $userId))
                ->count(),
        ];
    }

    public function selectSpd(int $spdId): void
    {
        $this->selectedSpdId = $spdId;
    }

    public function render()
    {
        return view('livewire.dashboard.approval-status-page');
    }
}
```

---

## 🛠️ Service Layer Pattern

### Example: ApprovalService

**File**: `app/Services/ApprovalService.php`

```php
<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Spd;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    /**
     * Process approval workflow for SPD
     */
    public function process(Spd $spd, string $action, ?string $notes = null): bool
    {
        $currentApproval = $spd->getPendingApproval();

        if (!$currentApproval) {
            return false;
        }

        if ($action === 'approve') {
            $this->approve($currentApproval, $notes);
            $this->checkAndProceed($spd, $currentApproval);
        } elseif ($action === 'reject') {
            $this->reject($currentApproval, $notes);
            $spd->update([
                'status' => 'rejected',
                'rejection_reason' => $notes,
            ]);
        }

        return true;
    }

    /**
     * Approve an approval step
     */
    protected function approve(Approval $approval, ?string $notes = null): void
    {
        $approval->update([
            'status' => 'approved',
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        Log::info("Approval approved: {$approval->id}");
    }

    /**
     * Reject an approval step
     */
    protected function reject(Approval $approval, ?string $notes = null): void
    {
        $approval->update([
            'status' => 'rejected',
            'notes' => $notes,
        ]);

        Log::info("Approval rejected: {$approval->id}");
    }

    /**
     * Check if all approvals done and proceed
     */
    protected function checkAndProceed(Spd $spd, ?Approval $lastApproval = null): void
    {
        $pendingCount = $spd->approvals()->where('status', 'pending')->count();

        if ($pendingCount === 0) {
            $spd->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            Log::info("SPD fully approved: {$spd->id}");
        }
    }
}
```

---

## 📝 Model Pattern

### Example: Spd Model

**File**: `app/Models/Spd.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spd extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'employee_id',
        'destination',
        'purpose',
        'departure_date',
        'return_date',
        'status',
        'current_approver_nip',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    // Query Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Methods
    public function getPendingApproval(): ?Approval
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->first();
    }

    public function isFullyApproved(): bool
    {
        return $this->approvals()
            ->where('status', '!=', 'approved')
            ->count() === 0;
    }
}
```

---

## 🎨 Blade Template Pattern

### Common Template Structure

```blade
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">{{ $title }}</h1>
        <p class="text-slate-600 mt-1">{{ $description }}</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Content Container -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <!-- Your content here -->
    </div>
</div>
```

---

## 🔒 Form Validation Pattern

### In Component

```php
#[Validate('required|string|max:255')]
public string $name = '';

#[Validate('required|email|unique:users,email')]
public string $email = '';

#[Validate('required|numeric|min:0')]
public int $amount = 0;

// Validation for edit (ignore current record)
public function save(): void
{
    $this->validate([
        'email' => 'required|email|unique:users,email,' . $this->editingId,
        'nip' => 'required|unique:users,nip,' . $this->editingId,
    ]);
    
    // Save logic
}

// Real-time validation (specific field)
public function updatedEmail($value)
{
    $this->validateOnly('email');
}
```

### In Blade Template

```blade
<div class="form-group">
    <label class="block text-sm font-semibold text-slate-900 mb-2">
        Email <span class="text-red-500">*</span>
    </label>
    
    <input type="email" wire:model="email" 
        class="w-full px-4 py-2.5 border rounded-lg transition-colors
                @error('email') border-red-500 bg-red-50 @else border-slate-200 @enderror" />
    
    @error('email')
        <span class="block text-xs text-red-600 mt-1">{{ $message }}</span>
    @enderror
    
    @if (!$errors->has('email') && !empty($email))
        <span class="block text-xs text-green-600 mt-1">✓ Valid</span>
    @endif
</div>
```

---

## 🔐 Authorization Pattern

### Using RbacService

```php
// Check single permission
if (RbacService::userHasPermission(auth()->user(), 'spd.approve')) {
    // User can approve
}

// Check any permission
if (RbacService::userHasAnyPermission(auth()->user(), ['spd.approve', 'spd.reject'])) {
    // User can approve or reject
}

// Check budget approval limit
if (RbacService::canApproveAmount(auth()->user(), $amount)) {
    // User can approve this amount
}
```

### In Blade Template

```blade
@can('has-permission:spd.approve')
    <button>Approve</button>
@endcan

@if (auth()->user()->can('approve-amount', $spd->estimated_cost))
    <button>Approve</button>
@else
    <p class="text-red-600">Your approval limit is exceeded</p>
@endif
```

---

## 📂 File Locations

### Quick Reference

```
Component Class      → app/Livewire/[Feature]/[ComponentName].php
Component View       → resources/views/livewire/[feature]/[component-name].blade.php
Service Class        → app/Services/[ServiceName].php
Model Class          → app/Models/[ModelName].php
Migration            → database/migrations/[timestamp]_create_[table_name]_table.php
Policy               → app/Policies/[ModelName]Policy.php
Controller           → app/Http/Controllers/[ControllerName].php
Request Validation   → app/Http/Requests/[Request name].php
Middleware           → app/Http/Middleware/[MiddlewareName].php
Livewire Form        → app/Livewire/Forms/[FormName].php
Route                → routes/web.php or routes/api.php
```

### Example Full Path

**UserManagement CRUD**:
- Component: `app/Livewire/Admin/UserManagement.php`
- View: `resources/views/livewire/admin/user-management.blade.php`
- Route: `routes/web.php` (line ~250)
- Policy: `app/Policies/UserPolicy.php`
- Model: `app/Models/User.php`

---

## 🎯 Common Tasks & Code Snippets

### Add New Admin CRUD Page

1. Create Livewire component in `app/Livewire/Admin/`
2. Follow AdminCRUD pattern (search, pagination, modal)
3. Create view in `resources/views/livewire/admin/`
4. Register route in `routes/web.php`
5. Add menu link in layout

### Add New Dashboard Widget

1. Create Livewire component in `app/Livewire/Dashboard/`
2. Create computed property for data fetching
3. Create view with stats/charts
4. Include in dashboard or make standalone page

### Add New Service

1. Create file in `app/Services/[ServiceName].php`
2. Add namespace and dependencies
3. Create public methods for business logic
4. Use from components/controllers via dependency injection

### Add New Model Relationship

```php
// In Model class
public function relationName(): HasMany|BelongsTo|etc
{
    return $this->hasMany(RelatedModel::class);
}

// Access in code
$model->relationName()      // Get relationship query
$model->relationName       // Get loaded relationship
```

---

**End of Code Structure & Patterns Reference**

Use this as a quick reference when implementing new features or modifying existing code.
