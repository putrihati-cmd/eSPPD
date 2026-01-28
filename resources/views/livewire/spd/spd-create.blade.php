<div>
    <!-- Step Indicators -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
        <div class="flex items-center justify-between">
            @foreach ([1 => 'Data Pegawai', 2 => 'Detail Perjalanan', 3 => 'Anggaran', 4 => 'Konfirmasi'] as $num => $label)
                <div class="flex items-center {{ $num < 4 ? 'flex-1' : '' }}">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm
                            @if ($step > $num) bg-emerald-500 text-white
                            @elseif($step === $num) bg-blue-600 text-white
                            @else bg-slate-200 text-slate-500 @endif">
                            @if ($step > $num)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span
                            class="text-sm font-medium {{ $step === $num ? 'text-blue-600' : 'text-slate-500' }}">{{ $label }}</span>
                    </div>
                    @if ($num < 4)
                        <div class="flex-1 h-0.5 mx-4 {{ $step > $num ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Step Content -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <!-- Step 1: Employee Selection -->
        @if ($step === 1)
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Pilih Pegawai</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Pegawai</label>
                    <select wire:model.live="employee_id"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->nip }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                @if ($selectedEmployee)
                    <div class="bg-blue-50 rounded-xl p-4 mt-4">
                        <h3 class="font-semibold text-slate-800 mb-3">Info Pegawai</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500">NIP</p>
                                <p class="font-medium text-slate-800">{{ $selectedEmployee->nip }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Jabatan</p>
                                <p class="font-medium text-slate-800">{{ $selectedEmployee->position }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Golongan</p>
                                <p class="font-medium text-slate-800">{{ $selectedEmployee->grade }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Unit</p>
                                <p class="font-medium text-slate-800">{{ $selectedEmployee->unit->name }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Step 2: Travel Details -->
        @if ($step === 2)
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Detail Perjalanan</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tujuan</label>
                    <input type="text" wire:model="destination" placeholder="Contoh: Jakarta, DKI Jakarta"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('destination')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Maksud Perjalanan</label>
                    <textarea wire:model="purpose" rows="3" placeholder="Jelaskan maksud perjalanan dinas..."
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    @error('purpose')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nomor Undangan (Opsional)</label>
                    <input type="text" wire:model="invitation_number" placeholder="Contoh: B-123/ABC/2026"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Berangkat</label>
                        <input type="date" wire:model="departure_date"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('departure_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Kembali</label>
                        <input type="date" wire:model="return_date"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('return_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Transportasi</label>
                        <select wire:model="transport_type"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pesawat">Pesawat</option>
                            <option value="kereta">Kereta Api</option>
                            <option value="bus">Bus</option>
                            <option value="kapal">Kapal</option>
                            <option value="mobil_dinas">Mobil Dinas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Penginapan</label>
                        <label class="flex items-center gap-3 mt-3">
                            <input type="checkbox" wire:model="needs_accommodation"
                                class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-slate-700">Memerlukan Penginapan</span>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 3: Budget -->
        @if ($step === 3)
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Sumber Anggaran</h2>

            <div class="space-y-4">
                @foreach ($budgets as $budget)
                    <label
                        class="block p-4 border-2 rounded-xl cursor-pointer transition-colors
                        {{ $budget_id === $budget->id ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="flex items-start gap-3">
                            <input type="radio" wire:model="budget_id" value="{{ $budget->id }}"
                                class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">{{ $budget->code }}</p>
                                <p class="text-sm text-slate-600">{{ $budget->name }}</p>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="text-xs text-slate-500">
                                        Tersedia: <span class="font-semibold text-emerald-600">Rp
                                            {{ number_format($budget->available_budget, 0, ',', '.') }}</span>
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        Terpakai: {{ $budget->usage_percentage }}%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2">
                                    <div class="bg-blue-600 h-1.5 rounded-full"
                                        style="width: {{ $budget->usage_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
                @error('budget_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <!-- Step 4: Confirmation -->
        @if ($step === 4)
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Konfirmasi SPD</h2>

            <div class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4">
                        <h3 class="font-semibold text-slate-800 mb-3">Pegawai</h3>
                        <p class="text-slate-700">{{ $selectedEmployee->name ?? '-' }}</p>
                        <p class="text-sm text-slate-500">{{ $selectedEmployee->position ?? '-' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4">
                        <h3 class="font-semibold text-slate-800 mb-3">Tujuan</h3>
                        <p class="text-slate-700">{{ $destination }}</p>
                        <p class="text-sm text-slate-500">{{ $this->calculateDuration() }} hari</p>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Maksud Perjalanan</h3>
                    <p class="text-slate-700">{{ $purpose }}</p>
                </div>

                <div class="bg-blue-50 rounded-xl p-4">
                    <h3 class="font-semibold text-slate-800 mb-3">Estimasi Biaya</h3>
                    <p class="text-2xl font-bold text-blue-600">Rp
                        {{ number_format($this->calculateEstimatedCost(), 0, ',', '.') }}</p>
                </div>
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-8 pt-6 border-t border-slate-200">
            @if ($step > 1)
                <button wire:click="prevStep"
                    class="px-6 py-2.5 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 transition-colors">
                    Sebelumnya
                </button>
            @else
                <div></div>
            @endif

            @if ($step < 4)
                <button wire:click="nextStep"
                    class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                    Selanjutnya
                </button>
            @else
                <button wire:click="submit"
                    class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-colors">
                    Simpan SPD
                </button>
            @endif
        </div>
    </div>
</div>
