@extends('layouts.guest')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-amber-500 to-orange-600 p-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-2xl p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800">Ganti Password</h1>
                    <p class="text-gray-600 mt-2">Anda harus mengganti password default sebelum melanjutkan</p>
                </div>

                @if (session('warning'))
                    <div class="mb-4 p-4 bg-amber-100 border border-amber-400 text-amber-800 rounded-lg">
                        {{ session('warning') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.force-update') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru
                        </label>
                        <input type="password" name="password" id="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                            placeholder="Minimal 8 karakter" minlength="8" required autofocus>
                        <p class="mt-1 text-xs text-gray-500">Password minimal 8 karakter dan tidak boleh sama dengan
                            tanggal lahir</p>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                            placeholder="Ulangi password baru" required>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition">
                        Simpan Password Baru
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
