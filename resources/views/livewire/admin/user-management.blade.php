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
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600">×</button>
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

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Nama</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">NIP</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Role</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Organisasi</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-slate-900">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->users as $user)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->nip }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900">
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                {{ $user->roleModel?->label ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->organization?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <button wire:click="openModal({{ $user->id }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">Edit</button>
                                <button wire:click="delete({{ $user->id }})" onclick="return confirm('Yakin ingin menghapus user ini?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">Tidak ada user ditemukan</td>
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
                    <h2 class="text-xl font-bold text-slate-900">{{ $editingId ? 'Edit User' : 'Tambah User' }}</h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Nama <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Nama lengkap" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('name') border-red-500 @enderror" />
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">NIP <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="nip" placeholder="Nomor Induk Pegawai" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('nip') border-red-500 @enderror" />
                        @error('nip') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="email" placeholder="Email" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('email') border-red-500 @enderror" />
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Role <span class="text-red-500">*</span></label>
                        <select wire:model="role_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('role_id') border-red-500 @enderror">
                            <option value="">Pilih role</option>
                            @foreach($this->roles as $role)
                                <option value="{{ $role->id }}">{{ $role->label }} (Level {{ $role->level }})</option>
                            @endforeach
                        </select>
                        @error('role_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Organisasi</label>
                        <select wire:model="organization_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg">
                            <option value="">Pilih organisasi</option>
                            @foreach($this->organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if (!$editingId)
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-2">Password <span class="text-red-500">*</span></label>
                            <input type="password" wire:model="password" placeholder="Password minimal 8 karakter" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('password') border-red-500 @enderror" />
                            @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-2">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" wire:model="password" placeholder="Password minimal 8 karakter" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('password') border-red-500 @enderror" />
                            @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="sticky bottom-0 bg-slate-50 border-t border-slate-100 px-6 py-4 flex gap-3 justify-end">
                    <button wire:click="closeModal" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-lg font-medium transition-colors">Batal</button>
                    <button wire:click="save" class="px-4 py-2.5 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 rounded-lg font-bold transition-colors">{{ $editingId ? 'Perbarui' : 'Buat' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
