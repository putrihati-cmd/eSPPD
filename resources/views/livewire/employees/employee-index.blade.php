<div>
    <!-- Search -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 mb-6">
        <div class="flex gap-4">
            <div class="flex-1 relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, NIP, atau jabatan..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button
                class="inline-flex items-center gap-2 bg-blue-600 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pegawai
            </button>
        </div>
    </div>

    <!-- Employee Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($employees as $employee)
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 font-bold">
                        {{ $employee->initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-slate-800 truncate">{{ $employee->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $employee->nip }}</p>
                    </div>
                    @if ($employee->is_active)
                        <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                    @else
                        <span class="w-3 h-3 bg-slate-300 rounded-full"></span>
                    @endif
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Jabatan</span>
                        <span class="text-slate-700 text-right truncate ml-2">{{ $employee->position }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Golongan</span>
                        <span class="text-slate-700">{{ $employee->grade }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Unit</span>
                        <span class="text-slate-700 text-right truncate ml-2">{{ $employee->unit->name ?? '-' }}</span>
                    </div>
                </div>

                <div class="flex gap-2 mt-4 pt-3 border-t border-slate-100">
                    <button
                        class="flex-1 px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        Edit
                    </button>
                    <button
                        class="flex-1 px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        Lihat SPD
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Pegawai</h3>
                <p class="text-slate-500">Mulai dengan menambahkan pegawai</p>
            </div>
        @endforelse
    </div>

    @if ($employees->hasPages())
        <div class="mt-6">
            {{ $employees->links() }}
        </div>
    @endif
</div>
