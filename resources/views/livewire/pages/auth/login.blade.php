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

    public function login(): void
    {
        $this->isLoading = true;

        try {
            $this->validate([
                'nip' => 'required',
                'password' => 'required|string|min:8',
            ]);

            $employee = Employee::where('nip', $this->nip)->first();

            if (!$employee) {
                $email = strpos($this->nip, '@') !== false ? $this->nip : $this->nip . '@uinsaizu.ac.id';
                if (Auth::attempt(['email' => $email, 'password' => $this->password], $this->remember)) {
                    $this->finalizeLogin();
                    return;
                }
                
                throw ValidationException::withMessages([
                    'nip' => 'NIP atau Email tidak ditemukan.',
                ]);
            }

            $user = $employee->user;
            if (!$user) {
                throw ValidationException::withMessages([
                    'nip' => 'Akun pengguna belum terdaftar untuk NIP ini.',
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
            $this->redirect(route('password.force-change', absolute: false), navigate: true);
            return;
        }
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }
}; ?>

<div class="selection:bg-[#D4E157] selection:text-black font-sans min-h-screen relative" style="background-color: #008080 !important; color: white;">
    <style>
        .login-bg-solid {
            position: fixed;
            inset: 0;
            background-color: #008080 !important;
            z-index: 0;
        }
        .login-pattern-overlay {
            position: fixed;
            inset: 0;
            background-image: url('{{ asset('images/pattern.png') }}');
            background-size: 600px;
            background-repeat: repeat;
            opacity: 0.12;
            mix-blend-mode: overlay;
            z-index: 1;
        }
        .login-card-container {
            background: rgba(255, 255, 255, 0.98) !important;
            border-radius: 40px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            padding: 3.5rem !important;
            color: #1a202c !important;
        }
        .lime-text { color: #D4E157 !important; }
        .lime-bg { background-color: #D4E157 !important; color: #000 !important; }
        .teal-btn { background-color: #0097a7 !important; color: #fff !important; }
        
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fade-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    <!-- UI Background -->
    <div class="login-bg-solid"></div>
    <div class="login-pattern-overlay"></div>

    <div class="relative z-10 min-h-screen flex flex-col lg:flex-row">
        <!-- Hero Section (Kiri) -->
        <div class="hidden lg:flex flex-[1.3] flex-col justify-center px-24 xl:px-32">
            <div class="max-w-2xl animate-fade-up">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-white/10 border border-white/20 text-[10px] font-bold mb-8 uppercase tracking-[0.2em]">
                    <span class="w-1.5 h-1.5 rounded-full lime-bg mr-2"></span>
                    UIN SAIZU Purwokerto
                </div>

                <h1 class="text-6xl font-black leading-[1.05] tracking-tight mb-8">
                    Sistem Informasi <br />
                    <span class="lime-text">Perjalanan Dinas</span>
                </h1>

                <div class="h-1.5 w-24 lime-bg mb-12 rounded-full"></div>

                <p class="text-xl text-white/80 font-medium leading-relaxed mb-12 max-w-lg">
                    Transformasi digital pengajuan dan pelaporan perjalanan dinas di lingkungan UIN SAIZU yang lebih
                    efisien, transparan, dan akuntabel.
                </p>

                <div class="flex items-center gap-6 mb-20 font-black">
                    <a href="#" class="h-14 px-10 rounded-2xl lime-bg flex items-center justify-center gap-2 shadow-xl hover:brightness-105 transition-all">
                        Pelajari Lebih Lanjut
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="#" class="h-14 px-10 rounded-2xl border-2 border-white/40 text-white flex items-center justify-center hover:bg-white/10 transition-all">
                        Panduan Pengguna
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-12 pt-12 border-t border-white/20">
                    <div>
                        <p class="text-4xl font-black lime-text mb-2 tracking-tight">500+</p>
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest leading-tight">Perjalanan<br />Dinas</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black lime-text mb-2 tracking-tight">50+</p>
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest leading-tight">Dosen &<br />Staff</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black lime-text mb-2 tracking-tight">99%</p>
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest leading-tight">Tingkat<br />Kepuasan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Card Section (Kanan) -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md animate-fade-up" style="animation-delay: 0.2s">
                <div class="login-card-container relative overflow-hidden">
                    <!-- Header -->
                    <div class="text-center mb-10">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo UIN SAIZU" class="h-24 mx-auto mb-8">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Login <span style="color: #0097a7;">e-SPPD</span></h2>
                        <p class="text-gray-500 font-bold text-xs uppercase tracking-widest mt-1">Sistem Informasi Perjalanan Dinas</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-red-700 text-sm font-bold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form wire:submit="login" class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Username / NIP</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                <input wire:model="nip" type="text" required placeholder="Masukkan NIP"
                                    class="w-full h-14 pl-14 pr-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all font-bold text-gray-900">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Password</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" required placeholder="Masukkan password"
                                    class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all font-bold text-gray-900">
                                <button type="button" wire:click="togglePasswordVisibility" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0097a7]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($showPassword)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        @endif
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" wire:model="remember" class="w-5 h-5 rounded border-gray-200 text-[#0097a7] focus:ring-[#0097a7]/20">
                                <span class="ml-3 text-xs font-black text-gray-400 group-hover:text-gray-900 transition-colors">Ingat saya</span>
                            </label>
                            <a href="#" class="text-xs font-black text-[#0097a7] hover:underline">Lupa password?</a>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full h-15 teal-btn font-black rounded-2xl py-4 shadow-xl flex items-center justify-center gap-2 hover:-translate-y-1 transition-all">
                            <span wire:loading.remove>Masuk ke Dashboard</span>
                            <span wire:loading class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                        </button>
                    </form>

                    <div class="mt-12 flex flex-col items-center gap-4 border-t border-gray-100 pt-8">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Terintegrasi Dengan</p>
                        <div class="flex items-center gap-4 text-gray-600 font-black text-xs uppercase tracking-tighter">
                            <span>UIN SAIZU</span>
                            <span class="lime-text text-xl">•</span>
                            <span>PUSKOM</span>
                        </div>
                        <p class="text-[9px] text-gray-300 font-bold uppercase tracking-widest mt-2">UIN SAIZU Purwokerto © {{ date('Y') }}</p>
                    </div>
                </div>

                <!-- Global Footer -->
                <div class="text-center mt-10">
                    <p class="text-white/60 text-sm font-bold tracking-tight">
                        Bantuan? <a href="#" class="lime-text hover:underline decoration-2 underline-offset-4">Hubungi Admin IT</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
