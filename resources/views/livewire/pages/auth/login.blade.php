<?php

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.login')] class extends Component
{
    public string $nip = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showPassword = false;
    public bool $isLoading = false;

    public function updated($propertyName): void
    {
        if ($propertyName === 'nip') {
            $this->validateOnly($propertyName, [
                'nip' => 'required',
            ]);
        }
    }

    public function login(): void
    {
        $this->isLoading = true;

        try {
            $this->validate([
                'nip' => 'required',
                'password' => 'required|string|min:8',
            ]);

            // Attempt NIP login
            $employee = Employee::where('nip', $this->nip)->first();

            if (!$employee) {
                // Try direct email login for compatibility
                if (Auth::attempt(['email' => $this->nip, 'password' => $this->password], $this->remember)) {
                    $this->finalizeLogin();
                    return;
                }
                
                throw ValidationException::withMessages([
                    'nip' => 'NIP/Email tidak ditemukan.',
                ]);
            }

            $user = $employee->user;
            if (!$user) {
                throw ValidationException::withMessages([
                    'nip' => 'Akun belum terdaftar.',
                ]);
            }

            if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
                throw ValidationException::withMessages([
                    'password' => 'Password salah.',
                ]);
            }

            $this->finalizeLogin();
        } catch (ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    private function finalizeLogin(): void
    {
        Session::regenerate();
        $user = Auth::user();
        if ($user && isset($user->is_password_reset) && !$user->is_password_reset) {
            $this->redirect(route('auth.force-change-password', absolute: false), navigate: true);
            return;
        }
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }
}; ?>

<div class="selection:bg-brand-lime selection:text-black font-sans min-h-screen bg-[#008080]">
    <style>
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .btn-teal {
            background-color: #0097a7;
            transition: all 0.3s ease;
        }
        .btn-teal:hover {
            background-color: #00838f;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 151, 167, 0.4);
        }
        .input-active {
            border-color: #0097a7 !important;
            box-shadow: 0 0 0 4px rgba(0, 151, 167, 0.1) !important;
        }
        #bg-pattern {
            background-image: url('{{ asset('images/pattern.png') }}');
            background-size: 800px;
            background-repeat: repeat;
            opacity: 0.15;
            mix-blend-mode: overlay;
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: fade-in-up 0.8s ease-out forwards; }
    </style>

    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-[#008080]"></div>
        <div id="bg-pattern" class="absolute inset-0"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col lg:flex-row">
        <!-- Hero Section -->
        <div class="hidden lg:flex flex-[1.3] flex-col justify-center px-20 xl:px-32 text-white">
            <div class="max-w-xl animate-up">
                <div class="inline-flex items-center px-4 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-bold mb-8 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-lime mr-2"></span>
                    UIN SAIZU Purwokerto
                </div>

                <h1 class="text-6xl font-extrabold leading-tight mb-6">
                    Sistem Informasi <br />
                    <span class="text-[#D4E157]">Perjalanan Dinas</span>
                </h1>

                <div class="h-1.5 w-20 bg-[#D4E157] mb-10 rounded-full"></div>

                <p class="text-lg text-white/90 font-medium leading-relaxed mb-12">
                    Transformasi digital pengajuan dan pelaporan perjalanan dinas di lingkungan UIN SAIZU yang lebih
                    efisien, transparan, dan akuntabel.
                </p>

                <div class="flex items-center gap-4 mb-20">
                    <a href="#" class="h-14 px-8 rounded-2xl bg-[#D4E157] text-black font-bold flex items-center gap-2 hover:opacity-90 transition-all">
                        Pelajari Lebih Lanjut
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="#" class="h-14 px-8 rounded-2xl border-2 border-white/30 text-white font-bold flex items-center hover:bg-white/10 transition-all">
                        Panduan Pengguna
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-12 pt-10 border-t border-white/10">
                    <div>
                        <p class="text-4xl font-black text-[#D4E157] mb-1">500+</p>
                        <p class="text-xs text-white/60 uppercase font-bold tracking-widest">Perjalanan<br>Dinas</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-[#D4E157] mb-1">50+</p>
                        <p class="text-xs text-white/60 uppercase font-bold tracking-widest">Dosen &<br>Staff</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-[#D4E157] mb-1">99%</p>
                        <p class="text-xs text-white/60 uppercase font-bold tracking-widest">Tingkat<br>Kepuasan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Card Section -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md animate-up" style="animation-delay: 0.2s">
                <div class="login-card p-10 lg:p-14 mb-8">
                    <!-- Logo & Title -->
                    <div class="text-center mb-10">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-24 mx-auto mb-6">
                        <h2 class="text-3xl font-black text-gray-900 mb-1">Login <span class="text-[#0097a7]">e-SPPD</span></h2>
                        <p class="text-gray-500 font-semibold text-sm">Sistem Informasi Perjalanan Dinas</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-red-700 text-sm font-bold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form wire:submit="login" class="space-y-6">
                        <!-- Username / NIP -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Username / NIP</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#0097a7]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                <input wire:model.live="nip" type="text" required placeholder="Masukkan NIP"
                                    class="w-full h-14 pl-14 pr-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all font-medium text-gray-900 @error('nip') border-red-300 @enderror">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700 ml-1">Password</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#0097a7]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" required placeholder="Masukkan password"
                                    class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all font-medium text-gray-900">
                                <button type="button" wire:click="togglePasswordVisibility" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0097a7]">
                                    @if($showPassword)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                    @endif
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" wire:model="remember" class="w-5 h-5 rounded-lg border-gray-200 text-[#0097a7] focus:ring-[#0097a7]/20">
                                <span class="ml-2.5 text-sm font-bold text-gray-500 group-hover:text-gray-900 transition-colors">Ingat saya</span>
                            </label>
                            <a href="#" class="text-sm font-black text-[#0097a7] hover:underline">Lupa password?</a>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full h-15 btn-teal text-white font-black rounded-2xl py-4 shadow-lg flex items-center justify-center gap-2">
                            <span wire:loading.remove>Masuk ke Dashboard</span>
                            <span wire:loading class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                        </button>
                    </form>

                    <div class="mt-12 flex flex-col items-center gap-4 border-t border-gray-100 pt-8">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Terintegrasi Dengan</p>
                        <div class="flex items-center gap-3 text-gray-600 font-black text-sm">
                            <span>UIN SAIZU</span>
                            <span class="text-brand-lime">•</span>
                            <span>PUSKOM</span>
                        </div>
                        <p class="text-[10px] text-gray-400">UIN SAIZU Purwokerto © {{ date('Y') }}</p>
                    </div>
                </div>

                <!-- Assistance Footer -->
                <div class="text-center">
                    <p class="text-white/70 text-sm font-bold">
                        Bantuan? <a href="#" class="text-[#D4E157] hover:underline">Hubungi Admin IT</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
