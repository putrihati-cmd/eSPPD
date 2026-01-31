@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-teal-50 to-blue-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8">
                    <span class="text-lg font-bold text-teal-700">e-SPPD</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('about') }}" class="text-gray-600 hover:text-teal-700">About</a>
                    <a href="{{ route('guide') }}" class="text-gray-600 hover:text-teal-700">Guide</a>
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-teal-700 text-white rounded-lg hover:bg-teal-800">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div>
                <span class="inline-block px-4 py-2 bg-teal-100 text-teal-700 rounded-full text-sm font-semibold mb-6">
                    UIN SAIZU PURWOKERTO
                </span>
                <h1 class="text-5xl font-bold text-gray-900 mb-6">
                    Sistem Informasi <br>
                    <span class="text-teal-700">Perjalanan Dinas</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Transformasi digital pengajuan dan pelaporan perjalanan dinas di lingkungan UIN SAIZU yang lebih efisien,
                    transparan, dan akuntabel.
                </p>

                <!-- Key Features -->
                <div class="space-y-4 mb-8">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-teal-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Proses pengajuan yang mudah dan cepat</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-teal-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Transparansi penuh dalam setiap tahap perjalanan</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-teal-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Laporan terintegrasi dengan sistem keuangan</span>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-4">
                    <a href="{{ route('guide') }}" class="px-6 py-3 bg-yellow-400 text-black font-semibold rounded-lg hover:bg-yellow-500 transition">
                        Pelajari Lebih Lanjut
                    </a>
                    <a href="{{ route('login') }}" class="px-6 py-3 border-2 border-teal-700 text-teal-700 font-semibold rounded-lg hover:bg-teal-50 transition">
                        Panduan Pengguna
                    </a>
                </div>
            </div>

            <!-- Right Stats -->
            <div class="space-y-8">
                <div class="bg-white rounded-xl p-8 shadow-lg">
                    <div class="text-5xl font-bold text-teal-700 mb-2">500+</div>
                    <p class="text-gray-600 font-semibold">PERJALANAN DINAS</p>
                    <p class="text-sm text-gray-500 mt-2">Terproses dengan sistem e-SPPD</p>
                </div>

                <div class="bg-white rounded-xl p-8 shadow-lg">
                    <div class="text-5xl font-bold text-teal-700 mb-2">50+</div>
                    <p class="text-gray-600 font-semibold">DOSEN & STAFF</p>
                    <p class="text-sm text-gray-500 mt-2">Menggunakan platform ini</p>
                </div>

                <div class="bg-white rounded-xl p-8 shadow-lg">
                    <div class="text-5xl font-bold text-teal-700 mb-2">99%</div>
                    <p class="text-gray-600 font-semibold">TINGKAT KEPUASAN</p>
                    <p class="text-sm text-gray-500 mt-2">Dari pengguna kami</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-center text-gray-900 mb-16">Fitur Unggulan</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-8 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="text-4xl mb-4">📋</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pengajuan Mudah</h3>
                    <p class="text-gray-600">Sistem pengajuan perjalanan yang intuitif dengan form yang jelas dan panduan langkah demi langkah.</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="text-4xl mb-4">✅</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Approval Workflow</h3>
                    <p class="text-gray-600">Proses persetujuan yang terstruktur dengan notifikasi real-time dan tracking status perjalanan.</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 border border-gray-200 rounded-xl hover:shadow-lg transition">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Laporan Terintegrasi</h3>
                    <p class="text-gray-600">Laporan perjalanan yang terintegrasi dengan sistem keuangan untuk audit trail lengkap.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-teal-700 to-blue-700 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Siap memulai?</h2>
            <p class="text-xl text-teal-100 mb-8">Login ke dashboard Anda dan mulai kelola perjalanan dinas dengan mudah.</p>
            <a href="{{ route('login') }}" class="inline-block px-8 py-4 bg-yellow-400 text-black font-bold rounded-lg hover:bg-yellow-500 transition text-lg">
                Masuk ke Dashboard
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="font-bold text-white mb-4">e-SPPD</h4>
                    <p class="text-sm">Sistem Informasi Perjalanan Dinas UIN SAIZU</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Navigasi</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('about') }}" class="hover:text-white">About</a></li>
                        <li><a href="{{ route('guide') }}" class="hover:text-white">Guide</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Kontak</h4>
                    <p class="text-sm">UIN SAIZU Purwokerto<br>Jawa Tengah, Indonesia</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Integrasi Dengan</h4>
                    <ul class="space-y-2 text-sm">
                        <li>UIN SAIZU</li>
                        <li>PUSKOM</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 pt-8">
                <p class="text-center text-sm">&copy; 2026 e-SPPD. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
@endsection
