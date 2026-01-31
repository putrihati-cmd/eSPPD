<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <div class="mb-4">
        <h3 class="text-lg font-bold text-slate-900">📊 Tren SPD (6 Bulan Terakhir)</h3>
        <p class="text-sm text-slate-500 mt-1">Jumlah pengajuan per bulan</p>
    </div>

    <!-- Chart Container with ASCII-style bars -->
    <div class="space-y-4">
        @php
            $maxValue = max(
                array_merge($chartData['approved'] ?? [], $chartData['pending'] ?? [], $chartData['rejected'] ?? [])
            ) ?: 1;
        @endphp

        @foreach($chartLabels as $index => $label)
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                <span class="text-xs text-slate-500">
                    ✓ {{ $chartData['approved'][$index] ?? 0 }} | 
                    ⏳ {{ $chartData['pending'][$index] ?? 0 }} | 
                    ✗ {{ $chartData['rejected'][$index] ?? 0 }}
                </span>
            </div>
            <div class="flex gap-1 h-8">
                <!-- Approved -->
                @if(($chartData['approved'][$index] ?? 0) > 0)
                <div class="flex-1 bg-emerald-500 rounded" style="width: {{ (($chartData['approved'][$index] ?? 0) / $maxValue) * 100 }}%; min-width: 4px;" 
                    title="Approved: {{ $chartData['approved'][$index] ?? 0 }}"></div>
                @endif
                
                <!-- Pending -->
                @if(($chartData['pending'][$index] ?? 0) > 0)
                <div class="flex-1 bg-orange-500 rounded" style="width: {{ (($chartData['pending'][$index] ?? 0) / $maxValue) * 100 }}%; min-width: 4px;" 
                    title="Pending: {{ $chartData['pending'][$index] ?? 0 }}"></div>
                @endif
                
                <!-- Rejected -->
                @if(($chartData['rejected'][$index] ?? 0) > 0)
                <div class="flex-1 bg-red-500 rounded" style="width: {{ (($chartData['rejected'][$index] ?? 0) / $maxValue) * 100 }}%; min-width: 4px;" 
                    title="Rejected: {{ $chartData['rejected'][$index] ?? 0 }}"></div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Legend -->
    <div class="mt-6 pt-4 border-t border-slate-200 flex gap-4">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-emerald-500 rounded"></div>
            <span class="text-xs text-slate-600">Disetujui</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-orange-500 rounded"></div>
            <span class="text-xs text-slate-600">Menunggu</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-red-500 rounded"></div>
            <span class="text-xs text-slate-600">Ditolak</span>
        </div>
    </div>
</div>
