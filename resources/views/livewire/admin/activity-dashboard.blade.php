<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Activity Dashboard</h1>
        <p class="text-slate-600 mt-1">Analisis aktivitas pengguna dan perubahan sistem</p>
    </div>

    <!-- Period Filter -->
    <div class="mb-6 flex gap-3">
        @foreach(['7days' => 'Last 7 Days', '30days' => 'Last 30 Days', '90days' => 'Last 90 Days', 'alltime' => 'All Time'] as $val => $label)
            <button wire:click="$set('period', '{{ $val }}')" class="px-4 py-2.5 rounded-lg font-semibold transition-all {{ $period === $val ? 'bg-brand-teal text-white shadow-md' : 'bg-white text-slate-900 border border-slate-200 hover:border-slate-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- User Filter -->
    <div class="mb-6">
        <select wire:model.live="selectedUserId" class="px-4 py-2.5 border border-slate-200 rounded-lg bg-white font-medium">
            <option value="">All Users</option>
            @foreach($this->users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Stats Cards -->
    @php $stats = $this->activityStats @endphp
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-1">Total Activities</p>
            <p class="text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-1">Active Users</p>
            <p class="text-3xl font-bold text-slate-900">{{ $stats['users'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-1">Entity Types</p>
            <p class="text-3xl font-bold text-slate-900">{{ $stats['entities'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-1">Creates</p>
            <p class="text-3xl font-bold text-emerald-600">{{ $stats['actions']['create'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100">
            <p class="text-slate-600 text-sm font-semibold mb-1">Updates</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['actions']['update'] }}</p>
        </div>
    </div>

    <!-- Actions Summary -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center">
            <p class="text-slate-600 text-xs font-semibold mb-1">Deletes</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['actions']['delete'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center">
            <p class="text-slate-600 text-xs font-semibold mb-1">Approvals</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['actions']['approve'] }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 text-center">
            <p class="text-slate-600 text-xs font-semibold mb-1">Rejections</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['actions']['reject'] }}</p>
        </div>
    </div>

    <!-- Top Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Top Users</h3>
            <div class="space-y-3">
                @forelse($this->topUsers as $item)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-900 font-medium">{{ $item['user']?->name ?? 'Unknown' }}</span>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm font-bold">{{ $item['count'] }}</span>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm">Tidak ada data</p>
                @endforelse
            </div>
        </div>

        <!-- Top Entities -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Most Changed Entities</h3>
            <div class="space-y-3">
                @forelse($this->topEntities as $entity)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-900 font-medium">{{ $entity->entity }}</span>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-sm font-bold">{{ $entity->total }}</span>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm">Tidak ada data</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Recent Activities</h3>
        </div>
        <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
            @forelse($this->userActivities->take(20) as $log)
                <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-slate-900 font-semibold">
                                <span class="text-slate-600">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="text-slate-900">
                                    @switch($log->action)
                                        @case('create')
                                            membuat
                                        @break
                                        @case('update')
                                            mengubah
                                        @break
                                        @case('delete')
                                            menghapus
                                        @break
                                        @case('approve')
                                            menyetujui
                                        @break
                                        @case('reject')
                                            menolak
                                        @break
                                        @default
                                            {{ $log->action }}
                                    @endswitch
                                </span>
                                <span class="text-slate-600">{{ $log->entity }}</span>
                            </p>
                            <p class="text-slate-500 text-sm mt-1">{{ $log->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-bold whitespace-nowrap">
                            {{ $log->ip_address }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-500">
                    Tidak ada aktivitas ditemukan
                </div>
            @endforelse
        </div>
    </div>
</div>
