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

            // Attempt login: First check if NIP belongs to an employee
            $employee = Employee::where('nip', $this->nip)->first();

            if (!$employee) {
                // Fallback: Check if it's a direct email login (for admin/superadmin)
                if (Auth::attempt(['email' => $this->nip, 'password' => $this->password], $this->remember)) {
                    $this->handleSuccess();
                    return;
                }
                throw ValidationException::withMessages(['nip' => 'Identitas tidak ditemukan.']);
            }

            $user = $employee->user;
            if (!$user) {
                throw ValidationException::withMessages(['nip' => 'Akun belum diaktifkan.']);
            }

            if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
                throw ValidationException::withMessages(['password' => 'Kata sandi tidak sesuai.']);
            }

            $this->handleSuccess();
        } catch (ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Integrasi sistem terhambat: ' . $e->getMessage());
        }
    }

    protected function handleSuccess(): void
    {
        Session::regenerate();
        $user = Auth::user();

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

<div class="selection:bg-[#D4E157] selection:text-black font-sans min-h-screen relative overflow-hidden bg-[#009ca6]">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        :root {
            --saizu-teal: #009ca6;
            --saizu-lime: #d4e157;
            --saizu-dark-teal: #007c85;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: var(--saizu-teal);
        }

        .pattern-bg {
            position: fixed;
            inset: 0;
            background-image: url('{{ asset('images/pattern.png') }}');
            background-size: 800px;
            background-repeat: repeat;
            opacity: 0.15;
            mix-blend-mode: overlay;
            z-index: 1;
        }

        .glow-overlay {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 80%);
            z-index: 2;
        }

        .top-glow {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #06b6d4, #3b82f6);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.8);
            z-index: 50;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 3rem;
            width: 100%;
            max-width: 480px;
        }

        .btn-lime {
            background-color: var(--saizu-lime);
            color: #1f2937;
            font-weight: 700;
            transition: all 0.2s;
        }
        .btn-lime:hover {
            filter: brightness(0.95);
            transform: translateY(-1px);
        }

        .btn-teal {
            background-color: var(--saizu-teal);
            color: #ffffff;
            font-weight: 700;
            transition: all 0.2s;
        }
        .btn-teal:hover {
            background-color: var(--saizu-dark-teal);
            transform: translateY(-1px);
        }

        .input-saizu {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            width: 100%;
            font-weight: 500;
            color: #111827;
            transition: all 0.2s;
        }
        .input-saizu:focus {
            outline: none;
            border-color: var(--saizu-teal);
            box-shadow: 0 0 0 3px rgba(0, 156, 166, 0.1);
            background-color: #ffffff;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>

    <!-- Background Layers -->
    <div class="pattern-bg"></div>
    <div class="glow-overlay"></div>
    <div class="top-glow"></div>

    <div class="relative z-10 min-h-screen flex flex-col lg:flex-row">
        <!-- Hero Section -->
        <div class="hidden lg:flex flex-[1.2] flex-col justify-center px-16 xl:px-32 text-white animate-fade">
            <div class="max-w-xl">
                <div class="inline-block px-4 py-1 rounded-full bg-white/10 border border-white/20 text-[11px] font-bold mb-10 text-white/80">
                    UIN SAIZU Purwokerto
                </div>

                <h1 class="text-[64px] font-black leading-[1.05] tracking-tight mb-6">
                    Sistem Informasi <br />
                    <span style="color: var(--saizu-lime);">Perjalanan Dinas</span>
                </h1>

                <p class="text-lg text-white/70 font-medium leading-relaxed mb-10">
                    Efisiensi Pengajuan dan Pelaporan Perjalanan Dinas UIN Saizu Purwokerto dengan sistem digital yang modern dan terintegrasi.
                </p>

                <div class="flex items-center gap-4 mb-20">
                    <a href="#" class="h-12 px-8 rounded-lg btn-lime flex items-center justify-center shadow-md">
                        Pelajari Lebih Lanjut
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="#" class="h-12 px-8 rounded-lg border-2 border-white/40 text-white font-bold flex items-center justify-center hover:bg-white/10 transition-all">
                        Panduan Pengguna
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-12">
                    <div>
                        <p class="text-4xl font-extrabold mb-1">500+</p>
                        <p class="text-[11px] text-white/40 uppercase font-black tracking-widest">Perjalanan Dinas</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold mb-1">50+</p>
                        <p class="text-[11px] text-white/40 uppercase font-black tracking-widest">Dosen & Staff</p>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                            <p class="text-4xl font-extrabold">99%</p>
                        </div>
                        <p class="text-[11px] text-white/40 uppercase font-black tracking-widest">Kepuasan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Card Section -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12 animate-fade" style="animation-delay: 0.1s">
            <div class="login-card">
                <div class="text-center mb-10">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-20 mx-auto mb-6">
                    <h2 class="text-2xl font-extrabold text-gray-900 mb-1">Login e-SPPD</h2>
                    <p class="text-gray-500 text-sm font-medium">Masuk ke sistem perjalanan dinas</p>
                </div>

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg text-red-600 text-sm font-bold text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit="login" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 ml-1">Username / NIP</label>
                        <input wire:model="nip" type="text" required placeholder="Masukkan username atau NIP" class="input-saizu">
                        @error('nip') <span class="text-xs text-red-500 font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 ml-1">Password</label>
                        <div class="relative">
                            <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" required placeholder="Masukkan password" class="input-saizu">
                        </div>
                        @error('password') <span class="text-xs text-red-500 font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between pb-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-gray-300 text-[var(--saizu-teal)] focus:ring-[var(--saizu-teal)]">
                            <span class="ml-2 text-xs font-semibold text-gray-500">Ingat saya</span>
                        </label>
                        <a href="#" class="text-xs font-bold text-[var(--saizu-teal)] hover:underline">Lupa password?</a>
                    </div>

                    <button type="submit" wire:loading.attr="disabled" class="w-full h-12 btn-teal rounded-lg shadow-sm flex items-center justify-center gap-2">
                        <span wire:loading.remove>Masuk</span>
                        <span wire:loading class="animate-spin h-5 w-5 border-2 border-white/50 border-t-white rounded-full"></span>
                    </button>
                </form>

                <div class="mt-8 text-center pt-8 border-t border-gray-100">
                    <p class="text-gray-500 text-sm font-medium">
                        Belum punya akun? <a href="#" class="text-[var(--saizu-teal)] font-bold hover:underline">Hubungi Admin</a>
                    </p>
                </div>
            </div>

            <!-- Mobile Attribution -->
            <div class="mt-10 lg:fixed lg:bottom-6 lg:right-6 lg:mt-0">
                <div class="bg-black/10 backdrop-blur-md px-4 py-2 rounded-full flex items-center gap-2 text-[10px] font-bold text-white/50">
                    <span>Made by</span>
                    <span class="text-white">Atoms</span>
                    <div class="w-1.5 h-1.5 rounded-full bg-white/20"></div>
                </div>
            </div>
        </div>
    </div>
</div>
