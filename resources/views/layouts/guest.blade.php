<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50">
        <div>
            <a href="/" wire:navigate class="flex items-center gap-3 mb-6">
                <div
                    class="w-12 h-12 bg-brand-500 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/20">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">e-SPPD</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-wide">Sistem Perjalanan Dinas</p>
                </div>
            </a>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white shadow-xl shadow-slate-200/50 overflow-hidden sm:rounded-2xl border border-slate-100">
            {{ $slot }}
        </div>

        <p class="mt-8 text-sm text-slate-400">
            &copy; {{ date('Y') }} e-SPPD. All rights reserved.
        </p>
    </div>
</body>

</html>
