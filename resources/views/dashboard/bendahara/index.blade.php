<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Bendahara
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Pending Verification --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Menunggu Verifikasi</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_verification'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Pending Payment --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Menunggu Pembayaran</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_payment'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Paid This Month --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Dibayar Bulan Ini</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $stats['paid_this_month'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Total Budget Used --}}
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Penggunaan</p>
                            <p class="text-2xl font-bold text-gray-800">Rp
                                {{ number_format($stats['total_budget_used'], 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Pending SPD List --}}
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">SPD Menunggu Verifikasi</h3>
                            <a href="{{ route('finance.verification') }}"
                                class="text-sm text-primary-600 hover:text-primary-700">
                                Lihat Semua →
                            </a>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($pendingSpd as $spd)
                            <div class="p-4 hover:bg-gray-50 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $spd->user->name ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">{{ $spd->destination->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-400">{{ $spd->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-800">Rp
                                            {{ number_format($spd->total_cost ?? 0, 0, ',', '.') }}</p>
                                        <a href="{{ route('finance.verification') }}"
                                            class="text-xs text-primary-600 hover:underline">
                                            Verifikasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p>Tidak ada SPD yang menunggu verifikasi</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Budget Status --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Status Anggaran</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($budgetStatus as $budget)
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">{{ $budget->unit_name }}</span>
                                    <span class="text-xs text-gray-500">
                                        {{ number_format(($budget->used / max($budget->allocated, 1)) * 100, 0) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full"
                                        style="width: {{ min(($budget->used / max($budget->allocated, 1)) * 100, 100) }}%">
                                    </div>
                                </div>
                                <div class="flex justify-between mt-1 text-xs text-gray-500">
                                    <span>Terpakai: Rp {{ number_format($budget->used, 0, ',', '.') }}</span>
                                    <span>Sisa: Rp {{ number_format($budget->remaining, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-gray-500">
                                <p>Belum ada data anggaran</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('finance.verification') }}"
                        class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
                        <svg class="w-6 h-6 text-amber-600 mr-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium text-gray-700">Verifikasi SPD</span>
                    </a>
                    <a href="{{ route('finance.payment') }}"
                        class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                        <span class="font-medium text-gray-700">Proses Bayar</span>
                    </a>
                    <a href="{{ route('finance.report') }}"
                        class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="font-medium text-gray-700">Laporan</span>
                    </a>
                    <a href="#"
                        class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                        <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="font-medium text-gray-700">Pengaturan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
