<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard Pimpinan</h1>
        <p class="text-slate-600 mt-1">{{ Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <!-- Welcome Banner -->
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-blue-900 rounded-2xl p-8 text-white shadow-lg">
        <div>
            <h2 class="text-3xl font-bold mb-2">👔 Dashboard Pimpinan/Approver</h2>
            <p class="text-white/80">Tinjau dan setujui pengajuan perjalanan dinas tim Anda</p>
        </div>
    </div>

    <!-- Stats Grid (4 cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu Persetujuan</p>
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
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Diproses</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalProcessed }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="mb-6">
        <a href="{{ route('approvals.queue') ?? '#' }}"
            class="inline-flex items-center gap-2 bg-orange-600 hover:bg-orange-700 text-white font-bold px-4 py-2.5 rounded-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
            </svg>
            <span>Review Antrian ({{ $pendingApprovals }})</span>
        </a>
    </div>

    <!-- Footer -->
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-900"><span class="font-semibold">👔 Panel Pimpinan:</span> Gunakan dashboard ini untuk mereview dan menyetujui pengajuan SPD dari tim Anda</p>
    </div>
</div>
