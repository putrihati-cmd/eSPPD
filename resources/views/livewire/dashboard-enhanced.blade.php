<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-600 mt-1">{{ Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <!-- Welcome Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-teal via-brand-teal to-brand-dark rounded-2xl p-8 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-3">
                        Selamat datang, <span class="text-brand-lime">{{ explode(' ', auth()->user()->name)[0] }}! </span>👋
                    </h2>
                    <p class="text-white/80 text-sm md:text-base max-w-lg">
                        @if($userRole === 'approver' || $userRole === 'admin')
                            Kelola dan tinjau perjalanan dinas tim Anda dengan mudah
                        @else
                            Kelola perjalanan dinas Anda dengan efisien dan transparan
                        @endif
                    </p>
                </div>
                <div class="hidden md:flex items-center justify-center w-24 h-24 bg-white/10 rounded-2xl">
                    <svg class="w-12 h-12 text-brand-lime" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="mb-6 flex gap-3">
        <a href="{{ route('spd.create') }}"
            class="inline-flex items-center gap-2 bg-brand-lime hover:bg-brand-lime/90 text-slate-900 font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat SPD Baru</span>
        </a>
        @if($userRole === 'approver' || $userRole === 'admin')
            <a href="{{ route('approvals.queue') }}"
                class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-900 font-bold px-4 py-2.5 rounded-xl border border-slate-200 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Review Antrian ({{ $pendingApproval }})</span>
            </a>
        @endif
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Stat Card: Total SPD -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all group cursor-pointer">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total SPD (Bulan Ini)</p>
                    <p class="text-2xl md:text-3xl font-bold text-slate-900 mt-2">{{ $totalSpdThisMonth }}</p>
                    <p class="text-xs text-slate-400 mt-2">{{ Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                </div>
                <div class="w-14 h-14 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-all">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stat Card: Pending Approval -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all group cursor-pointer">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Persetujuan</p>
                    <p class="text-2xl md:text-3xl font-bold text-orange-600 mt-2">{{ $pendingApproval }}</p>
                    <p class="text-xs text-slate-400 mt-2">Perlu tindakan</p>
                </div>
                <div class="w-14 h-14 bg-orange-50 rounded-lg flex items-center justify-center group-hover:bg-orange-100 transition-all">
                    <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stat Card: Approved -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all group cursor-pointer">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Disetujui (Bulan Ini)</p>
                    <p class="text-2xl md:text-3xl font-bold text-emerald-600 mt-2">{{ $approvedSpd }}</p>
                    <p class="text-xs text-slate-400 mt-2">Berhasil diproses</p>
                </div>
                <div class="w-14 h-14 bg-emerald-50 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 transition-all">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stat Card: Rejected -->
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all group cursor-pointer">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ditolak (Bulan Ini)</p>
                    <p class="text-2xl md:text-3xl font-bold text-red-600 mt-2">{{ $rejectedSpd }}</p>
                    <p class="text-xs text-slate-400 mt-2">Perlu revisi</p>
                </div>
                <div class="w-14 h-14 bg-red-50 rounded-lg flex items-center justify-center group-hover:bg-red-100 transition-all">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent SPDs Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <!-- Header -->
        <div class="border-b border-slate-100 p-6 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition-all"
            wire:click="toggleSection('recent')">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-brand-50 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m0 0h6" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900">SPD Terbaru</h3>
                    <p class="text-xs text-slate-500">5 pengajuan terakhir</p>
                </div>
            </div>
            <svg class="w-6 h-6 text-slate-400 transition-transform {{ $expandedSection === 'recent' ? 'rotate-180' : '' }}"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7-7m0 0L5 14m7-7v12" />
            </svg>
        </div>

        <!-- Content -->
        @if($expandedSection === 'recent' || true)
            <div class="divide-y divide-slate-100">
                @forelse($recentSpds as $spd)
                    <div class="p-4 hover:bg-slate-50 transition-all flex items-center justify-between group">
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900">{{ $spd['destination'] }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-slate-500">{{ $spd['start_date'] }} - {{ $spd['end_date'] }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $spd['status_color'] }}-50 text-{{ $spd['status_color'] }}-700">
                                {{ $spd['status_label'] }}
                            </span>
                            <a href="{{ route('spd.show', $spd['id']) }}"
                                class="p-2 hover:bg-slate-200 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-slate-500 font-medium">Belum ada pengajuan</p>
                        <p class="text-xs text-slate-400 mt-1">Mulai dengan membuat SPD baru</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>

    <!-- Charts Section -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <livewire:charts.spd-trend-chart />
        <livewire:charts.spd-status-chart />
    </div>

    <!-- Footer Note -->
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-900">
            <span class="font-semibold">💡 Tip:</span>
            Klik pada stat card untuk melihat detail lebih lanjut atau gunakan menu untuk navigasi
        </p>
    </div>
</div>
