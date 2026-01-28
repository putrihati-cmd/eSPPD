🎯 ROLE-BASED FEATURE DIFFERENTIATION
Buat sistem eSPPD dengan fitur yang berbeda untuk setiap role, namun dengan UI/UX yang konsisten (menggunakan design system yang sama).

1. DOSEN (Role: Dosen)
   Fitur Utama:

Ajukan SPPD baru dengan form auto-fill

Melihat daftar SPPD yang diajukan (status draft, pending, approved, rejected)

Melacak status persetujuan SPPD

Mengunggah bukti dan membuat laporan setelah perjalanan

Menerima notifikasi status SPPD

Akses Terbatas:

Hanya bisa mengedit/hapus SPPD dengan status draft

Hanya bisa melihat SPPD milik sendiri

2. ATASAN (Role: Atasan - Kaprodi/Kajur/DeKan)
   Fitur Utama:

Melihat dashboard antrian persetujuan SPPD dari bawahan

Menyetujui atau menolak SPPD dengan memberikan alasan

Melakukan bulk approval untuk beberapa SPPD sekaligus

Mendelegasikan wewenang persetujuan kepada atasan lain (jika berhalangan)

Melihat riwayat persetujuan yang telah dilakukan

Akses Terbatas:

Hanya bisa menyetujui/menolak SPPD dari bawahan langsung

Tidak bisa mengedit data SPPD, hanya approve/reject

3. BENDAHARA (Role: Bendahara)
   Fitur Utama:

Verifikasi anggaran dan kelengkapan dokumen SPPD

Memberikan persetujuan keuangan (setelah atasan menyetujui)

Mengelola proses pembayaran uang muka dan pelunasan

Mencetak dokumen SPPD resmi untuk ditandatangani

Membuat laporan keuangan perjalanan dinas

Akses Terbatas:

Hanya bisa memproses SPPD yang sudah disetujui atasan

Tidak bisa mengubah data perjalanan, hanya verifikasi administrasi keuangan

4. ADMIN (Role: Admin Sistem)
   Fitur Utama:

Mengelola user dan role (Dosen, Atasan, Bendahara, Admin)

Mengkonfigurasi sistem (template dokumen, workflow approval, dll)

Melihat audit log seluruh aktivitas sistem

Backup dan restore data

Monitoring kesehatan sistem

Akses Penuh:

Akses ke semua fitur dan data

Tidak terlibat dalam workflow bisnis (tidak approve SPPD)

🔐 PERMISSION MATRIX
Fitur / Modul Dosen Atasan Bendahara Admin
Ajukan SPPD ✅ ❌ ❌ ❌
Edit SPPD (draft) ✅ ❌ ❌ ❌
Hapus SPPD (draft) ✅ ❌ ❌ ❌
Lihat SPPD sendiri ✅ ✅* ✅* ✅
Lihat SPPD bawahan ❌ ✅ ❌ ✅
Lihat semua SPPD ❌ ❌ ❌ ✅
Approve/Reject SPPD ❌ ✅ ❌ ❌
Verifikasi Keuangan ❌ ❌ ✅ ❌
Cetak Dokumen Resmi ✅ ✅ ✅ ✅
Buat Laporan Perjalanan ✅ ❌ ❌ ❌
Verifikasi Laporan ❌ ❌ ✅ ❌
Kelola User ❌ ❌ ❌ ✅
Konfigurasi Sistem ❌ ❌ ❌ ✅
Audit Log ❌ ❌ ❌ ✅
\*Hanya jika terkait dengan proses yang diotorisasi

📱 IMPLEMENTASI UI YANG KONSISTEN
Komponen UI Sama, Konten Berbeda:
Dashboard: Komponen card sama, tetapi menampilkan data berbeda sesuai role

Navigation Menu: Struktur menu sama, tetapi item menu berbeda sesuai role

Tables: Komponen tabel sama, tetapi kolom dan data berbeda

Forms: Komponen input sama, tetapi field yang ditampilkan/editable berbeda

Contoh:
javascript
// Komponen DashboardCard sama untuk semua role
<DashboardCard 
  title={roleSpecificTitle} 
  data={roleSpecificData}
  actions={roleSpecificActions}
/>

// Tampilan berbeda berdasarkan role
Dosen: title="SPPD Saya", data=[draft: 3, pending: 2]
Atasan: title="Menunggu Persetujuan", data=[pending: 8, urgent: 3]
Bendahara: title="Verifikasi Keuangan", data=[pending: 5, overdue: 2]
Admin: title="Statistik Sistem", data=[users: 45, sppd: 120]
🚀 IMPLEMENTASI TEKNIS
Backend (Laravel):
Middleware role-based authorization

Policy untuk setiap model (SPPD, User, dll)

Query scope untuk membatasi data berdasarkan role

Frontend (Blade/Inertia):
Conditionally render components berdasarkan role

Service untuk mengelola permission checks

Route guards untuk halaman yang terproteksi

📦 OUTPUT YANG DIHARAPKAN
Role Permission Matrix lengkap untuk semua fitur

Middleware & Policy implementation guidelines

UI Component adaptation rules untuk setiap role

API endpoint security requirements

Database schema untuk user roles dan permissions

Catatan: UI tetap menggunakan design system yang sama (warna, komponen, layout), hanya konten dan fungsionalitas yang berbeda per role. Ini memudahkan development dan maintenance, serta memberikan pengalaman yang konsisten di seluruh aplikasi.

🎯 PROMPT: ROLE-BASED FEATURE DIFFERENTIATION
Buat implementasi sistem eSPPD dengan fitur berbeda per role, tetapi UI/UX design tetap konsisten menggunakan design system yang sudah ditentukan.

PERBEDAAN FITUR PER ROLE:

1. DOSEN (Role: Dosen)
   Fitur Utama:

Dashboard: Statistik SPPD pribadi (draft, pending, approved, rejected)

Menu:

"Ajukan SPPD Baru" (dengan auto-fill biodata)

"SPPD Saya" (list riwayat)

"Buat Laporan" (setelah perjalanan)

"Profil Saya" (lihat/edit data pribadi)

Tidak bisa: Approve SPPD orang lain, akses data bendahara, konfigurasi sistem

2. ATASAN (Role: Atasan - Kaprodi/Kajur/DeKan)
   Fitur Utama:

Dashboard: Antrian persetujuan SPPD dari bawahan + statistik approval

Menu:

"Antrian Persetujuan" (filter by status, unit)

"SPPD Bawahan" (monitoring)

"Delegasi Wewenang" (jika berhalangan)

"Bulk Approval" (setujui banyak sekaligus)

Tambahan fitur: Approve/reject dengan alasan, view all SPPD dalam unitnya

3. BENDAHARA (Role: Bendahara)
   Fitur Utama:

Dashboard: Status anggaran per unit, outstanding payments, pending verifikasi

Menu:

"Verifikasi Anggaran" (cek ketersediaan dana)

"Proses Pembayaran" (pencairan uang muka/pelunasan)

"Laporan Keuangan" (report per periode)

"SPJ Settlement" (pertanggungjawaban akhir)

Tambahan fitur: Verifikasi keuangan, generate dokumen pembayaran, budget tracking

4. ADMIN (Role: Admin Sistem)
   Fitur Utama:

Dashboard: System health, user activity, error logs, storage usage

Menu:

"User Management" (tambah/edit user, assign role)

"System Configuration" (setting aplikasi)

"Template Manager" (upload template dokumen)

"Audit Log" (lihat semua aktivitas)

"Backup & Restore" (management data)

Full access: Semua fitur + sistem settings

IMPLEMENTASI TEKNIS:

1. ROLE-BASED ROUTING:
   php
   // Routes akan berbeda per role
   Dosen: /dashboard, /sppd/create, /sppd/my
   Atasan: /dashboard, /approval/queue, /approval/history
   Bendahara: /dashboard, /finance/verification, /finance/payments
   Admin: /dashboard, /admin/users, /admin/config
2. PERMISSION MIDDLEWARE:
   php
   // Middleware untuk membatasi akses
   Route::middleware(['role:dosen'])->group(...);
   Route::middleware(['role:atasan'])->group(...);
   Route::middleware(['role:bendahara'])->group(...);
   Route::middleware(['role:admin'])->group(...);
3. CONDITIONAL UI RENDERING:
   blade
   {{-- Komponen hanya muncul untuk role tertentu --}}
   @role('atasan')
   <x-button-approve />
   <x-button-reject />
   @endrole

@role('bendahara')
<x-budget-status />
<x-payment-processor />
@endrole
UI/UX YANG SAMA TETAP:
Konsistensi Design:
Warna: Semua role menggunakan palet warna #02A0AC, #CBE155, dll

Komponen: Tombol, card, form elements sama semua

Layout: Struktur halaman konsisten (header, sidebar, content area)

Typography: Font dan ukuran sama semua

Perbedaan Hanya Pada:
Menu items di sidebar

Dashboard widgets dan data yang ditampilkan

Action buttons yang tersedia

Data visibility (hanya data yang diperbolehkan)

CONTOH IMPLEMENTASI:
File: app/Http/Middleware/CheckRole.php
php
class CheckRole
{
public function handle($request, Closure $next, ...$roles)
{
if (!in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }
        return $next($request);
}
}
File: resources/views/layouts/app.blade.php
blade
{{-- Sidebar menu berdasarkan role --}}
@if(auth()->user()->role === 'dosen')
@include('partials.menu-dosen')
@elseif(auth()->user()->role === 'atasan')
@include('partials.menu-atasan')
@elseif(auth()->user()->role === 'bendahara')
@include('partials.menu-bendahara')
@elseif(auth()->user()->role === 'admin')
@include('partials.menu-admin')
@endif
File: app/Http/Controllers/DashboardController.php
php
public function index()
{
$user = auth()->user();

    switch($user->role) {
        case 'dosen':
            $data = $this->getDosenDashboardData($user);
            return view('dashboard.dosen', $data);

        case 'atasan':
            $data = $this->getAtasanDashboardData($user);
            return view('dashboard.atasan', $data);

        case 'bendahara':
            $data = $this->getBendaharaDashboardData($user);
            return view('dashboard.bendahara', $data);

        case 'admin':
            $data = $this->getAdminDashboardData($user);
            return view('dashboard.admin', $data);
    }

}
OUTPUT YANG DIHARAPKAN:
Role-based routing configuration

Permission middleware implementation

Conditional menu rendering

Role-specific dashboard views (menggunakan layout yang sama)

Database schema untuk user roles dan permissions

Prinsip: Satu design system, multiple user experiences berdasarkan role. UI tetap konsisten, fungsionalitas yang berbeda.
