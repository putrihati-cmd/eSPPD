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

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-5xl font-bold text-gray-900 mb-8">Panduan Pengguna e-SPPD</h1>

        <!-- Table of Contents -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Daftar Isi</h2>
            <ul class="space-y-2">
                <li><a href="#intro" class="text-teal-700 hover:underline">1. Pendahuluan</a></li>
                <li><a href="#login" class="text-teal-700 hover:underline">2. Cara Login</a></li>
                <li><a href="#create-spd" class="text-teal-700 hover:underline">3. Membuat SPPD Baru</a></li>
                <li><a href="#submit" class="text-teal-700 hover:underline">4. Mengajukan SPPD</a></li>
                <li><a href="#approval" class="text-teal-700 hover:underline">5. Proses Persetujuan</a></li>
                <li><a href="#report" class="text-teal-700 hover:underline">6. Membuat Laporan Perjalanan</a></li>
                <li><a href="#faq" class="text-teal-700 hover:underline">7. FAQ</a></li>
            </ul>
        </div>

        <!-- Section 1: Intro -->
        <section id="intro" class="mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">1. Pendahuluan</h2>
                <p class="text-gray-700 mb-4">
                    e-SPPD (Sistem Informasi Perjalanan Dinas) adalah platform digital untuk mengajukan, menyetujui, dan melaporkan
                    perjalanan dinas di lingkungan UIN SAIZU Purwokerto.
                </p>
                <div class="bg-teal-50 border-l-4 border-teal-700 p-4 mt-4">
                    <p class="font-semibold text-teal-900">Keuntungan menggunakan e-SPPD:</p>
                    <ul class="list-disc list-inside text-teal-800 mt-2 space-y-1">
                        <li>Pengajuan lebih cepat dan mudah</li>
                        <li>Tracking real-time status perjalanan</li>
                        <li>Laporan terintegrasi dengan sistem keuangan</li>
                        <li>Riwayat lengkap tersimpan digital</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Section 2: Login -->
        <section id="login" class="mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">2. Cara Login</h2>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Langkah 1: Buka Halaman Login</h3>
                        <p class="text-gray-700 mb-3">Kunjungi halaman login e-SPPD dan Anda akan melihat form login dengan field:</p>
                        <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                            <li><strong>Username / NIP:</strong> Masukkan NIP Anda (15 digit)</li>
                            <li><strong>Password:</strong> Masukkan password Anda</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Langkah 2: Lupa Password?</h3>
                        <p class="text-gray-700">Jika lupa password, klik link "Lupa password?" dan ikuti proses reset password melalui OTP.</p>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Langkah 3: Masuk</h3>
                        <p class="text-gray-700">Klik tombol "Masuk ke Dashboard" untuk masuk ke sistem.</p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-700 p-4 mt-4">
                        <p class="font-semibold text-yellow-900">💡 Tips:</p>
                        <p class="text-yellow-800">Pastikan NIP dan password Anda benar. Jika masih bermasalah, hubungi administrator.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Create SPPD -->
        <section id="create-spd" class="mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">3. Membuat SPPD Baru</h2>
                <div class="space-y-6">
                    <p class="text-gray-700">
                        SPPD (Surat Perintah Perjalanan Dinas) adalah dokumen formal untuk perjalanan dinas. Berikut cara membuat SPPD baru:
                    </p>

                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold">1</div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Klik Menu "SPD"</h4>
                                <p class="text-gray-700">Pilih menu SPD dari sidebar dashboard Anda.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold">2</div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Klik Tombol "Buat SPPD Baru"</h4>
                                <p class="text-gray-700">Anda akan diarahkan ke form pembuatan SPPD baru.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold">3</div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Isi Informasi SPPD</h4>
                                <p class="text-gray-700">Lengkapi form dengan detail perjalanan:</p>
                                <ul class="list-disc list-inside text-gray-700 ml-4 mt-2 space-y-1">
                                    <li>Tujuan perjalanan</li>
                                    <li>Tanggal berangkat dan kembali</li>
                                    <li>Tujuan perjalanan dan alasan</li>
                                    <li>Peserta perjalanan</li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold">4</div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Simpan SPPD</h4>
                                <p class="text-gray-700">Klik tombol "Simpan" untuk menyimpan SPPD sebagai draft.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 4: Submit -->
        <section id="submit" class="mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">4. Mengajukan SPPD</h2>
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Setelah SPPD selesai dibuat, Anda dapat mengajukannya untuk disetujui:
                    </p>
                    <ol class="list-decimal list-inside text-gray-700 space-y-2">
                        <li>Buka SPPD draft Anda dari menu SPD</li>
                        <li>Periksa kembali semua informasi</li>
                        <li>Klik tombol "Ajukan untuk Persetujuan"</li>
                        <li>SPPD akan dikirim ke atasan/penyetuju</li>
                    </ol>

                    <div class="bg-blue-50 border-l-4 border-blue-700 p-4 mt-4">
                        <p class="font-semibold text-blue-900">ℹ️ Catatan:</p>
                        <p class="text-blue-800">Setelah diajukan, Anda tidak bisa mengubah SPPD lagi. Jika ada yang salah, Anda perlu membuat SPPD baru.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 5: Approval -->
        <section id="approval" class="mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">5. Proses Persetujuan</h2>
                <p class="text-gray-700 mb-4">
                    SPPD yang diajukan akan melewati beberapa tahap persetujuan:
                </p>

                <div class="space-y-4">
                    <div class="border-l-4 border-teal-700 pl-4">
                        <h4 class="font-semibold text-gray-900">Kaprodi/Kabag Level</h4>
                        <p class="text-gray-600">Persetujuan pertama dari atasan langsung (Kaprodi atau Kabag)</p>
                    </div>

                    <div class="border-l-4 border-teal-700 pl-4">
                        <h4 class="font-semibold text-gray-900">Wadek Level</h4>
                        <p class="text-gray-600">Persetujuan dari Wakil Dekan jika diperlukan</p>
                    </div>

                    <div class="border-l-4 border-teal-700 pl-4">
                        <h4 class="font-semibold text-gray-900">Dekan Level</h4>
                        <p class="text-gray-600">Persetujuan akhir dari Dekan</p>
                    </div>

                    <div class="border-l-4 border-teal-700 pl-4">
                        <h4 class="font-semibold text-gray-900">Bendahara</h4>
                        <p class="text-gray-600">Verifikasi anggaran dan persiapan pembayaran</p>
                    </div>
                </div>

                <div class="bg-green-50 border-l-4 border-green-700 p-4 mt-6">
                    <p class="font-semibold text-green-900">✓ Status Tracking:</p>
                    <p class="text-green-800">Anda dapat melihat status SPPD Anda kapan saja di dashboard dengan indikator visual.</p>
                </div>
            </div>
        </section>

        <!-- Section 6: Report -->
        <section id="report" class="mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">6. Membuat Laporan Perjalanan</h2>
                <p class="text-gray-700 mb-4">
                    Setelah perjalanan selesai, Anda harus membuat laporan perjalanan:
                </p>

                <div class="space-y-4">
                    <p class="text-gray-700"><strong>Langkah:</strong></p>
                    <ol class="list-decimal list-inside text-gray-700 space-y-2 ml-4">
                        <li>Buka SPPD yang sudah disetujui</li>
                        <li>Klik tombol "Buat Laporan Perjalanan"</li>
                        <li>Isi detail laporan (hasil perjalanan, biaya, dokumen pendukung)</li>
                        <li>Unggah file laporan jika diperlukan</li>
                        <li>Submit laporan untuk diverifikasi</li>
                    </ol>
                </div>

                <div class="bg-orange-50 border-l-4 border-orange-700 p-4 mt-6">
                    <p class="font-semibold text-orange-900">⚠️ Penting:</p>
                    <p class="text-orange-800">Laporan harus dibuat dalam waktu maksimal 7 hari setelah perjalanan selesai. Lampirkan semua bukti pengeluaran yang diperlukan.</p>
                </div>
            </div>
        </section>

        <!-- Section 7: FAQ -->
        <section id="faq" class="mb-12">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">7. Pertanyaan yang Sering Diajukan (FAQ)</h2>

                <div class="space-y-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">❓ Berapa lama proses persetujuan SPPD?</h4>
                        <p class="text-gray-700">Biasanya 2-3 hari kerja, tergantung dari ketersediaan penyetuju.</p>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">❓ Bagaimana jika SPPD ditolak?</h4>
                        <p class="text-gray-700">Jika SPPD ditolak, Anda akan mendapat notifikasi dengan alasan penolakan. Anda dapat membuat SPPD baru dengan perbaikan.</p>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">❓ Apakah bisa mengubah SPPD setelah diajukan?</h4>
                        <p class="text-gray-700">Tidak. SPPD tidak bisa diubah setelah diajukan. Jika ada kesalahan, buat SPPD baru.</p>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">❓ Bagaimana jika lupa NIP atau password?</h4>
                        <p class="text-gray-700">Hubungi bagian administrasi atau gunakan fitur "Lupa Password" di halaman login untuk reset password via OTP.</p>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">❓ Apakah ada biaya untuk menggunakan e-SPPD?</h4>
                        <p class="text-gray-700">Tidak ada biaya. e-SPPD adalah layanan gratis dari universitas.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Support Section -->
        <section class="mb-12">
            <div class="bg-gradient-to-r from-teal-700 to-blue-700 rounded-lg shadow-lg p-8 text-white">
                <h2 class="text-3xl font-bold mb-4">Butuh Bantuan?</h2>
                <p class="mb-6">Jika Anda mengalami kesulitan atau punya pertanyaan, jangan ragu untuk menghubungi tim support kami.</p>
                <div class="space-y-2">
                    <p><strong>Email:</strong> support@esppd.uninsaizu.ac.id</p>
                    <p><strong>Telepon:</strong> (0281) 123-4567</p>
                    <p><strong>Jam Kerja:</strong> Senin - Jumat, 08:00 - 16:00 WIB</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12 mt-16">
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
