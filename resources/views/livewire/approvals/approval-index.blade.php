<div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
        <h2 class="text-lg font-semibold text-slate-800">SPD Menunggu Approval</h2>
        <p class="text-slate-500 text-sm mt-1">Daftar SPD yang memerlukan persetujuan Anda</p>
    </div>

    @if ($approvals->count() > 0)
        <div class="space-y-4">
            @foreach ($approvals as $approval)
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-800">{{ $approval->spd->destination }}</h3>
                                    <p class="text-sm text-slate-500">{{ $approval->spd->spt_number }}</p>
                                </div>
                            </div>

                            <div class="ml-13 grid grid-cols-3 gap-4 mt-3">
                                <div>
                                    <p class="text-xs text-slate-500">Pegawai</p>
                                    <p class="text-sm font-medium text-slate-700">
                                        {{ $approval->spd->employee->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Tanggal</p>
                                    <p class="text-sm font-medium text-slate-700">
                                        {{ $approval->spd->departure_date->format('d M') }} -
                                        {{ $approval->spd->return_date->format('d M Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Biaya</p>
                                    <p class="text-sm font-semibold text-blue-600">{{ $approval->spd->formatCost() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                class="px-4 py-2 bg-red-100 text-red-700 rounded-xl text-sm font-medium hover:bg-red-200 transition-colors">
                                Tolak
                            </button>
                            <button
                                class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700 transition-colors">
                                Setujui
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-semibold text-slate-600 mb-2">Tidak Ada Pending Approval</h3>
            <p class="text-slate-500">Semua SPD sudah diproses</p>
        </div>
    @endif
</div>
