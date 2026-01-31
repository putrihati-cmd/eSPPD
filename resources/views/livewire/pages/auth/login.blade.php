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
    public bool $showPassword = false;
    public bool $isLoading = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->isLoading = true;

        $this->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        $emailToAuth = str_contains($this->nip, '@') ? $this->nip : $this->nip . '@uinsaizu.ac.id';

        if (!Auth::attempt(['email' => $emailToAuth, 'password' => $this->password], $this->remember)) {
            $this->isLoading = false;
            throw ValidationException::withMessages([
                'nip' => 'NIP atau password salah.',
            ]);
        }

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Toggle password visibility
     */
    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
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
        <div class="w-full max-w-[480px]">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-10 py-14 relative overflow-hidden">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-[#009CA6]/5 to-transparent rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-[#D4E157]/5 to-transparent rounded-full -ml-16 -mb-16"></div>

                <!-- Header -->
                <div class="relative text-center mb-12">
                    <div class="mb-6 flex justify-center">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#009CA6] to-[#007A82] blur-lg opacity-30 rounded-full"></div>
                            <img src="{{ asset('images/logo.png') }}" alt="Logo UIN SAIZU" class="relative h-20 w-auto">
                        </div>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Login e-SPPD</h2>
                    <p class="text-gray-500 text-sm">Masuk ke sistem perjalanan dinas</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <!-- Form -->
                <form wire:submit="login" class="space-y-5 relative">
                    <!-- NIP/Username -->
                    <div class="group">
                        <label for="nip" class="block text-sm font-semibold text-gray-700 mb-2.5">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-[#009CA6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Username / NIP
                            </span>
                        </label>
                        <div class="relative">
                            <input wire:model="nip" id="nip"
                                class="block w-full h-12 px-4 pl-12 bg-white border-2 border-gray-200 text-gray-900 text-sm rounded-lg focus:outline-none focus:border-[#009CA6] focus:ring-2 focus:ring-[#009CA6]/20 transition-all duration-200 placeholder:text-gray-400"
                                type="text" name="nip" required autofocus autocomplete="username"
                                placeholder="Masukkan NIP atau username" />
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#009CA6] opacity-0 group-focus-within:opacity-100 transition-opacity">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('nip')
                            <div class="mt-2 p-2.5 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2">
                                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="group">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2.5">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-[#009CA6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Password
                            </span>
                        </label>
                        <div class="relative">
                            <input wire:model="password" id="password"
                                type="{{ $showPassword ? 'text' : 'password' }}"
                                class="block w-full h-12 px-4 pl-12 pr-12 bg-white border-2 border-gray-200 text-gray-900 text-sm rounded-lg focus:outline-none focus:border-[#009CA6] focus:ring-2 focus:ring-[#009CA6]/20 transition-all duration-200 placeholder:text-gray-400"
                                name="password" required autocomplete="current-password" placeholder="Masukkan password" />
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#009CA6] opacity-0 group-focus-within:opacity-100 transition-opacity">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <button type="button" wire:click="togglePasswordVisibility"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#009CA6] transition-colors focus:outline-none">
                                @if ($showPassword)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"></path>
                                        <path d="M15.171 13.576l1.474 1.474a1 1 0 001.414-1.414l-14-14a1 1 0 00-1.414 1.414l1.473 1.473A10.014 10.014 0 00.458 10c1.274 4.057 5.065 7 9.542 7 2.181 0 4.322-.665 6.171-1.576z"></path>
                                    </svg>
                                @endif
                            </button>
                        </div>
                        @error('password')
                            <div class="mt-2 p-2.5 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2">
                                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-red-700">{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-2">
                        <label for="remember" class="inline-flex items-center cursor-pointer group">
                            <div class="relative">
                                <input id="remember" type="checkbox" wire:model="remember"
                                    class="sr-only peer">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-md peer-checked:border-[#009CA6] peer-checked:bg-[#009CA6] transition-all duration-200 group-hover:border-[#009CA6]"></div>
                                <svg class="absolute top-1 left-1 w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="ml-2.5 text-sm text-gray-600 font-medium group-hover:text-gray-900 transition-colors">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-semibold text-[#009CA6] hover:text-[#007A82] hover:underline transition-colors"
                                href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                            wire:loading.class="opacity-75 cursor-not-allowed"
                            class="w-full flex justify-center items-center h-12 bg-gradient-to-r from-[#009CA6] to-[#007A82] text-white text-sm font-bold rounded-lg shadow-lg hover:shadow-xl hover:from-[#007A82] hover:to-[#005f69] transition-all duration-200 active:scale-95 relative overflow-hidden group">

                            <span wire:loading.remove>
                                <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Masuk
                            </span>

                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sedang masuk...
                            </span>

                            <!-- Shine Effect -->
                            <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-colors"></div>
                        </button>
                    </div>

                    <!-- Footer Link -->
                    <div class="text-center pt-4">
                        <p class="text-sm text-gray-600">
                            Belum punya akun?
                            <span class="text-[#009CA6] font-bold">Hubungi Admin</span>
                        </p>
                    </div>
                </form>

                <!-- Footer Info -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-xs text-gray-400">
                        Sistem Informasi Perjalanan Dinas UIN SAIZU Purwokerto
                    </p>
                </div>
            </div>

            <!-- Support Link -->
            <div class="text-center mt-6">
                <a href="#" class="text-sm text-gray-500 hover:text-[#009CA6] transition-colors flex items-center justify-center gap-1.5">
                    <span>Butuh bantuan?</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
