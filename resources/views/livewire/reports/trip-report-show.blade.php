<div>
    <div class="mb-6">
        <a href="{{ route('spd.show', $report->spd) }}" class="text-indigo-600 hover:text-indigo-800">
            ← Kembali ke SPD
        </a>
    </div>

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

    <div class="grid grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="col-span-2 space-y-6">
            {{-- Header --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-semibold">Laporan Perjalanan Dinas</h2>
                        <p class="text-gray-600 mt-1">SPD: {{ $report->spd->spd_number }}</p>
                    </div>
                    <div>
                        @if ($report->is_verified)
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                Terverifikasi
                            </span>
                        @elseif ($report->submitted_at)
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm font-medium">
                                Menunggu Verifikasi
                            </span>
                        @else
                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">
                                Draft
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Nama Pegawai:</span>
                        <span class="font-medium">{{ $report->employee->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Unit Kerja:</span>
                        <span class="font-medium">{{ $report->employee->unit->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Tanggal Berangkat:</span>
                        <span class="font-medium">{{ $report->actual_departure_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Tanggal Kembali:</span>
                        <span class="font-medium">{{ $report->actual_return_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Lama Perjalanan:</span>
                        <span class="font-medium">{{ $report->actual_duration }} hari</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Tujuan:</span>
                        <span class="font-medium">{{ $report->spd->destination }}</span>
                    </div>
                </div>
            </div>

            {{-- Activities --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Isi Perjalanan</h3>
                <div class="space-y-4">
                    @foreach ($report->activities as $activity)
                        <div class="border-l-4 border-indigo-500 pl-4 py-2">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span
                                        class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($activity->date)->format('d F Y') }}</span>
                                    <span class="text-gray-600 ml-2">{{ $activity->time }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">
                                <span class="font-medium">Lokasi:</span> {{ $activity->location }}
                            </p>
                            <p class="text-gray-700">{{ $activity->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Outputs --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Output Perjalanan</h3>
                <ul class="list-decimal list-inside space-y-2">
                    @foreach ($report->outputs as $output)
                        <li class="text-gray-700">{{ $output->description }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Aksi</h3>
                <div class="space-y-3">
                    @if (!$report->submitted_at)
                        <button wire:click="submit" wire:confirm="Yakin ingin mengajukan laporan ini untuk verifikasi?"
                            class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Ajukan untuk Verifikasi
                        </button>
                        <a href="{{ route('reports.edit', $report) }}"
                            class="block w-full text-center bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                            Edit Laporan
                        </a>
                    @endif

                    @if ($report->submitted_at && !$report->is_verified && auth()->user()->role === 'admin')
                        <button wire:click="verify" wire:confirm="Yakin ingin memverifikasi laporan ini?"
                            class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Verifikasi Laporan
                        </button>
                    @endif

                    <a href="{{ route('reports.download', $report) }}"
                        class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Download PDF
                    </a>
                </div>
            </div>

            {{-- Verification Info --}}
            @if ($report->is_verified)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Informasi Verifikasi</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-600">Diverifikasi oleh:</span>
                            <p class="font-medium">{{ $report->verifier->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-600">Tanggal Verifikasi:</span>
                            <p class="font-medium">{{ $report->verified_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if ($report->verification_notes)
                            <div>
                                <span class="text-gray-600">Catatan:</span>
                                <p class="text-gray-700">{{ $report->verification_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
