<div>
    <!-- Welcome Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-2">Selamat Datang, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h2>
                <p class="text-blue-100 mb-4">Kelola perjalanan dinas Anda dengan mudah dan efisien</p>
                <a href="{{ route('spd.create') }}"
                    class="inline-flex items-center gap-2 bg-white text-blue-600 font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Buat SPD Baru
                </a>
            </div>
            <!-- Decorative circles -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
            <div class="absolute -right-5 -bottom-5 w-24 h-24 bg-white/10 rounded-full"></div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total SPD Bulan Ini -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Total SPD Bulan Ini</p>
                    <p class="text-3xl font-bold text-slate-800">{{ $totalSpdThisMonth }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Menunggu Approval -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Menunggu Approval</p>
                    <p class="text-3xl font-bold text-orange-500">{{ $pendingApproval }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Disetujui -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Disetujui</p>
                    <p class="text-3xl font-bold text-emerald-500">{{ $approvedSpd }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Selesai</p>
                    <p class="text-3xl font-bold text-blue-500">{{ $completedSpd }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent SPD and Budget -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent SPD -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">SPD Terbaru</h3>
                <a href="{{ route('spd.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Lihat
                    Semua →</a>
            </div>

            @if ($recentSpds->count() > 0)
                <div class="space-y-4">
                    @foreach ($recentSpds as $spd)
                        <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-xl">
                            <div
                                class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800 truncate">{{ $spd->destination }}</p>
                                <p class="text-sm text-slate-500">{{ $spd->spt_number }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $spd->purpose }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                                    @if ($spd->status === 'approved') bg-emerald-100 text-emerald-700
                                    @elseif($spd->status === 'submitted') bg-orange-100 text-orange-700
                                    @elseif($spd->status === 'rejected') bg-red-100 text-red-700
                                    @elseif($spd->status === 'completed') bg-blue-100 text-blue-700
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $spd->status_label }}
                                </span>
                                <p class="text-sm font-semibold text-blue-600 mt-1">{{ $spd->formatCost() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p>Belum ada SPD</p>
                </div>
            @endif
        </div>

        <!-- Budget Summary -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Ringkasan Budget</h3>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Total Budget</span>
                        <span class="font-semibold text-slate-800">Rp
                            {{ number_format($totalBudget, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Terpakai</span>
                        <span class="font-semibold text-orange-500">Rp
                            {{ number_format($usedBudget, 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-orange-500 h-2 rounded-full"
                            style="width: {{ $totalBudget > 0 ? ($usedBudget / $totalBudget) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-500">Tersedia</span>
                        <span class="font-semibold text-emerald-500">Rp
                            {{ number_format($availableBudget, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('budgets.index') }}"
                class="block mt-4 text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                Lihat Detail Budget →
            </a>
        </div>
    </div>
</div>
