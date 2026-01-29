@php
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

<aside class="fixed left-0 top-0 h-screen w-[280px] bg-white border-r border-slate-200 flex flex-col z-50">
    <!-- Logo -->
    <div class="p-6 border-b border-slate-100 flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div
                class="w-10 h-10 bg-brand-teal rounded-xl flex items-center justify-center shadow-lg shadow-brand-teal/30">
                <img src="{{ asset('images/logo.png') }}" alt="Logo"
                    class="w-7 h-7 object-contain brightness-0 invert">
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800 tracking-tight">e-SPPD</h1>
                <p class="text-[10px] text-brand-teal font-bold uppercase tracking-wider">UIN SAIZU Purwokerto</p>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        {{-- DASHBOARD: Everyone --}}
        <x-sidebar-link href="{{ route('dashboard') }}" :active="$currentRoute === 'dashboard'">
            <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Dashboard
        </x-sidebar-link>

        {{-- SPD MANAGEMENT: Employee & Admin --}}
        @if (auth()->user()->hasRole('employee') || auth()->user()->isAdmin())
            <x-sidebar-link href="{{ route('spd.index') }}" :active="str_starts_with($currentRoute, 'spd.index')">
                <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Dokumen SPPD
            </x-sidebar-link>

            <x-sidebar-link href="{{ route('spd.create') }}" :active="$currentRoute === 'spd.create'">
                <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
                </svg>
                Buat SPPD Baru
            </x-sidebar-link>
        @endif

        {{-- APPROVAL: Approver & Admin --}}
        @if (auth()->user()->hasRole('approver') || auth()->user()->isAdmin())
            <x-sidebar-link href="{{ route('approvals.index') }}" :active="str_starts_with($currentRoute, 'approvals.')">
                <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Approval
                @php
                    $pendingCount = \App\Models\Approval::pending()->count();
                @endphp
                @if ($pendingCount > 0)
                    <span
                        class="ml-auto bg-brand-teal text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm shadow-brand-teal/20">
                        {{ $pendingCount }}
                    </span>
                @endif
            </x-sidebar-link>
        @endif

        {{-- REPORTS: Employee & Admin --}}
        @if (auth()->user()->hasRole('employee') || auth()->user()->isAdmin())
            <x-sidebar-link href="{{ route('reports.index') }}" :active="str_starts_with($currentRoute, 'reports.')">
                <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Laporan
            </x-sidebar-link>
        @endif

        {{-- ADMIN ONLY --}}
        @if (auth()->user()->isAdmin())
            <div class="pt-6 mt-2">
                <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Administrasi</p>

                <x-sidebar-link href="{{ route('employees.index') }}" :active="str_starts_with($currentRoute, 'employees.')">
                    <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Pegawai
                </x-sidebar-link>

                <x-sidebar-link href="{{ route('budgets.index') }}" :active="str_starts_with($currentRoute, 'budgets.')">
                    <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Anggaran
                </x-sidebar-link>

                <x-sidebar-link href="{{ route('settings.index') }}" :active="str_starts_with($currentRoute, 'settings.')">
                    <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Pengaturan
                </x-sidebar-link>

                <x-sidebar-link href="{{ route('admin.users.index') }}" :active="str_starts_with($currentRoute, 'admin.users')">
                    <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Manajemen User
                </x-sidebar-link>
            </div>
        @endif
    </nav>

    <!-- User Profile -->
    @auth
        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center text-brand-teal font-bold border border-brand-100">
                    {{ auth()->user()->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->employee?->position ?? 'User' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Logout">
                        <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @endauth
</aside>
