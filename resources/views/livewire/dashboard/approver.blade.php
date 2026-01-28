<div>
    <!-- Welcome Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-2">Dashboard Pimpinan</h2>
                <p class="text-brand-50 mb-4">Tinjau dan setujui permohonan perjalanan dinas.</p>
                <a href="{{ route('approvals.queue') }}"
                    class="inline-flex items-center gap-2 bg-white text-brand-700 font-semibold px-5 py-2.5 rounded-xl hover:bg-brand-50 transition-colors shadow-sm">
                    Review Antrian ({{ $pendingApproval }})
                </a>
            </div>
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
        </div>
    </div>

    <!-- Approver Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500 mb-1">Perlu Persetujuan</p>
            <p class="text-3xl font-bold text-orange-500">{{ $pendingApproval }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500 mb-1">Disetujui Bulan Ini</p>
            <p class="text-3xl font-bold text-emerald-500">{{ $approvedSpd }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500 mb-1">Total SPD Unit</p>
            <p class="text-3xl font-bold text-slate-800">{{ $totalSpdThisMonth }}</p>
        </div>
    </div>

    <!-- Pending Applications Preview -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Permohonan Terbaru</h3>
        @if ($recentSpds->count() > 0)
            <div class="space-y-4">
                @foreach ($recentSpds as $spd)
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center font-bold">
                                {{ substr($spd->employee->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">{{ $spd->employee->name ?? 'Pegawai' }}</p>
                                <p class="text-sm text-slate-500">ke {{ $spd->destination }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400">{{ $spd->created_at->diffForHumans() }}</p>
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1
                                @if ($spd->status === 'submitted') bg-orange-100 text-orange-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ $spd->status_label }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('approvals.index') }}"
                    class="text-brand-600 font-medium hover:text-brand-700 text-sm">Lihat Semua Permohonan →</a>
            </div>
        @else
            <p class="text-slate-500 text-center py-4">Tidak ada permohonan baru.</p>
        @endif
    </div>
</div>
