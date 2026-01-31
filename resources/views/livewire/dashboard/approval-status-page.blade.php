<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Approval Status</h1>
        <p class="text-slate-600 mt-1">Track your SPD approval progress</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-2">Pending Approval</p>
            <p class="text-4xl font-bold text-orange-600">{{ $this->stats['pending'] }}</p>
            <p class="text-slate-500 text-xs mt-2">Menunggu persetujuan</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-2">Approved</p>
            <p class="text-4xl font-bold text-emerald-600">{{ $this->stats['approved'] }}</p>
            <p class="text-slate-500 text-xs mt-2">Sudah disetujui</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-2">Rejected</p>
            <p class="text-4xl font-bold text-red-600">{{ $this->stats['rejected'] }}</p>
            <p class="text-slate-500 text-xs mt-2">Ditolak</p>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <input type="text" wire:model.live="searchQuery" placeholder="Cari SPT/SPD number..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200" />
    </div>

    <!-- Pending Approvals List -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Pending Approvals</h2>
            <span class="px-3 py-1 bg-orange-50 text-orange-700 rounded-full text-sm font-bold">{{ $this->pendingApprovals->count() }}</span>
        </div>

        @forelse($this->pendingApprovals as $approval)
            <div wire:click="selectSpd({{ $approval->spd->id }})" class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all cursor-pointer">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <h3 class="text-lg font-bold text-slate-900">{{ $approval->spd->spt_number ?? 'N/A' }}</h3>
                            <span class="px-2 py-1 bg-orange-50 text-orange-700 rounded text-xs font-bold">PENDING</span>
                        </div>
                        <p class="text-slate-600 text-sm mb-2">
                            <span class="font-semibold">{{ $approval->spd->employee?->user?->name ?? 'Unknown' }}</span>
                            • {{ $approval->spd->travel_type ?? 'Unknown' }}
                        </p>
                        <p class="text-slate-500 text-xs">
                            Submitted {{ $approval->spd->created_at?->diffForHumans() }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-slate-900 font-bold">Level {{ $approval->level }}</p>
                        <p class="text-slate-600 text-sm">{{ $approval->approver?->name ?? 'Pending' }}</p>
                    </div>
                </div>

                <!-- Approval Timeline -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        @for($i = 1; $i <= 4; $i++)
                            <div class="flex-1">
                                <div class="h-1.5 {{ $i <= $approval->level ? 'bg-orange-400' : 'bg-slate-200' }} rounded-full mb-1"></div>
                                <p class="text-center text-xs text-slate-500">L{{ $i }}</p>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl p-8 shadow-sm border border-slate-100 text-center text-slate-500">
                <svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="font-semibold">No pending approvals</p>
                <p class="text-sm mt-1">All your SPDs are approved or pending review</p>
            </div>
        @endforelse
    </div>

    <!-- Recent Approvals -->
    @if($this->recentApprovals->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-slate-900 mb-4">Recent Activity</h2>
            <div class="space-y-2">
                @foreach($this->recentApprovals as $approval)
                    <div class="bg-white rounded-lg p-4 border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-slate-900 font-semibold">{{ $approval->spd->spt_number }}</p>
                            <p class="text-slate-600 text-sm">{{ $approval->updated_at?->diffForHumans() }}</p>
                        </div>
                        <span class="px-2 py-1 {{ $approval->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} rounded text-xs font-bold">
                            {{ strtoupper($approval->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
