<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

new #[Layout('layouts.guest')] class extends Component {
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', trans($status));
            $this->reset('email');
        } else {
            $this->addError('email', trans($status));
        }
    }
}; ?>

<div class="min-h-screen flex items-center justify-center p-4 -mt-20">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-2xl p-8 text-center">
            {{-- Icon --}}
            <div class="w-16 h-16 mx-auto mb-6 bg-primary-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-2">Lupa Password?</h1>
            <p class="text-gray-600 mb-6">Silakan hubungi admin untuk reset password</p>

            {{-- Contact Info --}}
            <div class="bg-gray-50 rounded-xl p-5 text-left mb-6 border-l-4 border-primary-500">
                <h2 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    📞 Kontak Admin
                </h2>

                <div class="space-y-2 text-sm">
                    <p class="font-medium text-gray-700">Bagian Kepegawaian</p>
                    <p class="flex items-center gap-2 text-gray-600">
                        <span>📱</span>
                        <a href="https://wa.me/6281234567890" class="text-primary-600 hover:underline">
                            0812-3456-7890 (WhatsApp)
                        </a>
                    </p>
                    <p class="flex items-center gap-2 text-gray-600">
                        <span>📧</span>
                        <a href="mailto:kepegawaian@uinsaizu.ac.id" class="text-primary-600 hover:underline">
                            kepegawaian@uinsaizu.ac.id
                        </a>
                    </p>
                    <p class="flex items-center gap-2 text-gray-600">
                        <span>🕐</span>
                        <span>Senin - Jumat, 08.00 - 16.00 WIB</span>
                    </p>
                </div>
            </div>

            {{-- Process Info --}}
            <div class="bg-blue-50 rounded-xl p-5 text-left mb-6 border border-blue-200">
                <h3 class="font-semibold text-blue-800 mb-3">Proses Reset Password:</h3>
                <ol class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start gap-2">
                        <span
                            class="w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-bold shrink-0">1</span>
                        <span>Hubungi admin via WhatsApp atau Email</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span
                            class="w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-bold shrink-0">2</span>
                        <span>Sebutkan <strong>NIP</strong> dan <strong>Nama lengkap</strong> Anda</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span
                            class="w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-bold shrink-0">3</span>
                        <span>Admin reset password ke <strong>tanggal lahir (DDMMYYYY)</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span
                            class="w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-bold shrink-0">4</span>
                        <span>Login dengan password baru</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span
                            class="w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-bold shrink-0">5</span>
                        <span><strong>Wajib ganti password</strong> saat pertama login</span>
                    </li>
                </ol>
            </div>

            {{-- Back to Login --}}
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Login
            </a>
        </div>
    </div>
</div>
