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

<div class="flex-1 flex items-center justify-center p-6 lg:p-12 z-20 w-screen min-h-screen bg-brand-teal relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-teal-400/20 blur-[120px] rounded-full -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-lime-400/20 blur-[120px] rounded-full translate-y-1/2 -translate-x-1/2"></div>

    <div class="w-full max-w-lg animate-fade-in relative z-10">
        <div class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl p-10 lg:p-14 border border-white/20">
            
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-teal-500 to-teal-700 rounded-3xl flex items-center justify-center shadow-lg shadow-teal-500/30 transform -rotate-6">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Lupa Password?</h1>
                <p class="text-gray-500 font-medium">Jangan khawatir, kami akan membantu Anda</p>
            </div>

            <!-- Warning/Guide Section -->
            <div class="space-y-6">
                <div class="bg-teal-50/50 border-2 border-teal-100 rounded-3xl p-6 text-left relative overflow-hidden group hover:bg-teal-50 transition-colors">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-teal-500/5 rounded-full blur-2xl group-hover:bg-teal-500/10 transition-all"></div>
                    <h2 class="font-bold text-teal-900 mb-4 flex items-center gap-3">
                        <span class="p-1.5 bg-teal-500 rounded-lg text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Proses Pengaturan Ulang
                    </h2>

                    <ol class="space-y-4 text-sm font-medium text-teal-800">
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-teal-200 text-teal-800 rounded-full flex items-center justify-center text-xs font-black">1</span>
                            <span>Sampaikan permintaan reset ke Bagian Kepegawaian</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-teal-200 text-teal-800 rounded-full flex items-center justify-center text-xs font-black">2</span>
                            <span>Admin akan mereset ke password standar: <strong class="bg-teal-200/50 px-1 rounded">DDMMYYYY</strong> (Tanggal Lahir)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-teal-200 text-teal-800 rounded-full flex items-center justify-center text-xs font-black">3</span>
                            <span>Anda diwajibkan mengganti password saat pertama kali masuk kembali</span>
                        </li>
                    </ol>
                </div>

                <!-- Contact Grid -->
                <div class="grid grid-cols-1 gap-4">
                    <a href="https://wa.me/6281234567890" target="_blank" class="flex items-center gap-4 p-4 bg-gray-50 border-2 border-gray-100 rounded-2xl hover:border-emerald-500 hover:bg-emerald-50 transition-all group">
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.483 8.413-.003 6.557-5.338 11.892-11.893 11.892-1.997 0-3.951-.5-5.688-1.448l-6.308 1.654zm6.222-3.619c1.605.952 3.253 1.458 4.937 1.459 5.353 0 9.711-4.358 9.713-9.711.001-2.592-1.008-5.028-2.846-6.867-1.837-1.838-4.272-2.848-6.865-2.848-5.354 0-9.712 4.357-9.715 9.711-.001 1.834.516 3.618 1.492 5.183l-.973 3.551 3.64l-.953zm10.741-6.829c-.279-.14-.1.65-.1.65s-.25.2.25.04c-.279.14-1.647-.607-2.614-1.455-.909-.795-1.528-1.745-1.528-1.745l-.266-.118c-.287-.145-.487-.205-.687-.04-.15.124-.65.25-.65.25s-.85.75-1.1.85c-.25.1-1.35.45-1.6-.35s-.75-2.05-.1-3.3c.1-.2.4-.45.6-.5.2-.05.45-.1.6-.1.15 0 .3.05.45.1.15.1.4.3.5.55s.5 1.2.5 1.2.1.25-.05.55-.45.75-.7 1.05s-.55.6-.25 1.1c.3.5.75 1.15 1.35 1.7.6.55 1.3 1.05 1.8 1.2.5.15 1 .15 1.35-.15s1.2-1.1 1.2-1.1.25-.3.45-.3c.2 0 .4.1.65.2l1.2.6c.25.1.4.2.45.3.05.15.2.25-.08.38z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">WhatsApp Kepegawaian</p>
                            <p class="text-lg font-black text-gray-900">0812-3456-7890</p>
                        </div>
                    </a>

                    <a href="mailto:kepegawaian@uinsaizu.ac.id" class="flex items-center gap-4 p-4 bg-gray-50 border-2 border-gray-100 rounded-2xl hover:border-teal-500 hover:bg-teal-50 transition-all group">
                        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Email Support</p>
                            <p class="text-lg font-black text-gray-900 text-sm lg:text-base text-ellipsis overflow-hidden">kepegawaian@uinsaizu.ac.id</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Back to Login -->
            <div class="mt-10 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-3 px-8 py-4 bg-gray-900 text-white font-black rounded-2xl shadow-xl hover:bg-gray-800 hover:-translate-y-1 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Login
                </a>
                <p class="mt-8 text-[10px] text-gray-400 font-bold uppercase tracking-widest text-center">UIN SAIZU Purwokerto</p>
            </div>
        </div>
    </div>
</div>
