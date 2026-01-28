<div>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500 mb-1">Total Anggaran {{ date('Y') }}</p>
            <p class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalBudget, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500 mb-1">Terpakai</p>
            <p class="text-2xl font-bold text-orange-500">Rp {{ number_format($usedBudget, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500 mb-1">Tersedia</p>
            <p class="text-2xl font-bold text-emerald-500">Rp
                {{ number_format($totalBudget - $usedBudget, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Budget List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-lg font-semibold text-slate-800">Daftar Anggaran (MAK)</h2>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($budgets as $budget)
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $budget->code }}</p>
                            <p class="text-sm text-slate-500">{{ $budget->name }}</p>
                        </div>
                        <span
                            class="px-3 py-1 text-sm font-medium rounded-full 
                            @if ($budget->usage_percentage < 50) bg-emerald-100 text-emerald-700
                            @elseif($budget->usage_percentage < 80) bg-orange-100 text-orange-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $budget->usage_percentage }}%
                        </span>
                    </div>

                    <div class="mb-2">
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                @if ($budget->usage_percentage < 50) bg-emerald-500
                                @elseif($budget->usage_percentage < 80) bg-orange-500
                                @else bg-red-500 @endif"
                                style="width: {{ $budget->usage_percentage }}%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Terpakai: <span class="font-medium text-slate-700">Rp
                                {{ number_format($budget->used_budget, 0, ',', '.') }}</span></span>
                        <span class="text-slate-500">Total: <span class="font-medium text-slate-700">Rp
                                {{ number_format($budget->total_budget, 0, ',', '.') }}</span></span>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <p class="text-slate-500">Belum ada data anggaran</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
