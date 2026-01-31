<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard Admin</h1>
        <p class="text-slate-600 mt-1">{{ Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <!-- Welcome Banner -->
    <div class="mb-6 bg-gradient-to-r from-purple-600 to-purple-900 rounded-2xl p-8 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold mb-2">👑 Dashboard Administrator</h2>
                <p class="text-white/80">Pantau seluruh sistem dan kelola SPD organisasi</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid (6 cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Pengguna</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalUsers }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM16 16a5 5 0 10-10 0v2a3 3 0 013 3v1" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total SPD</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $totalSpds }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu Approval</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $pendingApprovals }}</p>
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
                    <p class="text-xs font-semibold text-slate-500 uppercase">Disetujui (Bulan Ini)</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $approvedThisMonth }}</p>
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
                    <p class="text-xs font-semibold text-slate-500 uppercase">Ditolak (Bulan Ini)</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $rejectedThisMonth }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Anggaran</p>
                    <p class="text-2xl font-bold text-teal-600 mt-2">Rp {{ number_format($totalBudget, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <livewire:charts.spd-trend-chart />
        <livewire:charts.spd-status-chart />
    </div>

    <!-- System Health -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-900 mb-4">📊 Status Sistem</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <span class="text-sm font-medium text-slate-700">Database</span>
                    <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">✓ Online</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <span class="text-sm font-medium text-slate-700">Cache (Redis)</span>
                    <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">✓ Running</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                    <span class="text-sm font-medium text-slate-700">Queue Jobs</span>
                    <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded">⟳ Processing</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-bold text-slate-900 mb-4">🎯 Tindakan Cepat</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.users.index') ?? '#' }}" class="block p-3 hover:bg-slate-50 rounded-lg transition-all">
                    <p class="font-medium text-slate-900">Kelola Pengguna</p>
                    <p class="text-xs text-slate-500">Tambah, edit, hapus pengguna</p>
                </a>
                <a href="{{ route('approvals.queue') ?? '#' }}" class="block p-3 hover:bg-slate-50 rounded-lg transition-all">
                    <p class="font-medium text-slate-900">Review Antrian</p>
                    <p class="text-xs text-slate-500">{{ $pendingApprovals }} SPD menunggu</p>
                </a>
                <a href="{{ route('reports.index') ?? '#' }}" class="block p-3 hover:bg-slate-50 rounded-lg transition-all">
                    <p class="font-medium text-slate-900">Laporan</p>
                    <p class="text-xs text-slate-500">Unduh laporan sistem</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
        <p class="text-sm text-purple-900"><span class="font-semibold">👑 Panel Admin:</span> Gunakan dashboard ini untuk monitoring dan pengambilan keputusan strategis</p>
    </div>
</div>
