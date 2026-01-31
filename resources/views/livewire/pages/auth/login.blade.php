<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $nip = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        $emailToAuth = str_contains($this->nip, '@') ? $this->nip : $this->nip . '@uinsaizu.ac.id';

        if (!Auth::attempt(['email' => $emailToAuth, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'nip' => 'NIP atau password salah.',
            ]);
        }

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen flex flex-col lg:flex-row bg-[#009CA6] relative overflow-hidden font-['Inter']">

    <!-- Islamic Pattern Overlay -->
    <div class="absolute inset-0 z-0 opacity-10 pointer-events-none"
        style="background-image: url('https://mgx-backend-cdn.metadl.com/generate/images/938208/2026-01-29/87c6499f-dd64-42de-9c7b-3b5ebcf08f74.png'); background-size: cover; background-position: center;">
    </div>

    <!-- Hero Content (Left) -->
    <div class="hidden lg:flex flex-1 flex-col justify-center px-16 xl:px-24 z-10 text-white">
        <div class="max-w-2xl">
            <!-- Badge -->
            <span
                class="inline-block px-3 py-1 rounded-full bg-[#D4E157] text-[#1A1A1A] text-xs font-bold mb-8 uppercase tracking-widest">
                UIN SAIZU Purwokerto
            </span>

            <!-- Main Title -->
            <h1 class="text-[56px] font-extrabold leading-[1.1] tracking-tight mb-6">
                Sistem Informasi <br />
                <span class="text-[#D4E157]">Perjalanan Dinas</span>
            </h1>

            <!-- Description -->
            <p class="text-[18px] text-white/90 font-medium max-w-xl leading-relaxed mb-10">
                Efisiensi Pengajuan dan Pelaporan Perjalanan Dinas UIN Saizu Purwokerto dengan sistem digital yang
                modern dan terintegrasi
            </p>

            <!-- Buttons -->
            <div class="flex items-center gap-4 mb-16">
                <a href="#"
                    class="h-[48px] inline-flex items-center px-8 rounded-lg bg-[#D4E157] text-[#1A1A1A] font-bold text-sm shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">
                    Pelajari Lebih Lanjut
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
                <a href="#"
                    class="h-[48px] inline-flex items-center px-8 rounded-lg border-2 border-white text-white font-bold text-sm hover:bg-white/10 transition-colors">
                    Panduan Pengguna
                </a>
            </div>

            <!-- Stats Section -->
            <div class="flex items-start gap-12 border-t border-white/20 pt-8">
                <div>
                    <p class="text-[32px] font-bold text-[#D4E157] leading-none">500+</p>
                    <p class="text-sm text-white/80 mt-2 font-medium">Perjalanan Dinas</p>
                </div>
                <div>
                    <p class="text-[32px] font-bold text-[#D4E157] leading-none">50+</p>
                    <p class="text-sm text-white/80 mt-2 font-medium">Dosen & Staff</p>
                </div>
                <div>
                    <p class="text-[32px] font-bold text-[#D4E157] leading-none">99%</p>
                    <p class="text-sm text-white/80 mt-2 font-medium">Kepuasan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Area (Right) -->
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12 z-20">
        <div class="w-full max-w-[498px] bg-white rounded-lg shadow-atoms-card p-10 py-12 relative overflow-hidden">
            <div class="text-center mb-10">
                <img src="{{ asset('images/logo.png') }}" alt="Logo UIN SAIZU" class="h-[80px] w-auto mx-auto mb-6">
                <h2 class="text-2xl font-bold text-[#1A1A1A]">Login e-SPPD</h2>
                <p class="text-slate-500 text-sm mt-1">Masuk ke sistem perjalanan dinas</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                <!-- Username / NIP -->
                <div class="space-y-1.5">
                    <label for="nip" class="text-sm font-semibold text-slate-900">Username / NIP</label>
                    <input wire:model="nip" id="nip"
                        class="block w-full h-[44px] px-4 bg-white border border-[#009CA6]/30 text-slate-900 text-sm rounded-md focus:ring-2 focus:ring-[#009CA6] focus:border-[#009CA6] transition-colors placeholder:text-slate-400"
                        type="text" name="nip" required autofocus autocomplete="username"
                        placeholder="Masukkan username atau NIP" />
                    <x-input-error :messages="$errors->get('nip')" class="text-xs text-red-500 font-medium" />
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label for="password" class="text-sm font-semibold text-slate-900">Password</label>
                    <div class="relative">
                        <input wire:model="password" id="password" type="password"
                            class="block w-full h-[44px] px-4 bg-white border border-[#009CA6]/30 text-slate-900 text-sm rounded-md focus:ring-2 focus:ring-[#009CA6] focus:border-[#009CA6] transition-colors placeholder:text-slate-400"
                            name="password" required autocomplete="current-password" placeholder="Masukkan password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="text-xs text-red-500 font-medium" />
                </div>

                <!-- Remember & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label for="remember" class="inline-flex items-center cursor-pointer">
                        <input id="remember" type="checkbox" wire:model="remember"
                            class="rounded border-gray-300 text-[#009CA6] shadow-sm focus:ring-[#009CA6]">
                        <span class="ml-2 text-sm text-slate-600">Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm font-semibold text-[#009CA6] hover:text-[#007A82] hover:underline"
                            href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center items-center h-[44px] bg-[#009CA6] text-white text-sm font-bold rounded-md shadow-md hover:bg-[#007A82] hover:shadow-lg transition-all active:scale-[0.98]">
                        Masuk
                    </button>
                </div>

                <!-- Footer Link -->
                <div class="text-center pt-2">
                    <p class="text-sm text-slate-500">
                        Belum punya akun? <a href="#" class="text-[#009CA6] font-bold hover:underline">Hubungi
                            Admin</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
