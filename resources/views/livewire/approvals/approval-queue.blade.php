<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Antrian Approval</h2>
            <p class="text-sm text-slate-500">Kelola persetujuan SPPD</p>
        </div>
        <button wire:click="$set('showDelegateModal', true)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Kelola Delegasi
        </button>
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

    <!-- Search and Bulk Actions -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live="search" placeholder="Cari nomor SPPD atau tujuan..."
                    class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            @if (count($selectedIds) > 0)
                <div class="flex gap-2">
                    <input type="text" wire:model="bulkNotes" placeholder="Catatan (opsional)"
                        class="px-4 py-2 border border-slate-200 rounded-xl">
                    <button wire:click="bulkApprove"
                        class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700">
                        Approve {{ count($selectedIds) }} SPPD
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Pending Approvals Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">No. SPPD</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Pegawai</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tujuan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Biaya</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Menunggu</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pendingApprovals as $approval)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $approval->id }}"
                                class="rounded">
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('spd.show', $approval->spd) }}"
                                class="text-blue-600 hover:underline font-medium">
                                {{ $approval->spd->spd_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $approval->spd->employee->name ?? '-' }}</div>
                            <div class="text-sm text-slate-500">{{ $approval->spd->employee->nip ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-700">{{ $approval->spd->destination }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $approval->spd->departure_date?->format('d/m/Y') }} -
                            {{ $approval->spd->return_date?->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $approval->spd->formatCost() }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ $approval->created_at->diffForHumans() }}
                            @if ($approval->created_at->diffInHours(now()) > 48)
                                <span class="text-red-500">(Terlambat!)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <button wire:click="approve('{{ $approval->id }}')"
                                    class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 text-sm">
                                    Approve
                                </button>
                                <button
                                    onclick="document.getElementById('reject-{{ $approval->id }}').classList.toggle('hidden')"
                                    class="px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm">
                                    Reject
                                </button>
                            </div>
                            <!-- Reject Form -->
                            <div id="reject-{{ $approval->id }}" class="hidden mt-2">
                                <input type="text" id="notes-{{ $approval->id }}" placeholder="Alasan penolakan..."
                                    class="w-full px-2 py-1 text-sm border rounded mb-1">
                                <button
                                    onclick="$wire.reject('{{ $approval->id }}', document.getElementById('notes-{{ $approval->id }}').value)"
                                    class="w-full px-2 py-1 bg-red-600 text-white rounded text-sm">
                                    Konfirmasi Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                            Tidak ada SPPD yang menunggu approval
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $pendingApprovals->links() }}
        </div>
    </div>

    <!-- My Delegates Section -->
    <div class="mt-6 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Delegasi Aktif Saya</h3>
        @if ($myDelegates->count() > 0)
            <div class="space-y-3">
                @foreach ($myDelegates as $delegate)
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                        <div>
                            <p class="font-medium text-slate-800">{{ $delegate->delegate->name ?? '-' }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $delegate->start_date->format('d/m/Y') }} -
                                {{ $delegate->end_date->format('d/m/Y') }}
                            </p>
                            <p class="text-sm text-slate-500">{{ $delegate->reason }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="px-2 py-1 text-xs rounded-full {{ $delegate->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $delegate->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @if ($delegate->is_active)
                                <button wire:click="deactivateDelegate('{{ $delegate->id }}')"
                                    class="text-red-600 hover:text-red-700 text-sm">
                                    Nonaktifkan
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-500 text-center py-4">Belum ada delegasi</p>
        @endif
    </div>

    <!-- Delegate Modal -->
    @if ($showDelegateModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
            wire:click.self="$set('showDelegateModal', false)">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Buat Delegasi Baru</h3>
                <form wire:submit="createDelegate">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Delegasikan ke</label>
                            <select wire:model="delegateEmployeeId"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                                <option value="">-- Pilih Pegawai --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->nip }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                                <input type="date" wire:model="delegateStartDate"
                                    class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Selesai</label>
                                <input type="date" wire:model="delegateEndDate"
                                    class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Alasan</label>
                            <textarea wire:model="delegateReason" rows="2" class="w-full px-4 py-2 border border-slate-200 rounded-xl"
                                placeholder="Contoh: Cuti, Dinas Luar..."></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" wire:click="$set('showDelegateModal', false)"
                            class="flex-1 px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                            Simpan Delegasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
