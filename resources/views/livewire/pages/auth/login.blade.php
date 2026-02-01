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

            // Attempt NIP / Email login
            $employee = Employee::where('nip', $this->nip)->first();

            if (!$employee) {
                // Try direct email login for compatibility (handling @ domain)
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
        
        // FIX: Route name corrected from 'auth.force-change-password' to 'password.force-change'
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
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
    </style>

    <div class="fixed inset-0 z-0 h-full w-full">
        <div class="absolute inset-0 bg-[#008080]"></div>
        <div id="bg-pattern" class="absolute inset-0"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col lg:flex-row">
        <!-- Hero Section (Left) -->
        <div class="hidden lg:flex flex-[1.3] flex-col justify-center px-16 xl:px-32 text-white">
            <div class="max-w-xl animate-up">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-white/10 border border-white/20 text-xs font-bold mb-8 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#D4E157] mr-2 animate-pulse"></span>
                    UIN SAIZU Purwokerto
                </div>

                <h1 class="text-6xl font-black leading-[1.1] tracking-tight mb-8">
                    Sistem Informasi <br />
                    <span class="text-[#D4E157] drop-shadow-sm">Perjalanan Dinas</span>
                </h1>

                <div class="h-1.5 w-24 bg-[#D4E157] mb-10 rounded-full"></div>

                <p class="text-xl text-white/90 font-medium leading-relaxed mb-12">
                    Transformasi digital pengajuan dan pelaporan perjalanan dinas di lingkungan UIN SAIZU yang lebih
                    efisien, transparan, dan akuntabel.
                </p>

                <div class="flex items-center gap-6 mb-20">
                    <a href="/guide"
                        class="h-14 px-10 rounded-2xl bg-[#D4E157] text-black font-bold flex items-center shadow-lg hover:opacity-90 transition-all hover:-translate-y-1 active:scale-95">
                        Pelajari Lebih Lanjut
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="/guide"
                        class="h-14 px-10 rounded-2xl border-2 border-white/30 text-white font-bold flex items-center hover:bg-white/10 transition-all active:scale-95">
                        Panduan Pengguna
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-8 border-t border-white/10 pt-10">
                    <div>
                        <p class="text-4xl font-black text-[#D4E157] mb-1">500+</p>
                        <p class="text-xs text-white/60 uppercase font-bold tracking-widest leading-tight">Perjalanan<br />Dinas</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-[#D4E157] mb-1">50+</p>
                        <p class="text-xs text-white/60 uppercase font-bold tracking-widest leading-tight">Dosen &<br />Staff</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-[#D4E157] mb-1">99%</p>
                        <p class="text-xs text-white/60 uppercase font-bold tracking-widest leading-tight">Tingkat<br />Kepuasan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Card Section (Right) -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md animate-up" style="animation-delay: 0.2s">
                <div class="login-card p-10 lg:p-14 relative overflow-hidden">
                    <!-- Header -->
                    <div class="relative text-center mb-10">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo UIN SAIZU" class="h-24 mx-auto mb-8">
                        <div class="space-y-2">
                            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Login <span class="text-[#0097a7]">e-SPPD</span></h2>
                            <p class="text-gray-500 font-semibold text-sm">Sistem Informasi Perjalanan Dinas</p>
                        </div>
                    </div>

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl flex items-center gap-3 animate-shake">
                            <span class="text-red-700 text-sm font-bold">{{ session('error') }}</span>
                        </div>
                    @endif

                    <form wire:submit="login" class="space-y-6">
                        <!-- NIP -->
                        <div class="space-y-2">
                            <label for="nip" class="text-sm font-bold text-gray-700 ml-1">Username / NIP</label>
                            <div class="relative group">
                                <input wire:model="nip" id="nip" type="text" required autofocus
                                       class="w-full h-14 pl-14 pr-12 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all placeholder:text-gray-400 font-medium @error('nip') border-red-300 @enderror"
                                       placeholder="Masukkan NIP"
                                />
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#0097a7] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                @error('nip')
                                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-red-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-bold text-gray-700 ml-1">Password</label>
                            <div class="relative group">
                                <input wire:model="password" id="password" type="{{ $showPassword ? 'text' : 'password' }}" required
                                       class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-transparent rounded-2xl focus:bg-white focus:border-[#0097a7] focus:ring-4 focus:ring-[#0097a7]/10 transition-all placeholder:text-gray-400 font-medium"
                                       placeholder="Masukkan password"
                                />
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#0097a7] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <button type="button" wire:click="togglePasswordVisibility" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0097a7]">
                                    @if($showPassword)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" /></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                    @endif
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" wire:model="remember" class="w-5 h-5 rounded-lg border-gray-200 text-[#0097a7] focus:ring-[#0097a7]/20">
                                <span class="ml-2.5 text-sm font-bold text-gray-500 group-hover:text-gray-900 transition-colors">Ingat saya</span>
                            </label>
                            <a href="#" class="text-sm font-black text-[#0097a7] hover:underline transition-all">Lupa password?</a>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full h-14 btn-teal text-white font-black rounded-2xl shadow-lg hover:shadow-[#0097a7]/30 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-wait flex items-center justify-center gap-2">
                            <span wire:loading.remove>Masuk ke Dashboard</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </form>

                    <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col items-center gap-4">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest text-center">Terintegrasi Dengan</p>
                        <div class="flex items-center gap-4 text-gray-600 font-black text-sm uppercase">
                            <span>UIN SAIZU</span>
                            <span class="text-[#D4E157]">•</span>
                            <span>PUSKOM</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">UIN SAIZU Purwokerto © {{ date('Y') }}</p>
                    </div>
                </div>

                <!-- Footer Help -->
                <div class="text-center">
                    <p class="text-white/70 text-sm font-bold">
                        Bantuan? <a href="#" class="text-[#D4E157] hover:underline decoration-2">Hubungi Admin IT</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
