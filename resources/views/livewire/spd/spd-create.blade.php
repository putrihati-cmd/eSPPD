<div>
    <!-- Step Indicators -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6 transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            @foreach ([1 => 'Data Pegawai', 2 => 'Detail Perjalanan', 3 => 'Anggaran', 4 => 'Konfirmasi'] as $num => $label)
                <div class="flex items-center {{ $num < 4 ? 'flex-1' : '' }}">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all
                            @if ($step > $num) bg-accent-500 text-brand-900 shadow-md shadow-accent-500/30 transform scale-105
                            @elseif($step === $num) bg-brand-600 text-white shadow-lg shadow-brand-500/40 transform scale-110
                            @else bg-slate-100 text-slate-400 border border-slate-200 @endif">
                            @if ($step > $num)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span
                            class="text-sm font-semibold {{ $step === $num ? 'text-brand-700' : 'text-slate-500' }}">{{ $label }}</span>
                    </div>
                    @if ($num < 4)
                        <div class="flex-1 h-1 mx-4 rounded-full {{ $step > $num ? 'bg-accent-400' : 'bg-slate-100' }}">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Step Content -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 transition-all">
        <!-- Step 1: Employee Selection -->
        @if ($step === 1)
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="w-1 h-8 bg-brand-500 rounded-full"></span>
                Pilih Pegawai
            </h2>

            @if ($isOwnTrip)
                {{-- Auto-filled from logged-in user --}}
                <div class="bg-brand-50 border border-brand-200 rounded-xl p-4 mb-4">
                    <div class="flex items-center gap-2 text-brand-700 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-bold">Data Anda terisi otomatis</span>
                    </div>
                    <p class="text-sm text-brand-600">Anda membuat SPD untuk diri sendiri. Data pegawai sudah terisi
                        dari akun Anda.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama</label>
                        <input type="text" value="{{ $autoFillName }}" readonly
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-100 text-slate-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIP</label>
                        <input type="text" value="{{ $autoFillNip }}" readonly
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-100 text-slate-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan</label>
                        <input type="text" value="{{ $autoFillJabatan }}" readonly
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-100 text-slate-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pangkat</label>
                        <input type="text" value="{{ $autoFillPangkat }}" readonly
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-100 text-slate-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Unit Kerja</label>
                        <input type="text" value="{{ $autoFillUnitKerja }}" readonly
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-100 text-slate-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Bank / No. Rekening</label>
                        <input type="text" value="{{ $autoFillBank }} - {{ $autoFillNoRekening }}" readonly
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-100 text-slate-700 cursor-not-allowed">
                    </div>
                </div>

                <div class="mt-4 text-sm text-slate-500">
                    <a href="#" wire:click.prevent="$set('isOwnTrip', false)"
                        class="text-brand-600 hover:text-brand-700 hover:underline font-medium">
                        Buat SPD untuk pegawai lain?
                    </a>
                </div>
            @else
                {{-- Manual employee selection --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pegawai</label>
                        <select wire:model.live="employee_id"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->nip }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <span class="text-red-500 text-sm font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($selectedEmployee)
                        <div class="bg-brand-50 rounded-xl p-4 mt-4 border border-brand-100">
                            <h3 class="font-bold text-slate-800 mb-3">Info Pegawai</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-slate-500">NIP</p>
                                    <p class="font-semibold text-slate-800">{{ $selectedEmployee->nip }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Jabatan</p>
                                    <p class="font-semibold text-slate-800">{{ $selectedEmployee->position }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Golongan</p>
                                    <p class="font-semibold text-slate-800">{{ $selectedEmployee->grade }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Unit</p>
                                    <p class="font-semibold text-slate-800">{{ $selectedEmployee->unit->name }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Followers Section -->
            <div class="mt-8 border-t border-slate-200 pt-6">
                <h3 class="font-bold text-slate-800 mb-4">Pengikut / Rombongan</h3>

                <!-- Selected Followers List -->
                @if (count($this->followersList) > 0)
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($this->followersList as $index => $follower)
                            <div
                                class="flex items-center justify-between bg-slate-50 border border-slate-200 p-3 rounded-xl hover:border-brand-200 transition-colors">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $follower->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $follower->nip }}</p>
                                </div>
                                <button wire:click="removeFollower({{ $index }})"
                                    class="text-red-500 hover:text-red-700 p-2 bg-white rounded-lg border border-red-100 shadow-sm hover:shadow-md transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl p-4 text-center mb-4">
                        <p class="text-slate-500 text-sm">Belum ada pengikut yang ditambahkan</p>
                    </div>
                @endif

                <!-- Search & Add -->
                <div class="relative">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Cari Pegawai (Min. 2
                        karakter)</label>
                    <input type="text" wire:model.live="followerSearch" placeholder="Ketik nama atau NIP..."
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">

                    @if ($searchableEmployees->count() > 0)
                        <div
                            class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                            @foreach ($searchableEmployees as $emp)
                                <button wire:click="addFollower('{{ $emp->id }}')"
                                    class="w-full text-left px-4 py-3 hover:bg-brand-50 flex items-center justify-between group border-b border-slate-50 last:border-0 transition-colors">
                                    <div>
                                        <p class="font-medium text-slate-800 group-hover:text-brand-700">
                                            {{ $emp->name }}
                                        </p>
                                        <p class="text-xs text-slate-500 group-hover:text-brand-600">
                                            {{ $emp->nip }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-brand-600 hidden group-hover:block">+
                                        Tambahkan</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif(strlen($followerSearch) >= 2)
                        <div
                            class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg p-4 text-center text-slate-500">
                            Tidak ditemukan pegawai dengan nama tersebut.
                        </div>
                    @endif
                </div>
            </div>
        @endif


        <!-- Step 2: Travel Details -->
        @if ($step === 2)
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="w-1 h-8 bg-brand-500 rounded-full"></span>
                Detail Perjalanan
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tujuan</label>
                    <input type="text" wire:model="destination" placeholder="Contoh: Jakarta, DKI Jakarta"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    @error('destination')
                        <span class="text-red-500 text-sm font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Maksud Perjalanan</label>
                    <textarea wire:model="purpose" rows="3" placeholder="Jelaskan maksud perjalanan dinas..."
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                    @error('purpose')
                        <span class="text-red-500 text-sm font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Undangan (Opsional)</label>
                    <input type="text" wire:model="invitation_number" placeholder="Contoh: B-123/ABC/2026"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Berangkat</label>
                        <input type="date" wire:model="departure_date"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        @error('departure_date')
                            <span class="text-red-500 text-sm font-medium">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Kembali</label>
                        <input type="date" wire:model="return_date"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        @error('return_date')
                            <span class="text-red-500 text-sm font-medium">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Transportasi</label>
                        <select wire:model="transport_type"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <option value="pesawat">Pesawat</option>
                            <option value="kereta">Kereta Api</option>
                            <option value="bus">Bus</option>
                            <option value="kapal">Kapal</option>
                            <option value="mobil_dinas">Mobil Dinas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penginapan</label>
                        <label class="flex items-center gap-3 mt-3 cursor-pointer group">
                            <input type="checkbox" wire:model="needs_accommodation"
                                class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 transition-colors">
                            <span class="text-slate-700 group-hover:text-brand-700 transition-colors">Memerlukan
                                Penginapan</span>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 3: Budget -->
        @if ($step === 3)
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="w-1 h-8 bg-brand-500 rounded-full"></span>
                Sumber Anggaran
            </h2>

            <div class="space-y-4">
                @foreach ($budgets as $budget)
                    <label
                        class="block p-4 border-2 rounded-xl cursor-pointer transition-all hover:shadow-md
                        {{ $budget_id === $budget->id ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-brand-300' }}">
                        <div class="flex items-start gap-3">
                            <input type="radio" wire:model="budget_id" value="{{ $budget->id }}"
                                class="mt-1 w-5 h-5 text-brand-600 focus:ring-brand-500 border-slate-300">
                            <div class="flex-1">
                                <p class="font-bold text-slate-800">{{ $budget->code }}</p>
                                <p class="text-sm text-slate-600">{{ $budget->name }}</p>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="text-xs text-slate-500 font-medium">
                                        Tersedia: <span class="font-semibold text-emerald-600">Rp
                                            {{ number_format($budget->available_budget, 0, ',', '.') }}</span>
                                    </span>
                                    <span class="text-xs text-slate-500 font-medium">
                                        Terpakai: {{ $budget->usage_percentage }}%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 mt-2">
                                    <div class="bg-brand-500 h-2 rounded-full transition-all duration-500"
                                        style="width: {{ $budget->usage_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
                @error('budget_id')
                    <span class="text-red-500 text-sm font-medium">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <!-- Step 4: Confirmation -->
        @if ($step === 4)
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="w-1 h-8 bg-brand-500 rounded-full"></span>
                Konfirmasi SPD
            </h2>

            <div class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-3">Pegawai</h3>
                        <p class="text-slate-700 font-medium">{{ $selectedEmployee->name ?? '-' }}</p>
                        <p class="text-sm text-slate-500">{{ $selectedEmployee->position ?? '-' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <h3 class="font-bold text-slate-800 mb-3">Tujuan</h3>
                        <p class="text-slate-700 font-medium">{{ $destination }}</p>
                        <p class="text-sm text-slate-500">{{ $this->calculateDuration() }} hari</p>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <h3 class="font-bold text-slate-800 mb-3">Maksud Perjalanan</h3>
                    <p class="text-slate-700 leading-relaxed">{{ $purpose }}</p>
                </div>

                <div class="bg-brand-50 rounded-xl p-6 border border-brand-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 mb-1">Estimasi Biaya</h3>
                        <p class="text-sm text-brand-600">Perhitungan sementara</p>
                    </div>
                    <p class="text-3xl font-bold text-brand-600">Rp
                        {{ number_format($this->calculateEstimatedCost(), 0, ',', '.') }}</p>
                </div>
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-8 pt-6 border-t border-slate-200">
            @if ($step > 1)
                <button wire:click="prevStep"
                    class="px-6 py-2.5 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 hover:border-slate-400 font-medium transition-colors">
                    Sebelumnya
                </button>
            @else
                <div></div>
            @endif

            @if ($step < 4)
                <button wire:click="nextStep"
                    class="px-6 py-2.5 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all hover:scale-105">
                    Selanjutnya
                </button>
            @else
                <button wire:click="submit"
                    class="px-6 py-2.5 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    Simpan SPD
                </button>
            @endif
        </div>
    </div>
</div>
