<div>
    <!-- Welcome Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-brand-teal to-brand-dark rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Dashboard Administrator</h2>
                    <p class="text-white/90 font-medium">Pantau kesehatan sistem dan aktivitas perjalanan dinas</p>
                </div>
                <div class="hidden md:block">
                    <span
                        class="px-4 py-2 bg-brand-lime/20 border border-brand-lime/30 rounded-lg text-brand-lime text-xs font-bold uppercase tracking-widest">
                        System Health: Optimal
                    </span>
                </div>
            </div>
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
            <div class="absolute -right-5 -bottom-5 w-24 h-24 bg-brand-lime/10 rounded-full"></div>
        </div>
    </div>

    <!-- Admin Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-brand-teal hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Total Pengguna</p>
            <p class="text-3xl font-extrabold text-slate-800">24</p>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-brand-teal hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Total SPD</p>
            <p class="text-3xl font-extrabold text-slate-800">{{ $totalSpdThisMonth }}</p>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-orange-500 hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Menunggu Approval</p>
            <p class="text-3xl font-extrabold text-orange-500">{{ $pendingApproval }}</p>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 border-l-4 border-l-emerald-500 hover:shadow-md transition-all">
            <p class="text-sm font-bold text-slate-500 mb-1">Anggaran Terpakai</p>
            <p class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($usedBudget, 0, ',', '.') }}</p>
        </div>
    </div>
</div>
