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

    <!-- Alerts -->
    @if ($alerts->count() > 0)
        <div class="mb-6 space-y-3">
            @foreach ($alerts as $alert)
                <div
                    class="flex items-center gap-3 p-4 rounded-xl border
                @if ($alert['type'] === 'danger') bg-red-50 border-red-200 text-red-800
                @elseif($alert['type'] === 'warning') bg-orange-50 border-orange-200 text-orange-800
                @else bg-blue-50 border-blue-200 text-blue-800 @endif">
                    @if ($alert['type'] === 'danger')
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    @elseif($alert['type'] === 'warning')
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    @endif
                    <span class="font-medium">{{ $alert['message'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Trend Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Trend SPD 6 Bulan Terakhir</h3>
            <canvas id="monthlyTrendChart" height="200"></canvas>
        </div>

        <!-- Status Distribution Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Distribusi Status SPD</h3>
            <canvas id="statusChart" height="200"></canvas>
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Trend Chart
            const monthlyCtx = document.getElementById('monthlyTrendChart');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($monthlyTrend->pluck('month')) !!},
                        datasets: [{
                            label: 'Jumlah SPD',
                            data: {!! json_encode($monthlyTrend->pluck('count')) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode(collect($statusDistribution)->pluck('status')) !!},
                        datasets: [{
                            data: {!! json_encode(collect($statusDistribution)->pluck('count')) !!},
                            backgroundColor: [
                                'rgba(100, 116, 139, 0.8)', // Draft - slate
                                'rgba(249, 115, 22, 0.8)', // Pending - orange
                                'rgba(16, 185, 129, 0.8)', // Approved - emerald
                                'rgba(59, 130, 246, 0.8)', // Completed - blue
                                'rgba(239, 68, 68, 0.8)', // Rejected - red
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
