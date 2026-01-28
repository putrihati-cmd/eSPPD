<div>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Kelola Template Laporan</h2>
        <p class="text-sm text-slate-500">Upload template .docx kustom untuk laporan perjalanan dinas</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Form -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Upload Template Baru</h3>

            <form wire:submit="upload">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Template</label>
                        <input type="text" wire:model="templateName"
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                            placeholder="Template Laporan Resmi">
                        @error('templateName')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Template</label>
                        <select wire:model="templateType" class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                            <option value="trip_report">Laporan Perjalanan</option>
                            <option value="sppd">Surat Perintah Perjalanan Dinas</option>
                            <option value="spt">Surat Perintah Tugas</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">File Template (.docx)</label>
                        <input type="file" wire:model="file" accept=".docx"
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                        @error('file')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isDefault" id="isDefault"
                            class="rounded border-slate-300 text-blue-600">
                        <label for="isDefault" class="text-sm text-slate-600">Jadikan template default</label>
                    </div>
                </div>

                <button type="submit"
                    class="w-full mt-4 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="upload">Upload Template</span>
                    <span wire:loading wire:target="upload">Uploading...</span>
                </button>
            </form>

            <!-- Placeholder Guide -->
            <div class="mt-6 p-4 bg-slate-50 rounded-xl">
                <h4 class="text-sm font-medium text-slate-700 mb-2">Placeholder yang Tersedia:</h4>
                <div class="text-xs text-slate-600 space-y-1">
                    <p><code class="bg-slate-200 px-1">{{ NAMA }}</code> - Nama pegawai</p>
                    <p><code class="bg-slate-200 px-1">{{ NIP }}</code> - NIP pegawai</p>
                    <p><code class="bg-slate-200 px-1">{{ TUJUAN }}</code> - Tujuan perjalanan</p>
                    <p><code class="bg-slate-200 px-1">{{ TANGGAL_BERANGKAT }}</code> - Tanggal berangkat</p>
                    <p><code class="bg-slate-200 px-1">{{ TANGGAL_KEMBALI }}</code> - Tanggal kembali</p>
                    <p><code class="bg-slate-200 px-1">{{ KEPERLUAN }}</code> - Keperluan/tujuan</p>
                    <p><code class="bg-slate-200 px-1">{{ NOMOR_SPPD }}</code> - Nomor SPPD</p>
                </div>
            </div>
        </div>

        <!-- Templates List -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Daftar Template</h3>

            @if ($templates->count() > 0)
                <div class="space-y-3">
                    @foreach ($templates as $template)
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $template->name }}</p>
                                    <p class="text-sm text-slate-500">
                                        {{ ucfirst(str_replace('_', ' ', $template->type)) }}
                                        @if ($template->is_default)
                                            <span
                                                class="ml-2 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs rounded-full">Default</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="download('{{ $template->id }}')"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Download">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                                @if (!$template->is_default)
                                    <button wire:click="setDefault('{{ $template->id }}')"
                                        class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg"
                                        title="Set as Default">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                @endif
                                <button wire:click="delete('{{ $template->id }}')"
                                    wire:confirm="Yakin ingin menghapus template ini?"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p>Belum ada template yang diupload</p>
                </div>
            @endif
        </div>
    </div>
</div>
