@extends('layouts.app')

@section('title', 'Riwayat Revisi - ' . $spd->spt_number)

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

        <!-- Header -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Riwayat Revisi SPPD</h2>
                    <p class="text-slate-500 mt-1">{{ $spd->spt_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500">Total Revisi</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $spd->revision_count ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="font-semibold text-slate-800 mb-6">Timeline Revisi & Approval</h3>

            <div class="relative">
                <!-- Timeline line -->
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200"></div>

                <div class="space-y-6">
                    <!-- Initial Submission -->
                    <div class="relative flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center z-10">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="flex-1 bg-blue-50 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-blue-800">Pengajuan Awal</p>
                                    <p class="text-sm text-blue-600 mt-1">
                                        Oleh: {{ $spd->employee->name ?? 'Unknown' }}
                                    </p>
                                </div>
                                <span class="text-xs text-blue-500">
                                    {{ $spd->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Revision History -->
                    @if (!empty($revisionHistory))
                        @foreach ($revisionHistory as $index => $revision)
                            <!-- Rejection Event -->
                            @if (!empty($revision['previous_rejection_reason']))
                                <div class="relative flex gap-4">
                                    <div
                                        class="flex-shrink-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center z-10">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 bg-red-50 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium text-red-800">Ditolak</p>
                                                <p class="text-sm text-red-600 mt-1">
                                                    "{{ $revision['previous_rejection_reason'] }}"
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Revision Event -->
                            <div class="relative flex gap-4">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center z-10">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                                <div class="flex-1 bg-amber-50 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-amber-800">Revisi #{{ $revision['version'] }}</p>
                                            <p class="text-sm text-amber-600 mt-1">{{ $revision['notes'] }}</p>
                                            @if (!empty($revision['revised_by']))
                                                <p class="text-xs text-amber-500 mt-2">
                                                    NIP: {{ $revision['revised_by'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="text-xs text-amber-500">
                                            {{ \Carbon\Carbon::parse($revision['revised_at'])->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <!-- Approval History -->
                    @foreach ($spd->approvals()->orderBy('created_at')->get() as $approval)
                        <div class="relative flex gap-4">
                            <div @class([
                                'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center z-10',
                                'bg-emerald-500' => $approval->status === 'approved',
                                'bg-red-500' => $approval->status === 'rejected',
                                'bg-slate-400' => !in_array($approval->status, ['approved', 'rejected']),
                            ])>
                                @if ($approval->status === 'approved')
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                @elseif($approval->status === 'rejected')
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                            </div>
                            <div @class([
                                'flex-1 rounded-lg p-4',
                                'bg-emerald-50' => $approval->status === 'approved',
                                'bg-red-50' => $approval->status === 'rejected',
                                'bg-slate-50' => !in_array($approval->status, ['approved', 'rejected']),
                            ])>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p @class([
                                            'font-medium',
                                            'text-emerald-800' => $approval->status === 'approved',
                                            'text-red-800' => $approval->status === 'rejected',
                                            'text-slate-800' => !in_array($approval->status, ['approved', 'rejected']),
                                        ])>
                                            {{ ucfirst($approval->status) }} - Level {{ $approval->level ?? 1 }}
                                        </p>
                                        @if ($approval->notes)
                                            <p @class([
                                                'text-sm mt-1',
                                                'text-emerald-600' => $approval->status === 'approved',
                                                'text-red-600' => $approval->status === 'rejected',
                                                'text-slate-600' => !in_array($approval->status, ['approved', 'rejected']),
                                            ])>
                                                {{ $approval->notes }}
                                            </p>
                                        @endif
                                        @if ($approval->approver)
                                            <p @class([
                                                'text-xs mt-2',
                                                'text-emerald-500' => $approval->status === 'approved',
                                                'text-red-500' => $approval->status === 'rejected',
                                                'text-slate-500' => !in_array($approval->status, ['approved', 'rejected']),
                                            ])>
                                                {{ $approval->approver->name ?? 'Unknown' }}
                                            </p>
                                        @endif
                                    </div>
                                    <span @class([
                                        'text-xs',
                                        'text-emerald-500' => $approval->status === 'approved',
                                        'text-red-500' => $approval->status === 'rejected',
                                        'text-slate-500' => !in_array($approval->status, ['approved', 'rejected']),
                                    ])>
                                        {{ $approval->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Current Status -->
                    @if ($spd->status === 'approved')
                        <div class="relative flex gap-4">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-emerald-600 rounded-full flex items-center justify-center z-10">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 bg-emerald-100 rounded-lg p-4">
                                <p class="font-medium text-emerald-800">SPPD Disetujui (Final)</p>
                                @if ($spd->approved_at)
                                    <p class="text-sm text-emerald-600 mt-1">
                                        {{ $spd->approved_at->format('d F Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
