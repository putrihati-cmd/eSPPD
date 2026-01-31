<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard Saya</h1>
        <p class="text-slate-600 mt-1">{{ Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <!-- Welcome Banner -->
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-900 rounded-2xl p-8 text-white shadow-lg">
        <div>
            <h2 class="text-3xl font-bold mb-2">👋 Selamat datang, {{ Auth::user()->name }}!</h2>
            <p class="text-white/80">Kelola pengajuan perjalanan dinas Anda dengan mudah</p>
        </div>
    </div>

    <!-- Stats Grid (4 cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Pengajuan</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalSpds }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu Approval</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $pendingSpds }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Disetujui</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $approvedSpds }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Ditolak</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $rejectedSpds }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-6 flex gap-3">
        <a href="{{ route('spds.create') ?? '#' }}"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2.5 rounded-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat Pengajuan Baru</span>
        </a>
        <a href="{{ route('spds.index') ?? '#' }}"
            class="inline-flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white font-bold px-4 py-2.5 rounded-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
            </svg>
            <span>Lihat Semua Pengajuan</span>
        </a>
    </div>

    <!-- Charts Section -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <livewire:charts.spd-trend-chart />
        <livewire:charts.spd-status-chart />
    </div>

    <!-- Recent SPDs -->
    @if($recentSpds->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="border-b border-slate-200 p-5">
            <h3 class="text-lg font-bold text-slate-900">📋 Pengajuan Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-sm font-semibold text-slate-700">Tujuan</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold text-slate-700">Tanggal</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold text-slate-700">Status</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSpds as $spd)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-all">
                        <td class="px-5 py-4 text-sm text-slate-900">{{ $spd->destination ?? '-' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $spd->start_date?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td class="px-5 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($spd->status === 'approved') bg-emerald-100 text-emerald-800
                                @elseif($spd->status === 'rejected') bg-red-100 text-red-800
                                @elseif($spd->status === 'pending') bg-orange-100 text-orange-800
                                @else bg-slate-100 text-slate-800 @endif">
                                {{ ucfirst($spd->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            <a href="{{ route('spds.show', $spd) ?? '#' }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-900"><span class="font-semibold">💡 Tip:</span> Segera ajukan pengajuan SPD Anda dan pantau statusnya secara real-time</p>
    </div>
</div>
