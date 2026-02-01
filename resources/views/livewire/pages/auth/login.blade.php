<?php

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use function Livewire\Volt\{layout, state, rules};

layout('layouts.login');

state([
    'nip' => '',
    'password' => '',
    'remember' => false,
    'showPassword' => false,
    'isLoading' => false,
]);

$login = function () {
    $this->isLoading = true;

    try {
        $this->validate([
            'nip' => 'required',
            'password' => 'required|string|min:8',
        ]);

        // 1. Cari Employee berdasarkan NIP
        $employee = Employee::where('nip', $this->nip)->first();

        if (!$employee) {
            // Coba login sebagai email/NIP langsung (Admin/Backdoor)
            if (Auth::attempt(['email' => $this->nip, 'password' => $this->password], $this->remember)) {
                return $this->handleSuccess();
            }
            
            throw ValidationException::withMessages([
                'nip' => 'NIP atau Email tidak terdaftar di sistem.',
            ]);
        }

        // 2. Ambil User dari Employee
        $user = $employee->user;
        if (!$user) {
            throw ValidationException::withMessages([
                'nip' => 'Data pegawai ditemukan, tapi akun sistem belum dibuat.',
            ]);
        }

        // 3. Authenticate menggunakan Email User tersebut
        if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'password' => 'Password yang Anda masukkan salah.',
            ]);
        }

        return $this->handleSuccess();

    } catch (ValidationException $e) {
        $this->isLoading = false;
        throw $e;
    } catch (\Exception $e) {
        $this->isLoading = false;
        session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }
};

$handleSuccess = function () {
    Session::regenerate();
    $user = Auth::user();

    // Cek paksa ganti password (jika is_password_reset = false)
    if ($user && isset($user->is_password_reset) && !$user->is_password_reset) {
        return $this->redirect(route('password.force-change'), navigate: true);
    }

    return $this->redirectIntended(route('dashboard'), navigate: true);
};

$togglePassword = function () {
    $this->showPassword = !$this->showPassword;
};

?>

<div class="selection:bg-[#D4E157] selection:text-black font-sans min-h-screen relative" style="background-color: #006d75 !important;">
    <style>
        .login-bg-master {
            position: fixed;
            inset: 0;
            background-color: #006d75 !important;
            z-index: 0;
        }
        .login-pattern-master {
            position: fixed;
            inset: 0;
            background-image: url('{{ asset('images/pattern.png') }}');
            background-size: 800px;
            background-repeat: repeat;
            opacity: 0.12;
            mix-blend-mode: overlay;
            z-index: 1;
        }
        .login-card-master {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            padding: 3.5rem;
            color: #111827;
        }
        .btn-lime-glow {
            background-color: #D4E157 !important;
            color: #000 !important;
            font-weight: 900;
            transition: all 0.3s ease;
        }
        .btn-lime-glow:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }
        .btn-teal-dark {
            background-color: #0097a7 !important;
            color: #fff !important;
            font-weight: 900;
            transition: all 0.3s ease;
        }
        .btn-teal-dark:hover {
            background-color: #00838f !important;
            transform: translateY(-2px);
        }
        @keyframes fade-up-smooth {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fade-up-smooth 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    <!-- UI Background -->
    <div class="login-bg-master"></div>
    <div class="login-pattern-master"></div>

    <div class="relative z-10 min-h-screen flex flex-col lg:flex-row">
        <!-- Hero Section -->
        <div class="hidden lg:flex flex-[1.3] flex-col justify-center px-16 xl:px-32 text-white">
            <div class="max-w-2xl animate-fade-up">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-white/10 border border-white/20 text-[10px] font-bold mb-8 uppercase tracking-[0.2em]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#D4E157] mr-2"></span>
                    UIN SAIZU Purwokerto
                </div>

                <h1 class="text-6xl font-black leading-[1.05] tracking-tight mb-8">
                    Sistem Informasi <br />
                    <span style="color: #D4E157;">Perjalanan Dinas</span>
                </h1>

                <div class="h-1.5 w-24 rounded-full mb-10" style="background-color: #D4E157;"></div>

                <p class="text-xl text-white/90 font-medium leading-relaxed mb-12 max-w-lg">
                    Transformasi digital pengajuan dan pelaporan perjalanan dinas di lingkungan UIN SAIZU yang lebih
                    efisien, transparan, dan akuntabel.
                </p>

                <div class="flex items-center gap-6 mb-20">
                    <a href="#" class="h-14 px-10 rounded-2xl btn-lime-glow flex items-center justify-center gap-2 shadow-xl">
                        Pelajari Lebih Lanjut
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="#" class="h-14 px-10 rounded-2xl border-2 border-white/30 text-white font-black flex items-center hover:bg-white/10 transition-all">
                        Panduan Pengguna
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-12 pt-12 border-t border-white/20">
                    <div>
                        <p class="text-4xl font-black mb-1" style="color: #D4E157;">500+</p>
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest">Perjalanan Dinas</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black mb-1" style="color: #D4E157;">50+</p>
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest">Dosen & Staff</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black mb-1" style="color: #D4E157;">99%</p>
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest">Kepuasan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Card -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md animate-fade-up" style="animation-delay: 0.2s">
                <div class="login-card-master relative overflow-hidden">
                    <div class="text-center mb-10">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-24 mx-auto mb-8">
                        <h2 class="text-3xl font-black tracking-tight">Login <span style="color: #0097a7;">e-SPPD</span></h2>
                        <p class="text-gray-500 font-bold text-[10px] uppercase tracking-widest mt-1">Sistem Informasi Perjalanan Dinas</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-red-700 text-xs font-bold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form wire:submit="login" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Username / NIP</label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </span>
                                <input wire:model="nip" type="text" required placeholder="Masukkan NIP"
                                    class="w-full h-14 pl-14 pr-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all font-bold text-gray-900 border-gray-100">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Password</label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </span>
                                <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" required placeholder="Masukkan password"
                                    class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all font-bold text-gray-900 border-gray-100">
                                <button type="button" wire:click="togglePassword" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0097a7]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($showPassword) <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268-2.943 9.542-7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /> @else <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /> @endif
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" wire:model="remember" class="w-5 h-5 rounded border-gray-200 text-[#0097a7] focus:ring-[#0097a7]/20">
                                <span class="ml-2.5 text-xs font-black text-gray-400 group-hover:text-gray-900 transition-colors">Ingat saya</span>
                            </label>
                            <a href="#" class="text-xs font-black tracking-tight" style="color: #0097a7;">Lupa password?</a>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full h-14 btn-teal-dark rounded-2xl shadow-xl flex items-center justify-center gap-2">
                            <span wire:loading.remove>Masuk ke Dashboard</span>
                            <span wire:loading class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                        </button>
                    </form>

                    <div class="mt-12 flex flex-col items-center gap-4 border-t border-gray-100 pt-8">
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.2em]">Terintegrasi Dengan</p>
                        <div class="flex items-center gap-4 text-gray-600 font-black text-xs uppercase">
                            <span>UIN SAIZU</span>
                            <span style="color: #D4E157;">•</span>
                            <span>PUSKOM</span>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <p class="text-white/60 text-sm font-bold">
                        Bantuan? <a href="#" class="hover:underline decoration-2 underline-offset-4" style="color: #D4E157;">Hubungi Admin IT</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
