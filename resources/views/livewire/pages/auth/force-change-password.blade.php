<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';
    public string $password_confirmation = '';
    public bool $showPassword = false;
    public bool $showPasswordConfirmation = false;
    public bool $isLoading = false;

    /**
     * Mount: Check user is logged in and NOT reset password yet
     */
    public function mount(): void
    {
        $user = Auth::user();

        // Jika user belum login atau sudah ganti password, redirect ke dashboard
        if (!$user || $user->is_password_reset) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    /**
     * Change password
     */
    public function changePassword(): void
    {
        $this->isLoading = true;

        $this->validate([
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        $user = Auth::user();

        if (!$user) {
            $this->isLoading = false;
            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($this->password),
            'is_password_reset' => true, // Set flag = user sudah ganti password
            'password_changed_at' => now(),
        ]);

        // Clear session and redirect to dashboard
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function togglePasswordConfirmationVisibility(): void
    {
        $this->showPasswordConfirmation = !$this->showPasswordConfirmation;
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div>
    <style>
        .login-vignette {
            background: radial-gradient(circle at center, transparent 0%, rgba(0, 0, 0, 0.3) 100%);
        }

        @keyframes fade-in-up {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <div
        class="min-h-screen flex flex-col lg:flex-row bg-brand-teal relative overflow-hidden font-sans selection:bg-brand-lime selection:text-black">

        <!-- Background -->
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-dark via-brand-teal to-brand-dark"></div>
            <div class="absolute inset-0 login-vignette"></div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex items-center justify-center p-6 lg:p-12 z-20">
            <div class="w-full max-w-md animate-fade-in-up">
                <div
                    class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-atoms-card p-10 lg:p-14 relative overflow-hidden border border-white/20">

                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand-teal/10 blur-[80px] rounded-full"></div>
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-brand-lime/10 blur-[80px] rounded-full"></div>

                    <!-- Header -->
                    <div class="relative text-center mb-12">
                        <div class="mb-8 flex justify-center">
                            <div class="relative inline-block">
                                <div class="absolute inset-x-0 bottom-0 h-4 bg-red-500/20 blur-xl scale-125"></div>
                                <img src="{{ asset('images/logo.png') }}" alt="Logo UIN SAIZU" class="relative w-auto"
                                    style="height: 96px; min-height: 96px; max-height: 96px;">
                            </div>
                        </div>
                        <div class="space-y-3 mb-2">
                            <h2 class="text-3xl font-black text-gray-900 tracking-tight">
                                <span class="text-red-600">⚠️</span> Ganti Password
                            </h2>
                            <p class="text-gray-500 font-medium text-sm">Login Pertama Kali - WAJIB Ganti Password</p>
                        </div>
                        <div class="mt-6 p-4 bg-orange-50 border border-orange-200 rounded-xl">
                            <p class="text-xs text-orange-900 font-medium">
                                Anda login menggunakan password default. Untuk keamanan akun, silakan ganti password dengan password yang kuat dan mudah diingat.
                            </p>
                        </div>
                    </div>

                    <form wire:submit="changePassword" class="space-y-6 relative mt-8">

                        <!-- New Password -->
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-bold text-gray-700 ml-1">Password Baru</label>
                            <div class="relative group">
                                <input wire:model="password" id="password"
                                    type="{{ $showPassword ? 'text' : 'password' }}" name="password" required
                                    class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-brand-teal focus:ring-4 focus:ring-brand-teal/10 transition-all placeholder:text-gray-400 font-medium"
                                    placeholder="Masukkan password baru" />
                                <div
                                    class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-teal transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <button type="button" wire:click="togglePasswordVisibility"
                                    class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-teal transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($showPassword)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        @endif
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-xs text-red-500 font-bold ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-sm font-bold text-gray-700 ml-1">Konfirmasi Password</label>
                            <div class="relative group">
                                <input wire:model="password_confirmation" id="password_confirmation"
                                    type="{{ $showPasswordConfirmation ? 'text' : 'password' }}" name="password_confirmation"
                                    required
                                    class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-brand-teal focus:ring-4 focus:ring-brand-teal/10 transition-all placeholder:text-gray-400 font-medium"
                                    placeholder="Konfirmasi password baru" />
                                <div
                                    class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-teal transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <button type="button" wire:click="togglePasswordConfirmationVisibility"
                                    class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-teal transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($showPasswordConfirmation)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        @endif
                                    </svg>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="text-xs text-red-500 font-bold ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Requirements -->
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <p class="text-xs font-bold text-blue-900 mb-2">Persyaratan Password:</p>
                            <ul class="text-xs text-blue-800 space-y-1">
                                <li>✓ Minimal 8 karakter</li>
                                <li>✓ Gunakan kombinasi huruf, angka, dan simbol</li>
                                <li>✓ Jangan gunakan tanggal lahir atau nama</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full h-14 bg-gradient-to-r from-brand-teal to-brand-dark text-white font-black rounded-2xl shadow-lg hover:shadow-brand-teal/30 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-wait">
                            <span wire:loading.remove>Ganti Password</span>
                            <span wire:loading>Memproses...</span>
                        </button>

                        <!-- Logout Button -->
                        <button type="button" wire:click="logout"
                            class="w-full h-12 bg-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-300 transition-all active:scale-95">
                            Logout
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
