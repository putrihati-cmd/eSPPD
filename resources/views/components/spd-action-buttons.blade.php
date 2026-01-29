{{-- btn.md Implementation: SPD Action Buttons Component --}}
{{-- Usage: <x-spd-action-buttons :spd="$spd" /> --}}

<div class="flex flex-wrap gap-2" x-data="{ processing: false }">

    {{-- btn-detail: Always visible --}}
    @if ($showDetail)
        <a href="{{ route('spd.show', $spd) }}"
            class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Detail
        </a>
    @endif

    {{-- btn-edit: Only draft, only owner --}}
    @if ($showEdit)
        <a href="{{ route('spd.edit', $spd) }}"
            class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
        </a>
    @endif

    {{-- btn-cancel: draft/submitted owner OR override --}}
    @if ($showCancel)
        <button type="button"
            x-on:click="if(confirm('Yakin batalkan SPPD ini?')) { processing = true; $wire.cancelSpd('{{ $spd->id }}') }"
            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition"
            :disabled="processing">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Batalkan
        </button>
    @endif

    {{-- btn-download-st: Only approved --}}
    @if ($showDownloadSt)
        <a href="{{ route('spd.pdf.spt', $spd) }}"
            class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition"
            target="_blank">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Surat Tugas
        </a>
    @endif

    {{-- btn-download-spd: Only approved --}}
    @if ($showDownloadSpd)
        <a href="{{ route('spd.pdf.spd', $spd) }}"
            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition"
            target="_blank">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            SPD PDF
        </a>
    @endif

    {{-- btn-input-lpj: approved AND no LPJ yet --}}
    @if ($showInputLpj)
        <a href="{{ route('reports.create', $spd) }}"
            class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Input LPJ
        </a>
    @endif

    {{-- btn-view-lpj: has LPJ --}}
    @if ($showViewLpj)
        <a href="{{ route('reports.show', $spd->report) }}"
            class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Lihat LPJ
        </a>
    @endif

    {{-- btn-approve: pending AND current approver --}}
    @if ($showApprove)
        <button type="button" x-on:click="$dispatch('open-modal', 'approve-{{ $spd->id }}')"
            class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Approve
        </button>
    @endif

    {{-- btn-reject: pending AND current approver --}}
    @if ($showReject)
        <button type="button" x-on:click="$dispatch('open-modal', 'reject-{{ $spd->id }}')"
            class="inline-flex items-center px-3 py-1.5 bg-rose-600 text-white text-sm rounded-lg hover:bg-rose-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Reject
        </button>
    @endif

    {{-- btn-override-cancel: Dekan+ force cancel --}}
    @if ($showOverrideCancel && !$showCancel)
        <button type="button" x-on:click="$dispatch('open-modal', 'override-cancel-{{ $spd->id }}')"
            class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 transition"
            title="Override: Batalkan SPPD (Dekan+)">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            Override Cancel
        </button>
    @endif

    {{-- btn-resubmit: Only rejected AND owner (dari fitur.md) --}}
    @if ($spd->status === 'rejected' && Auth::id() === $spd->employee?->user_id)
        <a href="{{ route('spd.revisi', $spd) }}"
            class="inline-flex items-center px-3 py-1.5 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Ajukan Ulang
        </a>
    @endif

    {{-- btn-history: Shows revision history (owner, approver, or admin) --}}
    @if ($spd->revision_count > 0)
        <a href="{{ route('spd.history', $spd) }}"
            class="inline-flex items-center px-3 py-1.5 bg-slate-600 text-white text-sm rounded-lg hover:bg-slate-700 transition"
            title="Riwayat Revisi ({{ $spd->revision_count }}x)">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Revisi ({{ $spd->revision_count }})
        </a>
    @endif

</div>
