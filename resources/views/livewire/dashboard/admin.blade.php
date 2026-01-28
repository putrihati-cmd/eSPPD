<div>
    <!-- Welcome Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-600 to-brand-800 rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-2">Dashboard Admin</h2>
                <p class="text-brand-50 mb-4">Pantau kesehatan sistem dan aktivitas pengguna</p>
            </div>
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
        </div>
    </div>

    <!-- Admin Stats (Clone Style) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div
            class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-brand-500 hover:shadow-md transition-shadow">
            <p class="text-sm font-medium text-slate-500 mb-1">Total Pengguna</p>
            <p class="text-3xl font-bold text-slate-800">24</p>
        </div>
        <div
            class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-brand-500 hover:shadow-md transition-shadow">
            <p class="text-sm font-medium text-slate-500 mb-1">Total SPD</p>
            <p class="text-3xl font-bold text-slate-800">{{ $totalSpdThisMonth }}</p>
        </div>
        <div
            class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-orange-500 hover:shadow-md transition-shadow">
            <p class="text-sm font-medium text-slate-500 mb-1">Menunggu Approval</p>
            <p class="text-3xl font-bold text-orange-500">{{ $pendingApproval }}</p>
        </div>
        <div
            class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-emerald-500 hover:shadow-md transition-shadow">
            <p class="text-sm font-medium text-slate-500 mb-1">Anggaran Terpakai</p>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($usedBudget, 0, ',', '.') }}</p>
        </div>
    </div>
</div>
