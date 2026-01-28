<div>
    <!-- Search and Filter -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari SPD, tujuan, atau nama pegawai..."
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <select wire:model.live="status"
                    class="px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Menunggu Approval</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="completed">Selesai</option>
                </select>
            </div>
            <a href="{{ route('spd.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat SPD
            </a>
        </div>
    </div>

    <!-- SPD Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($spds as $spd)
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                        @if ($spd->status === 'approved') bg-emerald-100 text-emerald-700
                        @elseif($spd->status === 'submitted') bg-orange-100 text-orange-700
                        @elseif($spd->status === 'rejected') bg-red-100 text-red-700
                        @elseif($spd->status === 'completed') bg-blue-100 text-blue-700
                        @else bg-slate-100 text-slate-700 @endif">
                        {{ $spd->status_label }}
                    </span>
                </div>

                <h3 class="font-semibold text-lg text-slate-800 mb-1 truncate">{{ $spd->destination }}</h3>
                <p class="text-sm text-slate-500 mb-2">{{ $spd->spt_number }}</p>
                <p class="text-xs text-slate-400 line-clamp-2 mb-3">{{ $spd->purpose }}</p>

                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="w-6 h-6 bg-slate-200 rounded-full flex items-center justify-center text-xs font-medium text-slate-600">
                        {{ $spd->employee->initials ?? 'N/A' }}
                    </div>
                    <span class="text-sm text-slate-600 truncate">{{ $spd->employee->name ?? 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <div class="text-sm text-slate-500">
                        {{ $spd->departure_date->format('d M') }} - {{ $spd->return_date->format('d M Y') }}
                    </div>
                    <div class="font-semibold text-blue-600">
                        {{ $spd->formatCost() }}
                    </div>
                </div>

                <a href="{{ route('spd.show', $spd) }}"
                    class="block mt-3 text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Lihat Detail →
                </a>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada SPD</h3>
                <p class="text-slate-500 mb-4">Mulai dengan membuat SPD pertama Anda</p>
                <a href="{{ route('spd.create') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat SPD Baru
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($spds->hasPages())
        <div class="mt-6">
            {{ $spds->links() }}
        </div>
    @endif
</div>
