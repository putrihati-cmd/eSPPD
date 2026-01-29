<div>
    <!-- Welcome Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-teal to-brand-dark rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Dashboard Pimpinan</h2>
                    <p class="text-white/90 font-medium">Tinjau dan setujui permohonan perjalanan dinas unit Anda</p>
                    <a href="{{ route('approvals.queue') }}"
                        class="mt-4 inline-flex items-center gap-2 bg-brand-lime text-[#1A1A1A] font-bold px-5 py-2.5 rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-xl">
                        Review Antrian ({{ $pendingApproval }})
                    </a>
                </div>
            </div>
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
            <div class="absolute -right-5 -bottom-5 w-24 h-24 bg-brand-lime/10 rounded-full"></div>
        </div>
    </div>

    <!-- Approver Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-orange-500 hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Perlu Persetujuan</p>
            <p class="text-3xl font-extrabold text-orange-500">{{ $pendingApproval }}</p>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-emerald-500 hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Disetujui Bulan Ini</p>
            <p class="text-3xl font-extrabold text-emerald-500">{{ $approvedSpd }}</p>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-brand-teal hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Total SPD Unit</p>
            <p class="text-3xl font-extrabold text-slate-800">{{ $totalSpdThisMonth }}</p>
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
                            <span @class([
                                'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1',
                                'bg-orange-100 text-orange-700' => $spd->status === 'submitted',
                                'bg-slate-100 text-slate-700' => $spd->status !== 'submitted',
                            ])>
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
