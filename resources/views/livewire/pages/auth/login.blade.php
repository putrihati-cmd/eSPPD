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

    /**
     * Updated hook for real-time validation
     */
    public function updated($propertyName): void
    {
        if ($propertyName === 'nip') {
            $this->validateOnly($propertyName, [
                'nip' => 'required|numeric|digits:18',
            ]);
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->isLoading = true;

        try {
            $this->validate([
                'nip' => 'required|numeric|digits:18',
                'password' => 'required|string|min:8',
            ]);

            // Step 1: Find Employee by NIP
            $employee = Employee::where('nip', $this->nip)->first();

            if (!$employee) {
                throw ValidationException::withMessages([
                    'nip' => 'NIP tidak ditemukan dalam sistem.',
                ]);
            }

            // Step 2: Get User from Employee relation
            $user = $employee->user;

            if (!$user) {
                throw ValidationException::withMessages([
                    'nip' => 'Akun pengguna belum terdaftar untuk NIP ini.',
                ]);
            }

            // Step 3: Authenticate using User's email
            if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
                throw ValidationException::withMessages([
                    'password' => 'Password salah.',
                ]);
            }

            Session::regenerate();

            // Step 4: Check if user must change password on first login
            $user = Auth::user();
            if ($user && isset($user->is_password_reset) && !$user->is_password_reset) {
                $this->redirect(route('auth.force-change-password', absolute: false), navigate: true);
                return;
            }

            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } catch (ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Toggle password visibility
     */
    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }
}; ?>

<div class="flex-1 flex items-center justify-center p-6 lg:p-12 z-20 w-full min-h-screen bg-brand-teal">
    <div class="w-full max-w-md">
        <div class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl p-10 lg:p-14 relative overflow-hidden border border-white/20">

            <!-- Internal glows -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-teal-500/10 blur-[80px] rounded-full"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-lime-500/10 blur-[80px] rounded-full"></div>

            <!-- Header -->
            <div class="relative text-center mb-12">
                <div class="mb-8 flex justify-center">
                    <div class="relative inline-block">
                        <div class="absolute inset-x-0 bottom-0 h-4 bg-teal-500/20 blur-xl scale-125"></div>
                        <img src="{{ asset('images/logo.png') }}" alt="Logo UIN SAIZU" class="relative w-auto h-24">
                    </div>
                </div>
                <div class="space-y-3">
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Login <span class="text-teal-600">e-SPPD</span></h2>
                    <p class="text-gray-500 font-medium text-sm">Sistem Informasi Perjalanan Dinas</p>
                </div>
            </div>

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg flex items-center gap-3 animate-shake">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-red-700 text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <form wire:submit="login" class="space-y-6 relative">
                <!-- NIP -->
                <div class="space-y-2">
                    <label for="nip" class="text-sm font-bold text-gray-700 ml-1">NIP</label>
                    <div class="relative group">
                        <input wire:model.live="nip" id="nip" type="text" required autofocus
                               inputmode="numeric" maxlength="18"
                               class="w-full h-14 pl-14 pr-12 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all placeholder:text-gray-400 font-medium @error('nip') border-red-500 ring-4 ring-red-500/10 @enderror"
                               placeholder="Masukkan NIP 18 digit"
                        />
                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        @error('nip')
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        @else
                            @if(strlen($nip) === 18 && is_numeric($nip))
                                <div class="absolute right-5 top-1/2 -translate-y-1/2 text-emerald-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @endif
                        @enderror
                    </div>
                    @error('nip')
                        <p class="text-xs text-red-500 font-bold ml-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 ml-1 font-medium">NIP 18 digit (contoh: 1983...)</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label for="password" class="text-sm font-bold text-gray-700 ml-1">Password</label>
                    <div class="relative group">
                        <input wire:model="password" id="password" type="{{ $showPassword ? 'text' : 'password' }}" required
                               class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all placeholder:text-gray-400 font-medium @error('password') border-red-500 ring-4 ring-red-500/10 @enderror"
                               placeholder="Masukkan password"
                        />
                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <button type="button" wire:click="togglePasswordVisibility" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-teal-600 transition-colors focus:outline-none">
                            @if($showPassword)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            @endif
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-500 font-bold ml-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" wire:model="remember" class="w-5 h-5 rounded-lg border-gray-200 text-teal-600 focus:ring-teal-500/20">
                        <span class="ml-3 text-sm font-bold text-gray-600 group-hover:text-gray-900 transition-colors">Ingat saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm font-bold text-teal-600 hover:text-teal-700 hover:underline decoration-2 underline-offset-4 px-1 transition-all">Lupa password?</a>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full h-14 bg-gradient-to-r from-teal-600 to-teal-800 text-white font-black rounded-2xl shadow-lg hover:shadow-teal-600/30 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-wait flex items-center justify-center gap-2">
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

            <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col items-center gap-3">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest text-center">UIN SAIZU Purwokerto © {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</div>
