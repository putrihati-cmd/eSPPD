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

        $employee = Employee::where('nip', $this->nip)->first();

        if (!$employee) {
            if (Auth::attempt(['email' => $this->nip, 'password' => $this->password], $this->remember)) {
                return $this->handleSuccess();
            }
            throw ValidationException::withMessages([
                'nip' => 'Username atau NIP tidak dikenali.',
            ]);
        }

        $user = $employee->user;
        if (!$user) {
            throw ValidationException::withMessages([
                'nip' => 'Data ditemukan, namun akun belum aktif.',
            ]);
        }

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
        session()->flash('error', 'Sistem sedang sibuk: ' . $e->getMessage());
    }
};

$handleSuccess = function () {
    Session::regenerate();
    $user = Auth::user();

    if ($user && isset($user->is_password_reset) && !$user->is_password_reset) {
        return $this->redirect(route('password.force-change'), navigate: true);
    }

    return $this->redirectIntended(route('dashboard'), navigate: true);
};

$togglePassword = function () {
    $this->showPassword = !$this->showPassword;
};

?>

<div class="selection:bg-[#D4E157] selection:text-black font-sans min-h-screen relative overflow-hidden bg-[#0a0f0d]">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;700;900&family=Playfair+Display:ital,wght@0,900;1,900&display=swap');

        :root {
            --brand-teal: #0097a7;
            --brand-lime: #D4E157;
            --dark-bg: #0a0f0d;
        }

        body {
            background-color: var(--dark-bg);
            margin: 0;
            color: white;
            font-family: 'Outfit', sans-serif;
        }

        .lux-bg {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 0% 0%, rgba(0, 151, 167, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(212, 225, 87, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 50% 50%, rgba(0, 0, 0, 0.2) 0%, transparent 100%);
            z-index: 0;
        }

        .aurora-1 {
            position: absolute;
            top: -10%;
            right: -10%;
            width: 60%;
            height: 60%;
            background: var(--brand-teal);
            filter: blur(150px);
            opacity: 0.1;
            border-radius: 100%;
            animation: drift 20s infinite alternate;
        }

        .aurora-2 {
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 50%;
            height: 50%;
            background: var(--brand-lime);
            filter: blur(150px);
            opacity: 0.08;
            border-radius: 100%;
            animation: drift 15s infinite alternate-reverse;
        }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(10%, 10%) scale(1.1); }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 48px;
            box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.8);
        }

        .lux-input {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 20px !important;
            padding: 1.25rem 1.5rem 1.25rem 3.5rem !important;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .lux-input:focus {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: var(--brand-teal) !important;
            box-shadow: 0 0 0 4px rgba(0, 151, 167, 0.15) !important;
        }

        .lux-btn {
            background: linear-gradient(135deg, var(--brand-teal) 0%, #007c8a 100%);
            color: white;
            font-weight: 800;
            border-radius: 24px;
            padding: 1.25rem;
            box-shadow: 0 10px 30px -10px rgba(0, 151, 167, 0.5);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .lux-btn:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 40px -10px rgba(0, 151, 167, 0.6);
            filter: brightness(1.1);
        }

        .lux-btn:active {
            transform: translateY(0) scale(0.98);
        }

        .serif-title {
            font-family: 'Playfair Display', serif;
            line-height: 0.95;
            letter-spacing: -2px;
        }

        .outline-text {
            -webkit-text-stroke: 1px rgba(255,255,255,0.2);
            color: transparent;
        }

        .pattern-overlay {
            position: absolute;
            inset: 0;
            background-image: url('{{ asset('images/pattern.png') }}');
            background-size: 800px;
            opacity: 0.04;
            mix-blend-mode: overlay;
            z-index: 1;
        }

        @keyframes reveal {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-reveal {
            animation: reveal 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <!-- Background Layers -->
    <div class="lux-bg"></div>
    <div class="aurora-1"></div>
    <div class="aurora-2"></div>
    <div class="pattern-overlay"></div>

    <div class="relative z-10 min-h-screen flex flex-col lg:flex-row">
        
        <!-- Left Hero Content -->
        <div class="hidden lg:flex flex-[1.4] flex-col justify-center px-24 xl:px-40">
            <div class="animate-reveal">
                <div class="flex items-center gap-3 mb-10">
                    <div class="h-px w-12 bg-white/20"></div>
                    <span class="text-[10px] font-black tracking-[0.5em] text-white/40 uppercase">E-SPPD Institutional Excellence</span>
                </div>

                <h1 class="serif-title text-8xl mb-8">
                    <span class="block">Sistem</span>
                    <span class="block text-white/30 italic">Informasi</span>
                    <span class="block" style="color: var(--brand-lime);">Perjalanan Dinas</span>
                </h1>

                <p class="text-xl text-white/50 font-light leading-relaxed mb-16 max-w-lg">
                    Platform eksklusif pengajuan akomodasi dan perjalanan dinas di lingkungan <span class="text-white font-medium">UIN SAIZU Purwokerto</span>. Dirancang untuk efisiensi tanpa batas.
                </p>

                <div class="flex items-center gap-8">
                    <div class="flex flex-col">
                        <span class="text-4xl font-bold mb-1" style="color: var(--brand-lime);">500+</span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-white/30">Total Perjalanan</span>
                    </div>
                    <div class="h-10 w-px bg-white/10"></div>
                    <div class="flex flex-col">
                        <span class="text-4xl font-bold mb-1" style="color: var(--brand-lime);">99%</span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-white/30">Layanan Digital</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Login Card -->
        <div class="flex-1 flex flex-col items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-lg animate-reveal" style="animation-delay: 0.2s">
                <div class="glass-card p-12 lg:p-16 relative overflow-hidden">
                    <!-- Subtle Internal Glow -->
                    <div class="absolute -top-20 -left-20 w-40 h-40 bg-white/5 blur-3xl rounded-full"></div>

                    <div class="text-center mb-16">
                        <div class="inline-block relative mb-10">
                            <div class="absolute inset-0 bg-white/10 blur-2xl rounded-full"></div>
                            <img src="{{ asset('images/logo.png') }}" alt="UIN SAIZU" class="h-28 relative grayscale brightness-200 contrast-125 opacity-90">
                        </div>
                        <h2 class="text-3xl font-bold mb-2 tracking-tight">Portal Akses <span style="color: var(--brand-teal);">Resmi</span></h2>
                        <p class="text-white/30 font-black text-[9px] uppercase tracking-[0.3em]">Autentikasi Terenkripsi Sempurna</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-10 p-5 bg-red-500/10 border border-red-500/20 rounded-3xl text-red-400 text-sm font-medium text-center">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form wire:submit="login" class="space-y-8">
                        <!-- NIP -->
                        <div class="space-y-3">
                            <label class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] ml-2">Identitas Pegawai / NIP</label>
                            <div class="relative group">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-[var(--brand-teal)] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </span>
                                <input wire:model="nip" type="text" required placeholder="NIP 18 Digit"
                                    class="w-full lux-input outline-none font-medium placeholder:text-white/10 text-lg">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-3">
                            <label class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] ml-2">Kunci Pengamanan</label>
                            <div class="relative group">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-[var(--brand-teal)] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </span>
                                <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" required placeholder="••••••••"
                                    class="w-full lux-input outline-none font-medium placeholder:text-white/10 text-lg">
                                <button type="button" wire:click="togglePassword" class="absolute right-6 top-1/2 -translate-y-1/2 text-white/10 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($showPassword) <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        @else <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /> @endif
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-2">
                            <label class="flex items-center cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" wire:model="remember" class="peer h-5 w-5 bg-white/5 border border-white/10 rounded-md checked:bg-[var(--brand-teal)] checked:border-transparent transition-all">
                                </div>
                                <span class="ml-3 text-[10px] font-bold text-white/30 uppercase tracking-widest group-hover:text-white transition-colors">Tetap Login</span>
                            </label>
                            <a href="#" class="text-[10px] font-black text-white/20 uppercase tracking-widest hover:text-[var(--brand-lime)] transition-colors">Lupa Akses?</a>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" class="w-full lux-btn flex items-center justify-center gap-3">
                            <span wire:loading.remove>Masuk Sekarang</span>
                            <span wire:loading class="animate-spin h-5 w-5 border-2 border-white/50 border-t-white rounded-full"></span>
                            <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </button>
                    </form>

                    <div class="mt-16 flex flex-col items-center gap-5 pt-12 border-t border-white/5">
                        <span class="text-[8px] font-black text-white/20 uppercase tracking-[0.4em]">Official Collaboration</span>
                        <div class="flex items-center gap-6 opacity-40">
                            <span class="text-xs font-black tracking-tighter">UIN SAIZU</span>
                            <div class="h-1 w-1 bg-white/20 rounded-full"></div>
                            <span class="text-xs font-black tracking-tighter">PUSKOM</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Help -->
                <div class="mt-12 text-center">
                    <p class="text-[10px] font-medium text-white/20 uppercase tracking-[0.2em]">
                        Butuh Bantuan? <a href="#" class="text-white hover:text-[var(--brand-lime)] transition-colors underline underline-offset-8 decoration-white/10">Hubungi Konsol IT</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
