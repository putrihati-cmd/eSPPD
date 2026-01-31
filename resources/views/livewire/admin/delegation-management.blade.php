<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Approval Delegation Management</h1>
        <p class="text-slate-600 mt-1">Kelola pendelegasian approval ke pengguna lain</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Toolbar -->
    <div class="mb-6 flex gap-3">
        <button wire:click="openModal" class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Delegasi</span>
        </button>
        <input type="text" wire:model.live="search" placeholder="Cari delegasi..." class="px-4 py-2.5 rounded-xl border border-slate-200 flex-1" />
    </div>

    <!-- Delegations Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Dari (Delegator)</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Kepada (Delegate)</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Periode</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Alasan</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->delegations as $delegation)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $delegation->delegator->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $delegation->delegate->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $delegation->start_date->format('d M Y') }} - {{ $delegation->end_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($delegation->is_active && now()->between($delegation->start_date, $delegation->end_date))
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold">AKTIF</span>
                            @elseif(now()->before($delegation->start_date))
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold">PENDING</span>
                            @else
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">EXPIRED</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $delegation->reason ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                @if($delegation->is_active)
                                    <button wire:click="toggleActive({{ $delegation->id }})" class="px-3 py-1.5 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 rounded-lg text-xs font-medium transition-colors">Nonaktifkan</button>
                                @else
                                    <button wire:click="toggleActive({{ $delegation->id }})" class="px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-600 rounded-lg text-xs font-medium transition-colors">Aktifkan</button>
                                @endif
                                <button wire:click="openModal({{ $delegation->id }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">Edit</button>
                                <button wire:click="delete({{ $delegation->id }})" onclick="return confirm('Yakin ingin menghapus delegasi ini?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">Tidak ada delegasi ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->delegations->links() }}
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900">{{ $editingId ? 'Edit Delegasi' : 'Tambah Delegasi' }}</h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Dari (Delegator) <span class="text-red-500">*</span></label>
                        <select wire:model="delegator_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('delegator_id') border-red-500 @enderror">
                            <option value="">Pilih delegator</option>
                            @foreach($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->roleModel?->label }})</option>
                            @endforeach
                        </select>
                        @error('delegator_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Kepada (Delegate) <span class="text-red-500">*</span></label>
                        <select wire:model="delegate_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('delegate_id') border-red-500 @enderror">
                            <option value="">Pilih delegate</option>
                            @foreach($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->roleModel?->label }})</option>
                            @endforeach
                        </select>
                        @error('delegate_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="start_date" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('start_date') border-red-500 @enderror" />
                        @error('start_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Tanggal Berakhir <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="end_date" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('end_date') border-red-500 @enderror" />
                        @error('end_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Alasan</label>
                        <textarea wire:model="reason" placeholder="Alasan delegasi" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg"></textarea>
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
