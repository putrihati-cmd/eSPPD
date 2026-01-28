<div>
    <!-- Back Button -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('spd.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800">
            <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar SPD
        </a>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            @if ($spd->needsReport())
                <a href="{{ route('reports.create', $spd) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700 transition-colors">
                    <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Buat Laporan Perjalanan
                </a>
            @elseif($spd->report)
                <a href="{{ route('reports.show', $spd->report) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700 transition-colors">
                    <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat Laporan
                </a>
            @endif
            <a href="{{ route('spd.pdf.spt', $spd) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download SPT
            </a>
            <a href="{{ route('spd.pdf.spd', $spd) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download SPD
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- SPD Header -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">{{ $spd->destination }}</h2>
                        <p class="text-slate-500 mt-1">{{ $spd->spt_number }}</p>
                    </div>
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium 
                        @if ($spd->status === 'approved') bg-emerald-100 text-emerald-700
                        @elseif($spd->status === 'submitted') bg-orange-100 text-orange-700
                        @elseif($spd->status === 'rejected') bg-red-100 text-red-700
                        @elseif($spd->status === 'completed') bg-blue-100 text-blue-700
                        @else bg-slate-100 text-slate-700 @endif">
                        {{ $spd->status_label }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-slate-500">Tanggal Berangkat</p>
                        <p class="font-medium text-slate-800">{{ $spd->departure_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Tanggal Kembali</p>
                        <p class="font-medium text-slate-800">{{ $spd->return_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Durasi</p>
                        <p class="font-medium text-slate-800">{{ $spd->duration }} hari</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Transportasi</p>
                        <p class="font-medium text-slate-800 capitalize">
                            {{ str_replace('_', ' ', $spd->transport_type) }}</p>
                    </div>
                </div>
            </div>

            <!-- Purpose -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-3">Maksud Perjalanan</h3>
                <p class="text-slate-600">{{ $spd->purpose }}</p>

                @if ($spd->invitation_number)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm text-slate-500">Nomor Undangan</p>
                        <p class="font-medium text-slate-800">{{ $spd->invitation_number }}</p>
                    </div>
                @endif
            </div>

            <!-- Costs -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-4">Rincian Biaya</h3>
                <div class="space-y-3">
                    @foreach ($spd->costs as $cost)
                        <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                            <div>
                                <p class="font-medium text-slate-700">{{ $cost->category_label }}</p>
                                <p class="text-sm text-slate-500">{{ $cost->description }}</p>
                            </div>
                            <p class="font-semibold text-slate-800">{{ $cost->formatAmount() }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t-2 border-slate-200 flex justify-between">
                    <p class="font-semibold text-slate-800">Total Estimasi</p>
                    <p class="text-xl font-bold text-blue-600">{{ $spd->formatCost() }}</p>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Employee Info -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-4">Pegawai</h3>
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 font-bold">
                        {{ $spd->employee->initials ?? 'N/A' }}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800">{{ $spd->employee->name ?? 'N/A' }}</p>
                        <p class="text-sm text-slate-500">{{ $spd->employee->position ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">NIP</span>
                        <span class="text-slate-700">{{ $spd->employee->nip ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Golongan</span>
                        <span class="text-slate-700">{{ $spd->employee->grade ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Unit</span>
                        <span class="text-slate-700">{{ $spd->unit->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Approval History -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-4">Riwayat Approval</h3>
                @if ($spd->approvals->count() > 0)
                    <div class="space-y-3">
                        @foreach ($spd->approvals as $approval)
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium
                                    @if ($approval->status === 'approved') bg-emerald-100 text-emerald-600
                                    @elseif($approval->status === 'rejected') bg-red-100 text-red-600
                                    @else bg-orange-100 text-orange-600 @endif">
                                    {{ $approval->level }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-700">
                                        {{ $approval->approver->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-500">{{ $approval->status_label }}</p>
                                    @if ($approval->approved_at)
                                        <p class="text-xs text-slate-400">
                                            {{ $approval->approved_at->format('d M Y H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">Belum ada approval</p>
                @endif
            </div>

            <!-- Budget Info -->
            @if ($spd->budget)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <h3 class="font-semibold text-slate-800 mb-4">Sumber Anggaran</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kode MAK</span>
                            <span class="text-slate-700">{{ $spd->budget->code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nama</span>
                            <span class="text-slate-700 text-right">{{ $spd->budget->name }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
