@extends('layouts.app')

@section('title', 'Revisi SPPD - ' . $spd->spt_number)

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('spd.show', $spd) }}"
                class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800">
                <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Detail SPD
            </a>
        </div>

        <!-- Rejection Alert -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="font-semibold text-red-800">SPPD Ditolak</h4>
                    <p class="text-red-700 mt-1">{{ $rejectionReason }}</p>
                    @if ($spd->rejected_at)
                        <p class="text-sm text-red-600 mt-2">
                            Ditolak pada: {{ $spd->rejected_at->format('d F Y H:i') }}
                            @if ($spd->rejected_by)
                                oleh NIP {{ $spd->rejected_by }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Revision Form -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h2 class="text-xl font-bold text-slate-800 mb-6">Form Revisi SPPD</h2>

            <form action="{{ route('spd.resubmit', $spd) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Revision Notes (Required) -->
                <div>
                    <label for="revision_notes" class="block text-sm font-medium text-slate-700 mb-2">
                        Catatan Revisi <span class="text-red-500">*</span>
                    </label>
                    <textarea id="revision_notes" name="revision_notes" rows="4" required minlength="10" maxlength="1000"
                        placeholder="Jelaskan perubahan apa saja yang Anda lakukan untuk mengatasi alasan penolakan..."
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('revision_notes') }}</textarea>
                    @error('revision_notes')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Destination (Optional Edit) -->
                <div>
                    <label for="destination" class="block text-sm font-medium text-slate-700 mb-2">
                        Tempat Tujuan
                    </label>
                    <input type="text" id="destination" name="destination"
                        value="{{ old('destination', $spd->destination) }}"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Purpose (Optional Edit) -->
                <div>
                    <label for="purpose" class="block text-sm font-medium text-slate-700 mb-2">
                        Maksud Perjalanan
                    </label>
                    <textarea id="purpose" name="purpose" rows="3"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('purpose', $spd->purpose) }}</textarea>
                </div>

                <!-- Dates (Optional Edit) -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="departure_date" class="block text-sm font-medium text-slate-700 mb-2">
                            Tanggal Berangkat
                        </label>
                        <input type="date" id="departure_date" name="departure_date"
                            value="{{ old('departure_date', $spd->departure_date->format('Y-m-d')) }}"
                            min="{{ now()->format('Y-m-d') }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="return_date" class="block text-sm font-medium text-slate-700 mb-2">
                            Tanggal Kembali
                        </label>
                        <input type="date" id="return_date" name="return_date"
                            value="{{ old('return_date', $spd->return_date->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Revision History (if any) -->
                @if (count($revisionHistory) > 0)
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="font-semibold text-slate-700 mb-4">Riwayat Revisi Sebelumnya</h3>
                        <div class="space-y-3">
                            @foreach ($revisionHistory as $revision)
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <span class="text-sm font-medium text-slate-600">
                                            Revisi #{{ $revision['version'] }}
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            {{ \Carbon\Carbon::parse($revision['revised_at'])->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 mt-2">{{ $revision['notes'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Ajukan Ulang
                    </button>
                    <a href="{{ route('spd.show', $spd) }}"
                        class="px-6 py-3 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
