<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Role & Permission Management</h1>
        <p class="text-slate-600 mt-1">Kelola role dan permission sistem eSPPD</p>
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

    <!-- Toolbar -->
    <div class="mb-6 flex gap-3">
        <button wire:click="openModal" class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Role</span>
        </button>
        <input type="text" wire:model.live="search" placeholder="Cari role..." class="px-4 py-2.5 rounded-xl border border-slate-200 flex-1" />
    </div>

    <!-- Roles Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Nama</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Label</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Level</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Permissions</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->roles as $role)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $role->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $role->label }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-sm font-bold">{{ $role->level }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <div class="flex flex-wrap gap-1">
                                @forelse($role->permissions as $perm)
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs">{{ $perm->name }}</span>
                                @empty
                                    <span class="text-slate-400 italic">No permissions</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <button wire:click="openModal({{ $role->id }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">Edit</button>
                                <button wire:click="delete({{ $role->id }})" onclick="return confirm('Yakin ingin menghapus role ini?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">Tidak ada role ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->roles->links() }}
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900">{{ $editingId ? 'Edit Role' : 'Tambah Role' }}</h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    @if (!$editingId)
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-2">Nama Role <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="Misal: admin, approver, user" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('name') border-red-500 @enderror" />
                            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Label <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="label" placeholder="Misal: Administrator" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('label') border-red-500 @enderror" />
                        @error('label') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Level <span class="text-red-500">*</span> (1-99)</label>
                        <input type="number" wire:model="level" min="1" max="99" placeholder="Level otorisasi" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('level') border-red-500 @enderror" />
                        @error('level') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Permissions</label>
                        <div class="grid grid-cols-2 gap-3 max-h-60 overflow-y-auto p-3 border border-slate-200 rounded-lg bg-slate-50">
                            @foreach($this->availablePermissions->groupBy('category') as $category => $perms)
                                <div class="col-span-2">
                                    <h4 class="font-semibold text-slate-900 text-sm mb-2">{{ $category }}</h4>
                                    <div class="space-y-2 ml-3">
                                        @foreach($perms as $perm)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" wire:model="permissions" value="{{ $perm->id }}" />
                                                <span class="text-sm text-slate-700">{{ $perm->label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 bg-slate-50 border-t border-slate-100 px-6 py-4 flex gap-3 justify-end">
                    <button wire:click="closeModal" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-lg font-medium transition-colors">Batal</button>
                    <button wire:click="save" class="px-4 py-2.5 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 rounded-lg font-bold transition-colors">{{ $editingId ? 'Perbarui' : 'Buat' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
