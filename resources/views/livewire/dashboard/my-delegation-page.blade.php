<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">My Delegations</h1>
        <p class="text-slate-600 mt-1">Manage your approval delegations</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Action Button -->
    <div class="mb-6">
        <button wire:click="openModal" class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Delegasi</span>
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-2">Active Delegations</p>
            <p class="text-4xl font-bold text-emerald-600">{{ $this->stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-2">Inactive</p>
            <p class="text-4xl font-bold text-slate-400">{{ $this->stats['inactive'] }}</p>
        </div>
    </div>

    <!-- Active Delegations -->
    @if($this->activeDelegations->count() > 0)
        <div class="mb-8">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Active Delegations</h2>
            <div class="space-y-4">
                @foreach($this->activeDelegations as $delegation)
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-emerald-200 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-slate-900">Delegated to {{ $delegation->delegate->name }}</h3>
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold">ACTIVE</span>
                                </div>
                                <p class="text-slate-600 text-sm mb-3">{{ $delegation->start_date->format('d M Y') }} - {{ $delegation->end_date->format('d M Y') }}</p>
                                @if($delegation->reason)
                                    <p class="text-slate-500 text-sm italic">{{ $delegation->reason }}</p>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="openModal({{ $delegation->id }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">Edit</button>
                                <button wire:click="toggleActive({{ $delegation->id }})" class="px-3 py-1.5 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 rounded-lg text-xs font-medium transition-colors">Deactivate</button>
                                <button wire:click="delete({{ $delegation->id }})" onclick="return confirm('Yakin ingin menghapus delegasi ini?')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">Delete</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Inactive Delegations -->
    @if($this->inactiveDelegations->count() > 0)
        <div>
            <h2 class="text-lg font-bold text-slate-900 mb-4">Inactive Delegations</h2>
            <div class="space-y-3">
                @foreach($this->inactiveDelegations as $delegation)
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-900 font-semibold">{{ $delegation->delegate->name }}</p>
                                <p class="text-slate-600 text-sm">{{ $delegation->start_date->format('d M Y') }} - {{ $delegation->end_date->format('d M Y') }}</p>
                            </div>
                            <button wire:click="toggleActive({{ $delegation->id }})" class="px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-600 rounded-lg text-xs font-medium transition-colors">Activate</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Empty State -->
    @if($this->activeDelegations->count() === 0 && $this->inactiveDelegations->count() === 0)
        <div class="bg-white rounded-xl p-12 shadow-sm border border-slate-100 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <p class="text-slate-900 font-semibold mb-2">No delegations yet</p>
            <p class="text-slate-600 text-sm mb-4">Create a delegation to temporarily transfer your approval authority</p>
            <button wire:click="openModal" class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create Delegation</span>
            </button>
        </div>
    @endif

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900">{{ $editingId ? 'Edit Delegation' : 'New Delegation' }}</h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Delegate To <span class="text-red-500">*</span></label>
                        <select wire:model="delegate_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('delegate_id') border-red-500 @enderror">
                            <option value="">Choose a user</option>
                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->roleModel?->label }})</option>
                            @endforeach
                        </select>
                        @error('delegate_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="start_date" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('start_date') border-red-500 @enderror" />
                        @error('start_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">End Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="end_date" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg @error('end_date') border-red-500 @enderror" />
                        @error('end_date') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-2">Reason</label>
                        <textarea wire:model="reason" placeholder="Why are you delegating?" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg"></textarea>
                    </div>
                </div>

                <div class="sticky bottom-0 bg-slate-50 border-t border-slate-100 px-6 py-4 flex gap-3 justify-end">
                    <button wire:click="closeModal" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-lg font-medium transition-colors">Cancel</button>
                    <button wire:click="save" class="px-4 py-2.5 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 rounded-lg font-bold transition-colors">{{ $editingId ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
