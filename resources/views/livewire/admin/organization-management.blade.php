<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Organization & Unit Management</h1>
        <p class="text-slate-600 mt-1">Kelola organisasi dan unit struktural</p>
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

    <!-- Tabs -->
    <div class="mb-6 flex gap-2 border-b border-slate-200">
        <button wire:click="$set('tab', 'organizations')" class="px-4 py-3 border-b-2 font-semibold transition-colors {{ $tab === 'organizations' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
            Organisasi
        </button>
        <button wire:click="$set('tab', 'units')" class="px-4 py-3 border-b-2 font-semibold transition-colors {{ $tab === 'units' ? 'border-brand-teal text-brand-teal' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
            Unit
        </button>
    </div>

    <!-- Organizations Tab -->
    @if ($tab === 'organizations')
        <!-- Toolbar -->
        <div class="mb-6 flex gap-3">
            <button wire:click="openOrgModal" class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Organisasi</span>
            </button>
            <input type="text" wire:model.live="search" placeholder="Cari organisasi..." class="px-4 py-2.5 rounded-xl border border-slate-200 flex-1" />
        </div>

        <!-- Organizations Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Nama</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Alamat</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Unit</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->organizations as $org)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $org->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $org->code ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $org->address ?? '-' }}</td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-slate-900">{{ $org->units_count }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-2 justify-center">
                                    <button wire:click="openOrgModal({{ $org->id }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">Edit</button>
                                    <button wire:click="deleteOrg({{ $org->id }})" onclick="return confirm('Yakin ingin menghapus organisasi ini?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Tidak ada organisasi ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $this->organizations->links() }}
        </div>
    @endif

    <!-- Units Tab -->
    @if ($tab === 'units')
        <!-- Toolbar -->
        <div class="mb-6 flex gap-3">
            <button wire:click="openUnitModal" class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Unit</span>
            </button>
            <input type="text" wire:model.live="search" placeholder="Cari unit..." class="px-4 py-2.5 rounded-xl border border-slate-200 flex-1" />
        </div>

        <!-- Units Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Nama</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Organisasi</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->units as $unit)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $unit->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $unit->code ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $unit->organization?->name }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-2 justify-center">
                                    <button wire:click="openUnitModal({{ $unit->id }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">Edit</button>
                                    <button wire:click="deleteUnit({{ $unit->id }})" onclick="return confirm('Yakin ingin menghapus unit ini?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">Tidak ada unit ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $this->units->links() }}
        </div>
    @endif

    <!-- Organization Modal -->
    @if ($showOrgModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900">{{ $editingOrgId ? 'Edit Organisasi' : 'Tambah Organisasi' }}</h2>
                    <button wire:click="closeOrgModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Nama <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="org_name" placeholder="Nama organisasi" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('org_name') border-red-500 @enderror" />
                        @error('org_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Kode</label>
                        <input type="text" wire:model="org_code" placeholder="Kode organisasi" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Alamat</label>
                        <textarea wire:model="org_address" placeholder="Alamat organisasi" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg"></textarea>
                    </div>
                </div>

                <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex gap-3 justify-end">
                    <button wire:click="closeOrgModal" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-lg font-medium transition-colors">Batal</button>
                    <button wire:click="saveOrg" class="px-4 py-2.5 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 rounded-lg font-bold transition-colors">{{ $editingOrgId ? 'Perbarui' : 'Buat' }}</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Unit Modal -->
    @if ($showUnitModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900">{{ $editingUnitId ? 'Edit Unit' : 'Tambah Unit' }}</h2>
                    <button wire:click="closeUnitModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Nama <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="unit_name" placeholder="Nama unit" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('unit_name') border-red-500 @enderror" />
                        @error('unit_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Kode</label>
                        <input type="text" wire:model="unit_code" placeholder="Kode unit" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Organisasi <span class="text-red-500">*</span></label>
                        <select wire:model="organization_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('organization_id') border-red-500 @enderror">
                            <option value="">Pilih organisasi</option>
                            @foreach(\App\Models\Organization::orderBy('name')->get() as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                        @error('organization_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex gap-3 justify-end">
                    <button wire:click="closeUnitModal" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-lg font-medium transition-colors">Batal</button>
                    <button wire:click="saveUnit" class="px-4 py-2.5 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 rounded-lg font-bold transition-colors">{{ $editingUnitId ? 'Perbarui' : 'Buat' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
