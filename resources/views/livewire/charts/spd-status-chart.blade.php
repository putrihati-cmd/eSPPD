<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <div class="mb-4">
        <h3 class="text-lg font-bold text-slate-900">📈 Distribusi Status SPD</h3>
        <p class="text-sm text-slate-500 mt-1">Ringkasan status semua pengajuan</p>
    </div>

    <!-- Pie Chart using radial bars -->
    @php
        $total = array_sum($statusData);
        $colors = [
            'approved' => 'emerald',
            'pending' => 'orange',
            'rejected' => 'red',
            'draft' => 'slate',
        ];
    @endphp

    @if($total > 0)
    <div class="grid grid-cols-2 gap-6">
        <!-- Visual Pie -->
        <div class="flex items-center justify-center">
            <div class="relative w-40 h-40">
                <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                    @php $offset = 0; @endphp

                    @foreach(['approved', 'pending', 'rejected', 'draft'] as $status)
                    @php
                        $value = $statusData[$status];
                        $percentage = ($value / $total) * 100;
                        $radius = 45;
                        $circumference = 2 * pi() * $radius;
                        $dashoffset = $circumference - (($percentage / 100) * $circumference);
                    @endphp
                    @if($value > 0)
                    <circle cx="50" cy="50" r="45"
                        fill="none"
                        class="transition-all duration-300"
                        @if($status === 'approved') stroke="#10b981"
                        @elseif($status === 'pending') stroke="#f97316"
                        @elseif($status === 'rejected') stroke="#ef4444"
                        @else stroke="#cbd5e1" @endif
                        stroke-width="8"
                        stroke-dasharray="{{ (($percentage / 100) * $circumference) }}, {{ $circumference }}"
                        style="stroke-dashoffset: {{ -$offset }}; transform-origin: 50px 50px;"
                    />
                    @php $offset += (($percentage / 100) * $circumference); @endphp
                    @endif
                    @endforeach
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-slate-900">{{ $total }}</p>
                        <p class="text-xs text-slate-600">Total</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status List -->
        <div class="space-y-3">
            @foreach(['approved' => '✓ Disetujui', 'pending' => '⏳ Menunggu', 'rejected' => '✗ Ditolak', 'draft' => '📝 Draft'] as $key => $label)
            @php
                $value = $statusData[$key];
                $pct = $total > 0 ? round(($value / $total) * 100) : 0;
            @endphp
            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full
                        @if($key === 'approved') bg-emerald-500
                        @elseif($key === 'pending') bg-orange-500
                        @elseif($key === 'rejected') bg-red-500
                        @else bg-slate-400 @endif"></div>
                    <span class="text-sm font-medium text-slate-900">{{ $label }}</span>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-slate-900">{{ $value }}</p>
                    <p class="text-xs text-slate-600">{{ $pct }}%</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-slate-500">📭 Belum ada data SPD</p>
    </div>
    @endif
</div>
