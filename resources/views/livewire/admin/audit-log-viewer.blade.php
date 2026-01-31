<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Audit Logs</h1>
        <p class="text-slate-600 mt-1">Pantau semua aktivitas dan perubahan sistem</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Search</label>
                <input type="text" wire:model.live="search" placeholder="Cari entity ID..." class="w-full px-4 py-2.5 border border-slate-200 rounded-lg" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Entity</label>
                <select wire:model.live="entity" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg">
                    <option value="">Semua Entity</option>
                    @foreach($this->entities as $ent)
                        <option value="{{ $ent }}">{{ $ent }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Action</label>
                <select wire:model.live="action" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg">
                    <option value="">Semua Action</option>
                    @foreach($this->actions as $act)
                        <option value="{{ $act }}">{{ ucfirst($act) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">User</label>
                <select wire:model.live="user_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg">
                    <option value="">Semua User</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 items-end">
                <button wire:click="resetFilters" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-lg font-medium transition-colors">Reset</button>
                <a wire:click="export" href="#" class="flex-1 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg font-bold transition-colors text-center">Export</a>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Dari Tanggal</label>
                <input type="date" wire:model.live="date_from" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Hingga Tanggal</label>
                <input type="date" wire:model.live="date_to" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg" />
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Waktu</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">User</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Action</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Entity</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Entity ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Perubahan</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->logs as $log)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @switch($log->action)
                                @case('create')
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded text-xs font-bold">CREATE</span>
                                @break
                                @case('update')
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold">UPDATE</span>
                                @break
                                @case('delete')
                                    <span class="px-2 py-1 bg-red-50 text-red-700 rounded text-xs font-bold">DELETE</span>
                                @break
                                @case('approve')
                                    <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded text-xs font-bold">APPROVE</span>
                                @break
                                @case('reject')
                                    <span class="px-2 py-1 bg-orange-50 text-orange-700 rounded text-xs font-bold">REJECT</span>
                                @break
                                @default
                                    <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-bold">{{ strtoupper($log->action) }}</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $log->entity }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 font-mono text-xs">{{ substr($log->entity_id, 0, 8) }}...</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if($log->changes)
                                <details class="cursor-pointer">
                                    <summary class="text-xs font-semibold text-blue-600 hover:text-blue-700">{{ count($log->changes) }} fields</summary>
                                    <div class="mt-2 p-2 bg-slate-50 rounded text-xs font-mono text-slate-600 overflow-x-auto">
                                        <pre>{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                </details>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 font-mono text-xs">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">Tidak ada log ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $this->logs->links() }}
    </div>
</div>
