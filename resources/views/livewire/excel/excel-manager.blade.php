<div>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Import/Export Data SPPD</h2>
        <p class="text-sm text-slate-500">Kelola data SPPD menggunakan file Excel</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Import Section -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Import Data</h3>

            <!-- Download Template -->
            <div class="mb-6 p-4 bg-blue-50 rounded-xl">
                <p class="text-sm text-blue-800 mb-2">Langkah 1: Download template terlebih dahulu</p>
                <button wire:click="downloadTemplate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Template
                </button>
            </div>

            <!-- Upload Form -->
            <form wire:submit="upload">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Langkah 2: Upload file Excel yang sudah
                        diisi</label>
                    <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-blue-400 transition-colors"
                        x-data="{ dragover: false }" x-on:dragover.prevent="dragover = true"
                        x-on:dragleave.prevent="dragover = false"
                        x-on:drop.prevent="dragover = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                        :class="{ 'border-blue-400 bg-blue-50': dragover }">
                        <input type="file" wire:model="file" x-ref="fileInput" class="hidden" accept=".xlsx,.xls">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="text-slate-600 mb-2">Drag & drop file atau</p>
                        <button type="button" x-on:click="$refs.fileInput.click()"
                            class="text-blue-600 hover:underline">pilih file</button>
                        <p class="text-xs text-slate-400 mt-2">Format: .xlsx, .xls (Max 10MB)</p>
                    </div>
                    @if ($file)
                        <p class="mt-2 text-sm text-slate-600">File: {{ $file->getClientOriginalName() }}</p>
                    @endif
                    @error('file')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Progress Bar -->
                @if ($progress > 0)
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600">Progress</span>
                            <span class="text-slate-800 font-medium">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-300"
                                style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @endif

                <!-- Messages -->
                @if ($uploadMessage)
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
                        {{ $uploadMessage }}
                    </div>
                @endif
                @if ($uploadError)
                    <div
                        class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm whitespace-pre-line">
                        {{ $uploadError }}
                    </div>
                @endif

                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 disabled:opacity-50"
                        wire:loading.attr="disabled" @if (!$file) disabled @endif>
                        <span wire:loading.remove wire:target="upload">Upload & Import</span>
                        <span wire:loading wire:target="upload">Processing...</span>
                    </button>
                    @if ($file || $uploadMessage || $uploadError)
                        <button type="button" wire:click="resetUpload"
                            class="px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50">
                            Reset
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Export Section -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Export Data</h3>

            <form wire:submit="export">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select wire:model="exportStatus" class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Menunggu Approval</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                            <input type="date" wire:model="exportFromDate"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                            <input type="date" wire:model="exportToDate"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full mt-6 px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 inline-flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export ke Excel
                </button>
            </form>

            <!-- Quick Export Options -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <h4 class="text-sm font-medium text-slate-700 mb-3">Export Cepat</h4>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('excel.export', ['status' => 'approved']) }}"
                        class="px-4 py-2 text-center text-sm bg-emerald-50 text-emerald-700 rounded-xl hover:bg-emerald-100">
                        SPD Disetujui
                    </a>
                    <a href="{{ route('excel.export', ['from_date' => now()->startOfMonth()->format('Y-m-d')]) }}"
                        class="px-4 py-2 text-center text-sm bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100">
                        Bulan Ini
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
