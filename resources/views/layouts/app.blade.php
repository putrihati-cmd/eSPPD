<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3B82F6">
    <meta name="description" content="Sistem Elektronik Surat Perintah Perjalanan Dinas">

    <title>{{ $title ?? config('app.name', 'e-SPPD') }}</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>


<body class="font-sans antialiased bg-slate-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen ml-[280px] bg-slate-50 transition-all duration-300">
            <!-- Top Header -->
            <header
                class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-40">
                <div class="flex items-center gap-4 flex-1">
                    <button class="lg:hidden p-2 text-slate-500 hover:bg-slate-50 rounded-lg">
                        <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Search Bar (Clone) -->
                    <div class="relative w-full max-w-md hidden md:block">
                        <input type="text" placeholder="Cari dokumen, pengguna..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all placeholder:text-slate-400 text-slate-700">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" width="20"
                            height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <button
                        class="relative p-2 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-xl transition-colors">
                        <div class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></div>
                        <svg class="w-6 h-6" width="24" height="24" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-8">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="px-8 py-6 text-center text-sm text-slate-400 border-t border-slate-200 bg-white">
                Made with <span class="text-red-500">❤</span> by <span class="text-brand-600 font-bold">e-SPPD</span> ©
                {{ date('Y') }}
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
