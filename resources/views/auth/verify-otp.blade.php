@extends('layouts.guest')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 to-primary-800 p-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-2xl p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">Verifikasi Kode</h1>
                    <p class="text-gray-600 mt-2">Masukkan 6 digit kode yang dikirim ke kontak Anda</p>
                </div>

                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.verify-otp') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-6">
                        <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Verifikasi (6 digit)
                        </label>
                        <input type="text" name="otp" id="otp"
                            class="w-full px-4 py-4 text-center text-2xl tracking-widest font-mono border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                            placeholder="______" maxlength="6" pattern="\d{6}" inputmode="numeric" required autofocus>
                    </div>

                    <div class="mb-6 text-center">
                        <p class="text-sm text-gray-600">
                            Kode berlaku <span class="font-semibold text-primary-600" id="countdown">15:00</span>
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
                        Verifikasi
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Tidak menerima kode?
                        <a href="{{ route('password.request') }}"
                            class="text-primary-600 hover:text-primary-700 font-medium">
                            Kirim Ulang
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer (15 minutes)
        let time = 900;
        const timer = setInterval(() => {
            time--;
            const mins = Math.floor(time / 60);
            const secs = time % 60;
            document.getElementById('countdown').textContent =
                `${mins}:${secs.toString().padStart(2, '0')}`;

            if (time <= 0) {
                clearInterval(timer);
                document.getElementById('countdown').textContent = 'Expired';
            }
        }, 1000);

        // Auto-format OTP input
        document.getElementById('otp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    </script>
@endsection
