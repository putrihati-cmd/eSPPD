<div>
    <div class="mb-6">
        <a href="{{ route('spd.show', $spd) }}" class="text-indigo-600 hover:text-indigo-800">
            ← Kembali ke SPD
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Laporan Perjalanan Dinas</h2>

        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="save">
            {{-- SPD Info --}}
            <div class="mb-6 bg-gray-50 p-4 rounded">
                <h3 class="font-semibold mb-2">Informasi SPD</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Nomor SPD:</span>
                        <span class="font-medium">{{ $spd->spd_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Pegawai:</span>
                        <span class="font-medium">{{ $spd->employee->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Tujuan:</span>
                        <span class="font-medium">{{ $spd->destination }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Tanggal Rencana:</span>
                        <span class="font-medium">{{ $spd->departure_date->format('d/m/Y') }} -
                            {{ $spd->return_date->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Actual Dates --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Berangkat Aktual</label>
                    <input type="date" wire:model="actual_departure_date"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('actual_departure_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kembali Aktual</label>
                    <input type="date" wire:model="actual_return_date"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('actual_return_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Activities --}}
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Isi Perjalanan (Aktivitas Harian)</h3>
                    <button type="button" wire:click="addActivity"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        + Tambah Aktivitas
                    </button>
                </div>

                @foreach ($activities as $index => $activity)
                    <div class="bg-gray-50 p-4 rounded mb-4">
                        <div class="flex justify-between items-start mb-3">
                            <span class="font-medium">Aktivitas #{{ $index + 1 }}</span>
                            @if (count($activities) > 1)
                                <button type="button" wire:click="removeActivity({{ $index }})"
                                    class="text-red-600 hover:text-red-800">
                                    Hapus
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                <input type="date" wire:model="activities.{{ $index }}.date"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error("activities.{$index}.date")
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu</label>
                                <input type="text" wire:model="activities.{{ $index }}.time"
                                    placeholder="Pukul 09.00 - 12.00 WIB"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error("activities.{$index}.time")
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <input type="text" wire:model="activities.{{ $index }}.location"
                                placeholder="Nama tempat/lokasi"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error("activities.{$index}.location")
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Kegiatan</label>
                            <textarea wire:model="activities.{{ $index }}.description" rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error("activities.{$index}.description")
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Outputs --}}
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Output Perjalanan</h3>
                    <button type="button" wire:click="addOutput"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        + Tambah Output
                    </button>
                </div>

                @foreach ($outputs as $index => $output)
                    <div class="bg-gray-50 p-4 rounded mb-3">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-medium">Output #{{ $index + 1 }}</span>
                            @if (count($outputs) > 1)
                                <button type="button" wire:click="removeOutput({{ $index }})"
                                    class="text-red-600 hover:text-red-800">
                                    Hapus
                                </button>
                            @endif
                        </div>
                        <textarea wire:model="outputs.{{ $index }}.description" rows="2"
                            placeholder="Deskripsi output/hasil perjalanan"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        @error("outputs.{$index}.description")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end gap-4">
                <a href="{{ route('spd.show', $spd) }}"
                    class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Batal
                </a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Simpan Draft
                </button>
            </div>
        </form>
    </div>
</div>
