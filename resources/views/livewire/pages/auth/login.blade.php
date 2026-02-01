

@extends('layouts.login')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <form wire:submit.prevent="login" class="space-y-6">
            <!-- NIP -->
            <div class="space-y-2">
                <label for="nip" class="text-sm font-bold text-gray-700 ml-1">NIP</label>
                <div class="relative group">
                    <input
                        wire:model.debounce.400ms="nip"
                        id="nip"
                        type="text"
                        name="nip"
                        required
                        class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-brand-teal focus:ring-4 focus:ring-brand-teal/10 transition-all placeholder:text-gray-400 font-medium {{ $errors->has('nip') ? 'border-red-500 ring-2 ring-red-200' : '' }}"
                        placeholder="Masukkan NIP"
                        aria-invalid="{{ $errors->has('nip') ? 'true' : 'false' }}"
                        aria-describedby="nip-error nip-help"
                        autocomplete="username"
                    />
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-teal transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    @error('nip')
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 text-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    @enderror
                </div>
                <p id="nip-help" class="text-xs text-gray-400 ml-1">NIP hanya angka, tanpa spasi atau tanda baca.</p>
                @error('nip')
                    <p id="nip-error" class="text-xs text-red-500 font-bold ml-1 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="text-sm font-bold text-gray-700 ml-1">Password</label>
                <div class="relative group">
                    <input
                        wire:model.debounce.400ms="password"
                        id="password"
                        type="password"
                        name="password"
                        required
                        minlength="8"
                        class="w-full h-14 pl-14 pr-14 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-brand-teal focus:ring-4 focus:ring-brand-teal/10 transition-all placeholder:text-gray-400 font-medium {{ $errors->has('password') ? 'border-red-500 ring-2 ring-red-200' : '' }}"
                        placeholder="Masukkan password minimal 8 karakter"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        aria-describedby="password-error password-help"
                        autocomplete="current-password"
                        onpaste="return false;"
                    />
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-teal transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <button
                        type="button"
                        onclick="togglePasswordVisibility()"
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-teal transition-colors focus:outline-none"
                        aria-label="Tampilkan/Sembunyikan password"
                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"
                        tabindex="0"
                    >
                        <svg class="w-6 h-6 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <!-- Mata terbuka (default) -->
                            <path id="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path id="eye-open2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <!-- Mata tertutup (hidden by default, toggle via JS) -->
                            <path id="eye-closed" style="display:none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                    @error('password')
                        <div class="absolute right-14 top-1/2 -translate-y-1/2 text-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    @enderror
                </div>
                <p id="password-help" class="text-xs text-gray-400 ml-1">Password minimal 8 karakter, kombinasi huruf & angka disarankan.</p>
                @error('password')
                    <p id="password-error" class="text-xs text-red-500 font-bold ml-1 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-between pt-1 gap-2 sm:gap-0">
                <label class="flex items-center cursor-pointer group">
                    <input type="checkbox" wire:model="remember"
                        class="w-5 h-5 rounded-lg border-gray-200 text-brand-teal focus:ring-brand-teal/20">
                    <span class="ml-3 text-sm font-bold text-gray-600 group-hover:text-gray-900 transition-colors">Ingat saya</span>
                </label>
                <div class="flex flex-col items-end gap-1">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm font-bold text-brand-teal hover:underline decoration-2 underline-offset-4">Lupa password?</a>
                    @endif
                    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20IT%2C%20saya%20butuh%20bantuan%20login%20eSPPD" target="_blank" rel="noopener" class="text-xs text-gray-400 hover:text-brand-lime font-medium underline underline-offset-2">Butuh bantuan login?</a>
                </div>
            </div>
            <button type="submit" wire:loading.attr="disabled"
                class="w-full h-14 bg-gradient-to-r from-brand-teal to-brand-dark text-white font-black rounded-2xl shadow-lg hover:shadow-brand-teal/30 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-wait flex items-center justify-center gap-2">
                <span wire:loading.remove>Masuk ke Dashboard</span>
                <span wire:loading>
                    <svg class="w-5 h-5 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>
        </form>
        <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col items-center gap-3">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Terintegrasi dengan</p>
            <div class="flex items-center gap-4 opacity-50 font-black text-sm text-gray-900">
                <span>UIN SAIZU</span>
                <div class="w-1 h-1 bg-gray-300 rounded-full"></div>
                <span>PUSKOM</span>
            </div>
        </div>
        <div class="text-center mt-10">
            <p class="text-brand-dark text-sm font-medium">Bantuan? <a href="mailto:support@uinsaizu.ac.id?subject=Bantuan%20Login%20eSPPD" class="text-brand-lime font-bold hover:underline">Hubungi Admin IT</a></p>
        </div>
        <div class="mt-6 text-center text-xs text-gray-500">
            <p>Belum punya akun? Hubungi Admin TU</p>
            <p class="mt-1">UIN Saizu Purwokerto © {{ date('Y') }}</p>
        </div>
    </div>
    </div>
</div>
@endsection
