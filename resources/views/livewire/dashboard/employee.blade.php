<div>
    <!-- Welcome Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-teal to-brand-dark rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-2">Halo, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h2>
                <p class="text-white/90 mb-4 font-medium">Siap untuk pengajuan perjalanan dinas berikutnya?</p>
                <a href="{{ route('spd.create') }}"
                    class="inline-flex items-center gap-2 bg-brand-lime text-[#1A1A1A] font-bold px-5 py-2.5 rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan SPD Baru
                </a>
            </div>
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
            <div class="absolute -right-5 -bottom-5 w-24 h-24 bg-brand-lime/10 rounded-full"></div>
        </div>
    </div>

    <!-- Employee Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-brand-teal hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">SPD Saya (Bulan Ini)</p>
            <p class="text-3xl font-extrabold text-slate-800">{{ $totalSpdThisMonth }}</p>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-orange-500 hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Menunggu Persetujuan</p>
            <p class="text-3xl font-extrabold text-orange-500">{{ $pendingApproval }}</p>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-red-500 hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Laporan Tertunda</p>
            <p class="text-3xl font-extrabold text-red-500">0</p>
        </div>
    </div>

    <!-- My Recent SPDs Table -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Riwayat Perjalanan Terakhir</h3>
        @if ($recentSpds->count() > 0)
            <div class="space-y-4">
                @foreach ($recentSpds as $spd)
                    <div
                        class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-brand-200 transition-colors">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $spd->destination }}</p>
                            <p class="text-sm text-slate-500">{{ $spd->purpose }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $spd->departure_date->format('d M Y') }} -
                                {{ $spd->return_date->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span @class([
                                'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                'bg-emerald-100 text-emerald-700' => $spd->status === 'approved',
                                'bg-orange-100 text-orange-700' => $spd->status === 'submitted',
                                'bg-red-100 text-red-700' => $spd->status === 'rejected',
                                'bg-slate-100 text-slate-700' => !in_array($spd->status, [
                                    'approved',
                                    'submitted',
                                    'rejected',
                                ]),
                            ])>
                                {{ $spd->status_label }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('spd.index') }}" class="text-brand-600 font-medium hover:text-brand-700 text-sm">Lihat
                    Semua Riwayat →</a>
            </div>
        @else
            <p class="text-slate-500 text-center py-4">Belum ada riwayat perjalanan.</p>
        @endif
    </div>
</div>
