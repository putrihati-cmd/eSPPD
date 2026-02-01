<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

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

            // 1. Cari Employee berdasarkan NIP
            $employee = Employee::where('nip', $this->nip)->first();

            if (!$employee) {
                // Backdoor untuk admin (email)
                if (Auth::attempt(['email' => $this->nip, 'password' => $this->password], $this->remember)) {
                    $this->finishLogin();
                    return;
                }
                throw ValidationException::withMessages(['nip' => 'NIP tidak ditemukan dalam sistem.']);
            }

            // 2. Ambil User
            $user = $employee->user;
            if (!$user) {
                throw ValidationException::withMessages(['nip' => 'Akun pengguna belum diaktifkan.']);
            }

            // 3. Authenticate
            if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
                throw ValidationException::withMessages(['password' => 'Password yang Anda masukkan salah.']);
            }

            $this->finishLogin();
        } catch (ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Sistem sedang sibuk: ' . $e->getMessage());
        }
    }

    protected function finishLogin(): void
    {
        Session::regenerate();
        $user = Auth::user();

        // Cek paksa ganti password
        if ($user && isset($user->is_password_reset) && !$user->is_password_reset) {
            $this->redirect(route('password.force-change'), navigate: true);
            return;
        }

        $this->redirectIntended(route('dashboard'), navigate: true);
    }

    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }
}
?>

<div class="selection:bg-[#D4E157] selection:text-black font-sans min-h-screen relative overflow-hidden bg-[#0a0f0d]">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;700;900&family=Playfair+Display:ital,wght@0,900;1,900&display=swap');

        :root {
            --brand-teal: #0097a7;
            --brand-lime: #D4E157;
            --dark-bg: #0a0f0d;
        }

        body { background-color: var(--dark-bg); color: white; font-family: 'Outfit', sans-serif; margin: 0; }

        .lux-bg {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 0% 0%, rgba(0, 151, 167, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(212, 225, 87, 0.1) 0%, transparent 50%);
            z-index: 0;
        }

        .aurora {
            position: absolute;
            width: 60%; height: 60%;
            background: var(--brand-teal);
            filter: blur(150px);
            opacity: 0.1;
            border-radius: 100%;
            animation: drift 20s infinite alternate;
        }

        @keyframes drift {
            from { transform: translate(-10%, -10%) scale(1); }
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
            transition: all 0.4s ease !important;
        }

        .lux-input:focus {
            border-color: var(--brand-teal) !important;
            background: rgba(255, 255, 255, 0.05) !important;
            box-shadow: 0 0 0 4px rgba(0, 151, 167, 0.15) !important;
        }

        .lux-btn {
            background: linear-gradient(135deg, var(--brand-teal) 0%, #007c8a 100%);
            color: white; font-weight: 800; border-radius: 20px; padding: 1.25rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            text-transform: uppercase; letter-spacing: 1px;
        }

        .lux-btn:hover { transform: translateY(-3px); filter: brightness(1.1); box-shadow: 0 15px 30px rgba(0, 151, 167, 0.4); }

        .serif-title { font-family: 'Playfair Display', serif; line-height: 0.95; letter-spacing: -2px; }

        @keyframes reveal { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-reveal { animation: reveal 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    <div class="lux-bg"></div>
    <div class="aurora" style="top: -10%; right: -10%;"></div>
    <div class="aurora" style="bottom: -10%; left: -10%; background: var(--brand-lime); opacity: 0.05;"></div>

    <div class="relative z-10 min-h-screen flex flex-col lg:flex-row">
        <!-- Hero -->
        <div class="hidden lg:flex flex-[1.4] flex-col justify-center px-24 xl:px-40">
            <div class="animate-reveal">
                <div class="flex items-center gap-4 mb-10">
                    <div class="h-px w-10 bg-white/30"></div>
                    <span class="text-[10px] font-black tracking-[0.4em] text-white/40 uppercase">e-SPPD Institutional</span>
                </div>
                <h1 class="serif-title text-8xl mb-10 text-white">
                    <span class="block">Sistem</span>
                    <span class="block text-white/20 italic">Informasi</span>
                    <span class="block" style="color: var(--brand-lime);">Perjalanan Dinas</span>
                </h1>
                <div class="flex items-center gap-10">
                    <div class="flex flex-col">
                        <span class="text-4xl font-black" style="color: var(--brand-lime);">500+</span>
                        <span class="text-[10px] font-bold text-white/30 uppercase tracking-widest mt-1">Total Trip</span>
                    </div>
                    <div class="h-10 w-px bg-white/10"></div>
                    <div class="flex flex-col">
                        <span class="text-4xl font-black" style="color: var(--brand-lime);">99.9%</span>
                        <span class="text-[10px] font-bold text-white/30 uppercase tracking-widest mt-1">Uptime</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-lg animate-reveal" style="animation-delay: 0.1s">
                <div class="glass-card p-12 lg:p-16">
                    <div class="text-center mb-12">
                        <img src="{{ asset('images/logo.png') }}" class="h-24 mx-auto mb-8 grayscale brightness-200 contrast-125 opacity-80">
                        <h2 class="text-3xl font-bold tracking-tight mb-2">Portal <span style="color: var(--brand-teal);">Resmi</span></h2>
                        <p class="text-white/20 font-black text-[9px] uppercase tracking-[0.3em]">Autentikasi Pegawai UIN SAIZU</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-8 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-400 text-sm font-bold text-center">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form wire:submit="login" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-white/30 uppercase tracking-widest ml-2">NIP Pegawai</label>
                            <div class="relative group">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-[var(--brand-teal)]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </span>
                                <input wire:model="nip" type="text" required placeholder="Masukkan NIP" class="w-full lux-input outline-none font-bold text-lg">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-white/30 uppercase tracking-widest ml-2">Kata Sandi</label>
                            <div class="relative group">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-[var(--brand-teal)]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </span>
                                <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" required placeholder="••••••••" class="w-full lux-input outline-none font-bold text-lg">
                                <button type="button" wire:click="togglePassword" class="absolute right-6 top-1/2 -translate-y-1/2 text-white/10 hover:text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($showPassword) <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268 2.943-9.542-7z" />
                                        @else <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /> @endif
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" class="w-full lux-btn flex items-center justify-center gap-3">
                            <span wire:loading.remove>Masuk Sistem</span>
                            <span wire:loading class="animate-spin h-5 w-5 border-2 border-white/50 border-t-white rounded-full"></span>
                            <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </button>
                    </form>

                    <div class="mt-12 text-center">
                        <a href="#" class="text-[10px] font-black text-white/20 uppercase tracking-widest hover:text-[var(--brand-lime)]">Lupa Akses Pegawai?</a>
                    </div>
                </div>

                <div class="mt-10 text-center">
                    <p class="text-[10px] font-bold text-white/10 uppercase tracking-[0.3em]">
                        Admin Assistance: <a href="#" style="color: var(--brand-lime);">Konsol IT UIN SAIZU</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
