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
            'nip' => 'required|numeric|digits:18',
            'password' => 'required|string',
        ]);

        // Step 1: Find Employee by NIP
        $employee = \App\Models\Employee::where('nip', $this->nip)->first();

        if (!$employee) {
            $this->isLoading = false;
            throw ValidationException::withMessages([
                'nip' => 'NIP tidak ditemukan dalam sistem.',
            ]);
        }

        // Step 2: Get User from Employee relation
        $user = $employee->user;

        if (!$user) {
            $this->isLoading = false;
            throw ValidationException::withMessages([
                'nip' => 'Akun pengguna belum terdaftar untuk NIP ini.',
            ]);
        }

        // Step 3: Authenticate using User's email
        if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
            $this->isLoading = false;
            throw ValidationException::withMessages([
                'nip' => 'NIP atau password salah.',
            ]);
        }

        Session::regenerate();

        // Step 4: Check if user must change password on first login
        $user = Auth::user();
        if ($user && !$user->is_password_reset) {
            $this->redirect(route('auth.force-change-password', absolute: false), navigate: true);
            return;
        }

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

<div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center">
            <img class="mx-auto h-16 w-auto" src="{{ asset('images/logo-uin.png') }}" alt="Logo UIN">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">e-SPPD</h2>
            <p class="mt-2 text-sm text-gray-600">Sistem Perjalanan Dinas</p>
        </div>
        <form wire:submit="login" class="mt-8 space-y-6">
            @if ($errorMessage)
                <div class="rounded-md bg-red-50 p-4 border-l-4 border-red-500">
                    <span class="text-red-700 text-sm">{{ $errorMessage }}</span>
                </div>
            @endif
            <div>
                <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                <input wire:model.defer="nip" id="nip" name="nip" type="text" maxlength="18"
                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 focus:z-10 sm:text-sm"
                    placeholder="Masukkan NIP 18 digit" required>
                <p class="mt-1 text-xs text-gray-500">Format: 18 digit angka NIP</p>
                @error('nip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input wire:model.defer="password" id="password" name="password" type="password"
                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-teal-500 focus:border-teal-500 focus:z-10 sm:text-sm"
                    placeholder="Password" required>
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input wire:model="remember" id="remember_me" name="remember_me" type="checkbox"
                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">Ingat saya</label>
                </div>
            </div>
            <div>
                <button type="submit" wire:loading.attr="disabled"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    <span wire:loading.remove>Login</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </form>
        <div class="mt-6 text-center text-xs text-gray-500">
            <p>Belum punya akun? Hubungi Admin TU</p>
            <p class="mt-1">UIN Saizu Purwokerto © {{ date('Y') }}</p>
        </div>
    </div>
</div>
