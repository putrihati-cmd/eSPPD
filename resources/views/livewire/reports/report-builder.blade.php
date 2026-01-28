<div>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Report Builder</h2>
        <p class="text-sm text-slate-500">Buat laporan kustom dengan pilihan field dan filter</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar: Config -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Report Type -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-3">Jenis Laporan</h3>
                <select wire:model.live="reportType" class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                    @foreach ($reportTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-3">Filter</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-slate-500">Status</label>
                        <select wire:model.live="filterStatus"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">Semua</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Menunggu Approval</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Unit Kerja</label>
                        <select wire:model.live="filterUnitId"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            <option value="">Semua Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Dari Tanggal</label>
                        <input type="date" wire:model.live="filterFromDate"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Sampai Tanggal</label>
                        <input type="date" wire:model.live="filterToDate"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <!-- Field Selection -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-3">Pilih Kolom</h3>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach ($availableFields as $key => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model.live="selectedFields" value="{{ $key }}"
                                class="rounded border-slate-300 text-blue-600">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Group By -->
            @if ($reportType !== 'sppd_list')
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                    <h3 class="font-semibold text-slate-800 mb-3">Group By</h3>
                    <select wire:model.live="groupBy"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                        <option value="">Tidak ada</option>
                        <option value="units.id">Unit Kerja</option>
                        <option value="status">Status</option>
                        <option value="transport_type">Transportasi</option>
                    </select>
                </div>
            @endif
        </div>

        <!-- Main: Preview & Export -->
        <div class="lg:col-span-3">
            <!-- Export Buttons -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Export Laporan</span>
                    <div class="flex gap-3">
                        <button wire:click="exportExcel"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </button>
                        <button wire:click="exportPdf"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            PDF
                        </button>
                        <button wire:click="exportCsv"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-xl hover:bg-slate-700">
                            CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800">Preview Data ({{ $previewLimit }} data pertama)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach ($selectedFields as $field)
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">
                                        {{ $availableFields[$field] ?? $field }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($previewData as $row)
                                <tr class="hover:bg-slate-50">
                                    @foreach ($selectedFields as $field)
                                        <td class="px-4 py-3 text-slate-700">
                                            @if (in_array($field, ['departure_date', 'return_date', 'created_at']))
                                                {{ isset($row[$field]) ? \Carbon\Carbon::parse($row[$field])->format('d/m/Y') : '-' }}
                                            @elseif(in_array($field, ['estimated_cost', 'actual_cost']))
                                                {{ isset($row[$field]) ? 'Rp ' . number_format($row[$field], 0, ',', '.') : '-' }}
                                            @else
                                                {{ $row[$field] ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($selectedFields) }}"
                                        class="px-4 py-8 text-center text-slate-500">
                                        Tidak ada data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
