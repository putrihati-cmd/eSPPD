<div>
    <!-- Search and Filter -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 mb-6 transition-all hover:shadow-md">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari SPD, tujuan, atau nama pegawai..."
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" width="20"
                        height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <select wire:model.live="status"
                    class="px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Menunggu Approval</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="completed">Selesai</option>
                </select>
            </div>
            <a href="{{ route('spd.create') }}"
                class="inline-flex items-center gap-2 bg-brand-600 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">
                <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat SPD
            </a>
        </div>
    </div>

    <!-- SPD Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($spds as $spd)
            <div
                class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md hover:border-brand-200 transition-all group">
                <div class="flex items-start justify-between mb-3">
                    <div
                        class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center group-hover:bg-brand-100 transition-colors">
                        <svg class="w-5 h-5 text-brand-600" width="20" height="20" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold shadow-sm
                        @if ($spd->status === 'approved') bg-accent text-slate-800
                        @elseif($spd->status === 'submitted') bg-orange-100 text-orange-700
                        @elseif($spd->status === 'rejected') bg-red-100 text-red-700
                        @elseif($spd->status === 'completed') bg-blue-100 text-blue-700
                        @else bg-slate-100 text-slate-700 @endif">
                        {{ $spd->status_label }}
                    </span>
                </div>

                <h3 class="font-bold text-lg text-slate-800 mb-1 truncate group-hover:text-brand-700 transition-colors">
                    {{ $spd->destination }}</h3>
                <p class="text-sm text-slate-500 mb-2 font-mono">{{ $spd->spt_number }}</p>
                <p class="text-xs text-slate-400 line-clamp-2 mb-3 leading-relaxed">{{ $spd->purpose }}</p>

                <div class="flex items-center gap-2 mb-3">
                    <div
                        class="w-6 h-6 bg-slate-200 rounded-full flex items-center justify-center text-xs font-bold text-slate-600">
                        {{ $spd->employee->initials ?? 'N/A' }}
                    </div>
                    <span class="text-sm text-slate-600 truncate">{{ $spd->employee->name ?? 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <div class="text-sm text-slate-500 flex items-center gap-1">
                        <svg class="w-4 h-4 text-slate-400" width="16" height="16" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ $spd->departure_date->format('d M') }} - {{ $spd->return_date->format('d M Y') }}
                    </div>
                    <div class="font-bold text-brand-600">
                        {{ $spd->formatCost() }}
                    </div>
                </div>

                <a href="{{ route('spd.show', $spd) }}"
                    class="block mt-3 text-center text-sm text-brand-600 hover:text-brand-700 font-semibold py-2 bg-brand-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all">
                    Lihat Detail →
                </a>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 mx-auto mb-4 bg-slate-50 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-300" width="32" height="32" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-2">Belum Ada SPD</h3>
                <p class="text-slate-500 mb-6 max-w-sm mx-auto">Mulai perjalanan dinas Anda dengan membuat Surat
                    Perintah Perjalanan Dinas baru.</p>
                <a href="{{ route('spd.create') }}"
                    class="inline-flex items-center gap-2 bg-brand-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">
                    <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
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
