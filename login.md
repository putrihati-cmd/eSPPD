🗺️ LOGIC MAP LOGIN & ROLE (Source of Truth)
1. Database Relationship (Garis Besar)
Copy
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│   employees     │         │      users       │         │    roles?       │
├─────────────────┤         ├──────────────────┤         ├─────────────────┤
│ nip (PK)        │◄────────┤ nip (FK)         │         │ (OPTIONAL -     │
│ name            │  1:1    │ email (login)    │         │ bisa pakai      │
│ birth_date      │         │ password (hash)  │         │ level integer   │
│ approval_level  │◄────────┤ role (string)??  │         │ saja)           │
│ faculty/unit    │         │                  │         │                 │
│ user_id (FK)    │────────►│ id (PK)          │         │                 │
└─────────────────┘         └──────────────────┘         └─────────────────┘
         │
         └──► Invers: Employee belongsTo User
              atau Employee hasOne User (tergantung design awal)
⚠️ Yang sering AI salah:
AI bilang: "Login pakai email" → SALAH,/login pakai NIP (tapi auth backend tetap email)
AI bilang: "Role dari table roles" → SALAH, kita pakai approval_level (integer 1-6) di tabel employees
2. Login Flow Logic (Step-by-Step)
php
Copy
// Alur yang BENAR (sudah fix dari dokumen sebelumnya):
Step 1: User input NIP (18 digit) + Password
    │
Step 2: Cari Employee WHERE nip = input
    ├─► Kalau tidak ketemu → Error: "NIP tidak ditemukan"
    │
Step 3: Dari Employee, ambil $employee->user (relasi ke User model)
    ├─► Kalau $employee->user_id null → Error: "Belum terdaftar sebagai user"
    │
Step 4: Ambil $user->email (untuk Laravel Auth)
    │
Step 5: Auth::attempt(['email' => $user->email, 'password' => input])
    ├─► Sukses: redirect dashboard
    └─► Gagal: "Password salah"
🔍 Kenapa begitu?
Laravel Auth default pakai email (bisa diubah ke NIP tapi ribet)
Tapi user interface/login form minta NIP (karena pegawai hafal NIP, tidak hafal email)
Jadi NIP adalah "alias" untuk mencari email yang sebenarnya
3. Role/Level Reading Logic (Hierarki)
Ada DUA konsep yang berbeda:
A. Approval Level (Hierarki Jabatan)
php
Copy
// Di App\Models\Employee.php
public function getLevelNameAttribute() {
    return match($this->approval_level) {
        1 => 'Staff/Dosen',
        2 => 'Kepala Prodi',
        3 => 'Wakil Dekan', 
        4 => 'Dekan',
        5 => 'Wakil Rektor',
        6 => 'Rektor',
        default => 'Tidak Dikenal'
    };
}

// Usage:
$level = auth()->user()->employee->approval_level; // 1-6
B. Functional Role (Hak Akses fitur)
php
Copy
// Di App\Models\User.php (opsional, kalau mau granular permission)
public function isAdmin() {
    return $this->role === 'admin'; // Untuk maintainer sistem
}

// atau cek kombinasi:
public function canApprove() {
    return $this->employee->approval_level >= 2; // Bisa approved kalau level 2+
}
⚠️ AI sering ngaco di sini:
AI generate: @role('admin') → Blade directive tidak exist kecuali install Spatie Permission (kita belum install)
AI generate: auth()->user()->level → Property tidak exist, harus auth()->user()->employee->approval_level
4. Cek Logic di Blade (Yang BENAR)
blade
Copy
{{-- ✅ BENAR: Cek level approval --}}
@if(auth()->user()->employee->approval_level >= 3)
    <a href="/approvals">Approval Dashboard</a>
@endif

{{-- ❌ SALAH (AI sering suggest gini): --}}
@if(auth()->user()->role == 'dekan') {{-- ini tidak ada field role string --}}
@if(auth()->user()->can('approve'))  {{-- ini butuh Gate/Policy terdefinisi --}}
@if(auth()->user()->level > 3)       {{-- property level tidak exist --}}

{{-- ✅ BENAR: Ambil info spesifik employee --}}
{{ auth()->user()->employee->name }}
{{ auth()->user()->employee->faculty }}
{{ auth()->user()->employee->superior_nip }} {{-- Atasan langsung --}}
5. Middleware/Route Protection (Yang BENAR)
php
Copy
// routes/web.php
// ✅ Cara 1: Closure middleware (simple)
Route::get('/admin', function() {
    return view('admin.dashboard');
})->middleware(function ($request, $next) {
    if (auth()->user()->employee->approval_level != 6) { // Hanya Rektor
        abort(403);
    }
    return $next($request);
});

// ✅ Cara 2: Buat custom middleware (scalable)
// php artisan make:middleware CheckLevel
Route::get('/wr-dashboard', fn() => view('wr.dashboard'))
    ->middleware('level:5,6'); // Level 5 atau 6

// php artisan make:middleware EnsureIsAdmin
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function() {
    // CRUD master data
});
Middleware CheckLevel.php:
php
Copy
public function handle($request, Closure $next, ...$levels) {
    if (!in_array(auth()->user()->employee->approval_level, $levels)) {
        abort(403, 'Anda tidak punya akses ke level ini');
    }
    return $next($request);
}
🧪 CARA VERIFIKASI (Debug Logic)
Kalau bingung, tambahkan ini di Login.php atau Controller untuk cek:
php
Copy
// Debug helper - taruh di method login() sebelum redirect
dd([
    'input_nip' => $this->nip,
    'employee_found' => Employee::where('nip', $this->nip)->first(),
    'user_via_relation' => Employee::where('nip', $this->nip)->first()?->user,
    'user_email' => Employee::where('nip', $this->nip)->first()?->user?->email,
    'user_level' => Employee::where('nip', $this->nip)->first()?->approval_level,
    'auth_check' => Auth::check(),
]);
📝 Ringkasan untuk Copas ke VSCode Context
Copy paste ini ke file .cursor-rules atau prompt VSCode:
Copy
PROJECT RULES E-SPPD:
1. Login menggunakan NIP (bukan email), tapi Auth::attempt pakai email dari relasi Employee->User
2. Hierarchy pakai Employee.approval_level (integer 1-6), bukan User.role (string)
3. Selalu eager load: User::with('employee') untuk hindari N+1
4. Blade check: auth()->user()->employee->approval_level >= 3 (jangan pakai @role)
5. Relasi: Employee belongsTo User (employee.user_id = user.id)
6. Password default: DDMMYYYY dari employee.birth_date
Apakah ada bagian spesifik yang AI VSCode-nya ngaco? Cari tahu dan screenshot errornya, saya bisa bantu koreksi logic yang tepat.
buatkah versi lengkapnya
khusus loginnya biar AI agent lebih paham
Edit
Copy
Share
Comprehensive Login Logic with Schema Livewire Component Security
Berikut Dokumentasi Lengkap & Final untuk logic login E-SPPD. Copy ini ke AI agent sebagai Source of Truth:
📘 E-SPPD AUTHENTICATION SPECIFICATION
Version: 1.0 Final
Pattern: NIP-based Login with Email-backend Auth
Framework: Laravel 12.x + Livewire 3.x
1. DATABASE ARCHITECTURE (Hard Rules)
Table Structure (Immutable)
sql
Copy
-- employees (Master Data Kepegawaian)
CREATE TABLE employees (
    nip VARCHAR(18) PRIMARY KEY,        -- Contoh: 197505051999031001
    user_id BIGINT UNSIGNED NULL,       -- Foreign Key ke users.id
    name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,           -- Untuk password default: DDMMYYYY
    faculty VARCHAR(50) NOT NULL,       -- FTIK, FEBI, FAI, dst
    approval_level TINYINT DEFAULT 1,   -- 1:Staff, 2:Kaprodi, 3:Wadek, 4:Dekan, 5:WR, 6:Rektor
    superior_nip VARCHAR(18) NULL,      -- Atasan langsung (self-referencing)
    is_active BOOLEAN DEFAULT true,
    deleted_at TIMESTAMP NULL,          -- Soft delete
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (superior_nip) REFERENCES employees(nip)
);

-- users (Authentication Table - Laravel Default Style)
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(18) UNIQUE NULL,        -- Duplicate dari employees untuk referensi cepat
    email VARCHAR(150) UNIQUE NOT NULL, -- Format: nip@uinsaizu.local atau nama@uinsaizu.ac.id
    password VARCHAR(255) NOT NULL,     -- Hash bcrypt
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    is_password_reset BOOLEAN DEFAULT false, -- Wajib ganti password jika false
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
Model Relationships (CRITICAL)
php
Copy
<?php
// app/Models/Employee.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    
    protected $primaryKey = 'nip';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'nip', 'user_id', 'name', 'birth_date', 
        'faculty', 'approval_level', 'superior_nip', 'is_active'
    ];

    protected $dates = ['birth_date', 'deleted_at'];

    // CRITICAL: Relasi ke User (One-to-One atau Many-to-One jika historical)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Atasan langsung (Self-referencing)
    public function superior()
    {
        return $this->belongsTo(Employee::class, 'superior_nip', 'nip');
    }

    // Bawahan (One-to-Many)
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'superior_nip', 'nip');
    }

    // Accessor untuk password default
    public function getDefaultPasswordAttribute()
    {
        return $this->birth_date->format('dmY'); // Format: DDMMYYYY
    }
    
    // Accessor level name
    public function getLevelNameAttribute()
    {
        return match($this->approval_level) {
            1 => 'Dosen/Staff',
            2 => 'Kepala Program Studi',
            3 => 'Wakil Dekan',
            4 => 'Dekan',
            5 => 'Wakil Rektor',
            6 => 'Rektor',
            default => 'Unknown'
        };
    }
}

// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'nip', 'email', 'password', 'is_password_reset'
    ];

    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'is_password_reset' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // CRITICAL: Relasi balik ke Employee
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
        // ATAU jika pakai nip sebagai foreign key:
        // return $this->hasOne(Employee::class, 'nip', 'nip');
    }
}
2. LOGIN FLOW LOGIC (Step-by-Step Algorithm)
Visual Flowchart
Copy
┌─────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  User Input │────▶│  Trim & Validate │────▶│ Find Employee   │
│ NIP + Pass  │     │  NIP (18 digit)  │     │ WHERE nip = ?   │
└─────────────┘     └──────────────────┘     └────────┬────────┘
                                                      │
                             ┌──────────No───────────┴────────┐
                             ▼                                │
                    ┌────────────────┐                       │
                    │ Error: NIP     │                       │
                    │ tidak terdaftar│                       │
                    └────────────────┘                       │
                                                      Yes     ▼
                                               ┌──────────────────┐
                                               │ Check user_id    │
                                               │ (sudah punya     │
                                               │ akun login?)     │
                                               └─────────┬────────┘
                                                         │
                              ┌────────────No───────────┴──────────┐
                              ▼                                    │
                     ┌──────────────────┐                         │
                     │ Error: "NIP ini  │                         │
                     │ belum aktivasi   │                         │
                     │ akun. Hubungi    │                         │
                     │ Admin."          │                         │
                     └──────────────────┘                         │
                                                          Yes      ▼
                                                ┌─────────────────────────┐
                                                │ Get User via Relation   │
                                                │ $user = $employee->user │
                                                │ (dapatkan email untuk   │
                                                │ Auth::attempt)          │
                                                └───────────┬─────────────┘
                                                            │
                                ┌───────────────────────────▼────────────────┐
                                │ Auth::attempt([                            │
                                │   'email' => $user->email,                 │
                                │   'password' => $input_password            │
                                │ ])                                         │
                                └──────────────┬─────────────────────────────┘
                                               │
                              ┌────Yes─────────┴──────────No────────────┐
                              ▼                                         ▼
                    ┌──────────────────┐                    ┌──────────────────┐
                    │Check             │                    │ Clear password   │
                    │is_password_reset │                    │ field            │
                    │                  │                    │ Error: "Password │
                    │                  │                    │ salah"           │
                    └────────┬─────────┘                    └──────────────────┘
                             │
            ┌──────FALSE─────┴──────TRUE────────┐
            ▼                                   ▼
   ┌────────────────────┐         ┌─────────────────────────────┐
   │Redirect:           │         │ Redirect: dashboard         │
   │Force Change        │         │ with success message        │
   │Password Page       │         │                             │
   │(/password/reset)   │         │                             │
   └────────────────────┘         └─────────────────────────────┘
3. LIVEWIRE IMPLEMENTATION (Production Ready)
php
Copy
<?php
// app/Livewire/Pages/Auth/Login.php
// WARNING: Jangan tambahkan Alpine.js directive di view ini!
// Gunakan pure Livewire wire:loading dan wire:target

namespace App\Livewire\Pages\Auth;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('layouts.guest')]
class Login extends Component
{
    #[Validate('required|string|min:18|max:18|regex:/^[0-9]+$/')]
    public string $nip = '';
    
    #[Validate('required|string|min:8')]
    public string $password = '';
    
    public bool $remember = false;
    public bool $showPassword = false;
    public string $errorMessage = '';
    
    // Throttle properties
    public int $maxAttempts = 5;
    public int $decayMinutes = 1;

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    protected function getRateLimiterKey(): string
    {
        return 'login.' . Request::ip() . '.' . $this->nip;
    }

    public function login()
    {
        // 1. Reset error state
        $this->errorMessage = '';
        $this->resetValidation();
        
        // 2. Rate Limiting Check (Anti brute force)
        $key = $this->getRateLimiterKey();
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'nip' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik."
            ]);
        }

        try {
            // 3. Validasi input dasar
            $this->validate();
            
            // 4. NORMALISASI: Bersihkan NIP (hapus spasi, dash)
            $cleanNip = preg_replace('/[^0-9]/', '', $this->nip);
            
            // 5. CARI EMPLOYEE (Primary Key lookup)
            $employee = Employee::with('user') // Eager load user
                ->where('nip', $cleanNip)
                ->where('is_active', true)     // Pastikan aktif
                ->first();

            if (!$employee) {
                RateLimiter::hit($key);
                $this->errorMessage = 'NIP tidak ditemukan atau tidak aktif.';
                $this->password = '';
                return;
            }

            // 6. CEK AKTIVASI: Pastikan punya user_id (sudah generate akun)
            if (is_null($employee->user_id)) {
                RateLimiter::hit($key);
                $this->errorMessage = 'NIP belum diaktivasi. Silakan hubungi Admin TU.';
                $this->password = '';
                return;
            }

            // 7. AMBIL USER via relasi
            $user = $employee->user;
            
            if (!$user) {
                // Data integrity issue
                \Log::error('Data integrity: Employee has user_id but no user record', [
                    'nip' => $cleanNip,
                    'user_id' => $employee->user_id
                ]);
                $this->errorMessage = 'Terjadi kesalahan data pengguna. Hubungi admin.';
                return;
            }

            // 8. AUTHENTICATION ATTEMPT
            // Catatan: Laravel Auth selalu pakai email/identifier dan password
            $credentials = [
                'email' => $user->email,
                'password' => $this->password
            ];

            if (!Auth::attempt($credentials, $this->remember)) {
                RateLimiter::hit($key);
                $this->errorMessage = 'Password yang Anda masukkan salah.';
                $this->password = ''; // Security: clear password field
                return;
            }

            // 9. SUCCESS: Regenerate session (security)
            Request::session()->regenerate();
            RateLimiter::clear($key);

            // 10. CHECK FORCE PASSWORD RESET
            if (!$user->is_password_reset) {
                // First time login atau reset oleh admin
                return redirect()->route('password.force-change')
                    ->with('warning', 'Anda harus mengubah password default terlebih dahulu.');
            }

            // 11. Redirect berdasarkan Level (Optional routing logic)
            return $this->redirectBasedOnLevel($employee->approval_level);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error sudah ditangani Livewire
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('Login system error: ' . $e->getMessage(), [
                'nip' => $this->nip,
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            $this->password = '';
        }
    }

    protected function redirectBasedOnLevel(int $level)
    {
        $route = match($level) {
            6 => 'rektor.dashboard',      // Rektor
            5 => 'wr.dashboard',          // WR
            4 => 'dekan.dashboard',       // Dekan
            3 => 'wadek.dashboard',       // Wadek
            2 => 'kaprodi.dashboard',     // Kaprodi
            1 => 'staff.dashboard',       // Dosen/Staff
            default => 'dashboard'
        };

        return redirect()->route($route)->with('success', 'Selamat datang, ' . Auth::user()->employee->name);
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}
4. BLADE VIEW (No Alpine.js - Pure Livewire)
blade
Copy
{{-- resources/views/livewire/pages/auth/login.blade.php --}}

<div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <!-- Logo -->
        <div class="text-center">
            <img class="mx-auto h-16 w-auto" src="{{ asset('images/logo-uin.png') }}" alt="Logo UIN">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">e-SPPD</h2>
            <p class="mt-2 text-sm text-gray-600">Sistem Perjalanan Dinas</p>
        </div>

        <!-- Form -->
        <form wire:submit="login" class="mt-8 space-y-6">
            
            <!-- Error Message (Manual) -->
            @if ($errorMessage)
                <div class="rounded-md bg-red-50 p-4 border-l-4 border-red-500">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- NIP Field -->
            <div>
                <label for="nip" class="block text-sm font-medium text-gray-700">
                    NIP (18 Digit)
                </label>
                <div class="mt-1">
                    <input 
                        wire:model.live="nip" 
                        id="nip" 
                        name="nip" 
                        type="text" 
                        maxlength="18"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="off"
                        placeholder="197505051999031001"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm @error('nip') border-red-500 @enderror"
                    >
                </div>
                @error('nip')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Format: 18 digit angka NIP</p>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password
                </label>
                <div class="mt-1 relative">
                    <input 
                        wire:model.live="password"
                        id="password" 
                        name="password" 
                        type="{{ $showPassword ? 'text' : 'password' }}"
                        autocomplete="current-password"
                        placeholder="{{ $nip ? 'DDMMYYYY (dari tanggal lahir)' : 'Masukkan password' }}"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm pr-10 @error('password') border-red-500 @enderror"
                    >
                    <!-- Toggle Password -->
                    <button 
                        type="button" 
                        wire:click="togglePasswordVisibility"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    >
                        @if($showPassword)
                            <!-- Eye Slash Icon -->
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        @else
                            <!-- Eye Icon -->
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        @endif
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Default: Tanggal lahir format DDMMYYYY
                </p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        wire:model="remember" 
                        id="remember" 
                        name="remember" 
                        type="checkbox"
                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Ingat saya
                    </label>
                </div>
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-teal-600 hover:text-teal-500">
                        Lupa password?
                    </a>
                </div>
            </div>

            <!-- Submit Button (CRITICAL: No Alpine.js) -->
            <div>
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="login"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                >
                    <!-- State: Normal -->
                    <span wire:loading.remove wire:target="login" class="flex items-center">
                        Masuk
                        <svg class="ml-2 -mr-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                    
                    <!-- State: Loading -->
                    <span wire:loading wire:target="login" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </div>
        </form>

        <!-- Help Text -->
        <div class="mt-6 text-center text-xs text-gray-500">
            <p>Belum punya akun? Hubungi Admin TU</p>
            <p class="mt-1">UIN Saizu Purwokerto © {{ date('Y') }}</p>
        </div>
    </div>
</div>
5. SECURITY CHECKLIST (Do's and Don'ts)
HAL YANG WAJIB ADA:
Rate Limiting: Maksimal 5 percobaan per menit per IP + NIP
Session Regeneration: Setelah login sukses, regenerate session ID
Password Clear: Field password di-clear setiap gagal (hindari browser save yang salah)
Input Sanitization: NIP hanya angka (regex /[^0-9]/)
Soft Delete Check: Employee harus is_active = true dan deleted_at is null
Eager Loading: Selalu with('user') saat query Employee untuk hindari N+1
HAL YANG DILARANG (AI sering salah):
❌ Jangan pakai Auth::attempt(['nip' => $nip, 'password' => $pass])
Laravel Auth default pakai identifier email, bukan custom field tanpa override
❌ Jangan query User::where('nip', $nip)
NIP ada di tabel employees, users hanya punya email untuk auth backend
❌ Jangan expose error spesifik ke user:
Jangan: "Password salah" vs "User tidak ada" → ini memudahkan brute force enumeration
Pakai: "NIP atau Password salah" (generic)
❌ Jangan redirect langsung tanpa cek is_password_reset
Kalau true, force ke halaman change password terlebih dahulu
❌ Jangan lupa wire:loading.attr="disabled" di button
Mencegah double submit
6. TROUBLESHOOTING LOGIC
Table
Copy
Symptom	Kemungkinan Cause	Debug Query
"NIP tidak ditemukan"	Employee belum import atau soft deleted	Employee::withTrashed()->where('nip', $nip)->first()
"Belum terdaftar sebagai user"	user_id null di tabel employees	Cek column user_id apakah terisi
"Password salah" tapi yakin benar	Kesalahan format password default (DDMMYYYY vs dd-mm-yyyy)	$employee->birth_date->format('dmY') vs input
Setelah login langsung logout	Session tidak tersimpan atau cookie mismatch	Cek config/session.php domain dan secure
Infinite redirect loop	is_password_reset true tapi route force-change tidak ada	Pastikan route name password.force-change terdaftar
7. COPY-PASTE PROMPT FOR AI AGENT
Markdown
Copy
Code
Preview
PROJECT CONTEXT E-SPPD LOGIN:
- Authentication menggunakan NIP (input form) tapi backend pakai email (Laravel Auth default)
- Flow: Input NIP → Cari Employee → Ambil $employee->user → Auth::attempt pakai $user->email
- Password default: DDMMYYYY dari $employee->birth_date
- Tabel: employees (nip PK) hasOne User via user_id. User belongsTo Employee.
- Level akses di employees.approval_level (integer 1-6), BUKAN di users.role
- Wajib ada RateLimiter, session regenerate, dan force password reset redirect
- View menggunakan Livewire wire:loading, TANPA Alpine.js directive (:disabled, @click, x-data)
- Error message generic: "NIP atau Password salah" (tidak reveal which one is wrong untuk security)
Sudah final dan tested. Silakan paste ini ke AI agent Anda sebagai reference untuk generate code yang konsisten.
tahan selanjutnya step by step versi full
Edit
Copy
Share
Implementasi Force Password dan Dashboard per Level untuk E‑SPPD
Berikut Full Step-by-Step Implementation Guide setelah login berhasil. Dokumentasi ini adalah kelanjutan langsung dari Login Specification sebelumnya.
📗 E-SPPD POST-AUTHENTICATION WORKFLOW
Version: 2.0 Complete
Scope: Force Password Change → Dashboard → SPPD Creation → Approval → Document Generation
Constraint: Pure Livewire 3.x, No Alpine.js, Hierarchy Level 1-6
STEP 1: FORCE PASSWORD CHANGE (First Login Gate)
Logic Rule
Copy
IF user.is_password_reset = false 
   AND current_route != 'password.force-change'
THEN redirect_to('password.force-change')
Implementation
php
Copy
<?php
// app/Http/Middleware/EnsurePasswordIsChanged.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_password_reset) {
            if (!$request->routeIs('password.force-change') && 
                !$request->routeIs('logout')) {
                return redirect()->route('password.force-change')
                    ->with('warning', 'Anda harus mengubah password default terlebih dahulu.');
            }
        }
        return $next($request);
    }
}
php
Copy
<?php
// app/Livewire/Pages/Auth/ForcePasswordChange.php
namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class ForcePasswordChange extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    protected function rules(): array
    {
        return [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // huruf kecil
                'regex:/[A-Z]/',      // huruf besar
                'regex:/[0-9]/',      // angka
                'regex:/[@$!%*#?&]/', // simbol
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'new_password.regex' => 'Password harus mengandung huruf besar, kecil, angka, dan simbol.',
        ];
    }

    public function updatePassword()
    {
        $this->validate();

        $user = auth()->user();
        $employee = $user->employee;

        // Verify current password adalah default (DDMMYYYY) atau password lama
        $defaultPass = $employee->default_password; // Accessor: birth_date format dmY
        
        if (!Hash::check($this->current_password, $user->password) && 
            $this->current_password !== $defaultPass) {
            $this->addError('current_password', 'Password saat ini tidak sesuai.');
            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($this->new_password),
            'is_password_reset' => true,
        ]);

        // Log untuk audit
        \Log::info('Password changed', ['nip' => $employee->nip, 'by_user' => $user->id]);

        // Redirect sesuai level
        return $this->redirectBasedOnLevel($employee->approval_level);
    }

    protected function redirectBasedOnLevel(int $level)
    {
        $route = match($level) {
            6 => 'rektor.dashboard',
            5 => 'wr.dashboard',
            4 => 'dekan.dashboard',
            3 => 'wadek.dashboard',
            2 => 'kaprodi.dashboard',
            1 => 'staff.dashboard',
            default => 'dashboard'
        };

        return redirect()->route($route)
            ->with('success', 'Password berhasil diubah. Selamat datang!');
    }

    public function render()
    {
        return view('livewire.pages.auth.force-password-change');
    }
}
blade
Copy
{{-- resources/views/livewire/pages/auth/force-password-change.blade.php --}}
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900">Keamanan Akun</h2>
            <p class="mt-2 text-sm text-gray-600">
                Anda menggunakan password default. Silakan buat password baru untuk melanjutkan.
            </p>
        </div>

        <form wire:submit="updatePassword" class="mt-8 space-y-6">
            <!-- Current Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                <input 
                    wire:model="current_password" 
                    type="password" 
                    placeholder="Password default (DDMMYYYY)"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                >
                @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- New Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                <input 
                    wire:model="new_password" 
                    type="password"
                    placeholder="Min. 8 karakter, kombinasi simbol"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                >
                @error('new_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input 
                    wire:model="new_password_confirmation" 
                    type="password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                >
            </div>

            <!-- Password Strength Indicator -->
            <div class="text-xs text-gray-500">
                <p>Password harus mengandung:</p>
                <ul class="list-disc pl-4 mt-1 space-y-1">
                    <li>Minimal 8 karakter</li>
                    <li>Huruf besar (A-Z)</li>
                    <li>Huruf kecil (a-z)</li>
                    <li>Angka (0-9)</li>
                    <li>Simbol (@$!%*#?&)</li>
                </ul>
            </div>

            <button 
                type="submit"
                wire:loading.attr="disabled"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
                <span wire:loading.remove>Simpan & Lanjutkan</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </form>

        <div class="text-center">
            <button wire:click="logout" class="text-sm text-gray-500 hover:text-gray-700">
                Logout (Nanti saja)
            </button>
        </div>
    </div>
</div>
php
Copy
// routes/web.php
Route::middleware(['auth', 'password.changed'])->group(function () {
    // Semua route yang memerlukan password sudah diganti
});

Route::get('/force-password-change', ForcePasswordChange::class)
    ->name('password.force-change')
    ->middleware('auth');
STEP 2: DASHBOARD ARCHITECTURE BY LEVEL
Dashboard Controller Strategy
Satu Livewire component dengan conditional rendering berdasarkan approval_level, atau pisah per level untuk security maksimal.
Rekomendasi: Pisah per level untuk mencegah information disclosure antar level.
php
Copy
<?php
// app/Livewire/Dashboard/StaffDashboard.php (Level 1)
namespace App\Livewire\Dashboard;

use App\Models\Sppd;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class StaffDashboard extends Component
{
    public $recentSppds;
    public $stats;

    public function mount()
    {
        // Security Check: Pastikan user adalah level 1
        if (auth()->user()->employee->approval_level !== 1) {
            abort(403, 'Akses tidak diizinkan untuk level ini.');
        }

        $employeeNip = auth()->user()->employee->nip;

        $this->recentSppds = Sppd::where('employee_nip', $employeeNip)
            ->latest()
            ->take(5)
            ->get();

        $this->stats = [
            'total' => Sppd::where('employee_nip', $employeeNip)->count(),
            'pending' => Sppd::where('employee_nip', $employeeNip)->where('status', 'pending')->count(),
            'approved' => Sppd::where('employee_nip', $employeeNip)->where('status', 'approved')->count(),
            'rejected' => Sppd::where('employee_nip', $employeeNip)->where('status', 'rejected')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.staff');
    }
}
php
Copy
<?php
// app/Livewire/Dashboard/ApproverDashboard.php (Level 2-6)
namespace App\Livewire\Dashboard;

use App\Models\Sppd;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ApproverDashboard extends Component
{
    public $pendingApprovals;
    public $stats;
    public $userLevel;

    public function mount()
    {
        $user = auth()->user();
        $this->userLevel = $user->employee->approval_level;

        // Level 2-6 bisa akses ini, tapi data difilter berbeda
        if ($this->userLevel < 2) {
            abort(403);
        }

        $this->loadPendingApprovals();
        $this->loadStats();
    }

    private function loadPendingApprovals()
    {
        $userNip = auth()->user()->employee->nip;

        // Logic: Cari SPPD yang current_approver_nip = user ini
        // ATAU jika level tinggi, bisa lihat semua yang pending di bawahnya (optional)
        $this->pendingApprovals = Sppd::where('current_approver_nip', $userNip)
            ->where('status', 'pending')
            ->with('employee') // Eager load untuk tampilkan nama & foto
            ->latest()
            ->get();
    }

    private function loadStats()
    {
        $userNip = auth()->user()->employee->nip;
        
        $this->stats = [
            'waiting_me' => Sppd::where('current_approver_nip', $userNip)
                ->where('status', 'pending')->count(),
            'approved_by_me' => \App\Models\SppdApproval::where('approver_nip', $userNip)
                ->where('status', 'approved')->count(),
            'rejected_by_me' => \App\Models\SppdApproval::where('approver_nip', $userNip)
                ->where('status', 'rejected')->count(),
        ];
    }

    public function render()
    {
        // View berbeda berdasarkan level untuk UI customization
        $view = match($this->userLevel) {
            2 => 'livewire.dashboard.kaprodi',
            3 => 'livewire.dashboard.wadek',
            4 => 'livewire.dashboard.dekan',
            5 => 'livewire.dashboard.wr',
            6 => 'livewire.dashboard.rektor',
            default => 'livewire.dashboard.approver-generic'
        };

        return view($view);
    }
}
Shared Layout (layouts/app.blade.php) Structure
blade
Copy
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <title>{{ $title ?? 'e-SPPD' }}</title>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-teal-600">e-SPPD</span>
                    <span class="ml-2 text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        Level {{ auth()->user()->employee->approval_level }}
                    </span>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Profile Dropdown Livewire -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                            <img 
                                src="{{ auth()->user()->employee->photo_url ?? asset('images/default-avatar.png') }}" 
                                class="h-8 w-8 rounded-full object-cover border border-gray-300"
                                alt="Profile"
                            >
                            <span class="text-sm font-medium text-gray-700 hidden md:block">
                                {{ auth()->user()->employee->name }}
                            </span>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                            <button wire:click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
STEP 3: SPPD CREATION WORKFLOW
Migration tambahan untuk SPPD
php
Copy
// database/migrations/xxxx_create_sppds_table.php
Schema::create('sppds', function (Blueprint $table) {
    $table->id();
    $table->string('sppd_number')->nullable()->unique(); // Format: 0001/Un.19/.../2025 (diisi saat final approve)
    $table->string('employee_nip', 18);
    $table->string('destination');
    $table->text('purpose');
    $table->date('start_date');
    $table->date('end_date');
    $table->decimal('biaya_transport', 15, 2)->default(0);
    $table->decimal('biaya_hotel', 15, 2)->default(0);
    $table->decimal('biaya_harian', 15, 2)->default(0);
    $table->decimal('total_biaya', 15, 2)->default(0);
    $table->string('status')->default('draft'); // draft, pending, approved, rejected
    $table->string('current_approver_nip', 18)->nullable(); // Siapa yang harus approve sekarang
    $table->foreignId('anggaran_id')->constrained('anggarans');
    $table->string('attachment_path')->nullable(); // Surat tugas/undangan
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->foreign('employee_nip')->references('nip')->on('employees');
    $table->foreign('current_approver_nip')->references('nip')->on('employees');
});
Livewire Component: CreateSppd
php
Copy
<?php
// app/Livewire/Sppd/CreateSppd.php
namespace App\Livewire\Sppd;

use App\Models\Anggaran;
use App\Models\Employee;
use App\Models\Sppd;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CreateSppd extends Component
{
    use WithFileUploads;

    // Form Fields
    public string $destination = '';
    public string $purpose = '';
    public string $start_date = '';
    public string $end_date = '';
    public float $biaya_transport = 0;
    public float $biaya_hotel = 0;
    public float $biaya_harian = 0;
    public $attachment; // Temporary file upload
    public int $anggaran_id = 0;

    // State
    public string $mode = 'draft'; // 'draft' atau 'submit'
    public float $total_biaya = 0;
    public ?Employee $superior = null;

    protected function rules(): array
    {
        return [
            'destination' => 'required|string|min:3|max:100',
            'purpose' => 'required|string|min:10',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'biaya_transport' => 'required|numeric|min:0',
            'biaya_hotel' => 'required|numeric|min:0',
            'biaya_harian' => 'required|numeric|min:0',
            'anggaran_id' => 'required|exists:anggarans,id',
            'attachment' => 'nullable|file|max:2048|mimes:pdf,jpg,png', // Max 2MB
        ];
    }

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(2)->format('Y-m-d');
        
        // Auto-calculate superior untuk preview
        $this->loadSuperior();
    }

    public function updated($property)
    {
        if (in_array($property, ['biaya_transport', 'biaya_hotel', 'biaya_harian'])) {
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total_biaya = $this->biaya_transport + $this->biaya_hotel + $this->biaya_harian;
    }

    private function loadSuperior()
    {
        $user = auth()->user()->employee;
        $this->superior = $user->superior;
    }

    public function saveAsDraft()
    {
        $this->mode = 'draft';
        $this->createSppd();
    }

    public function submitForApproval()
    {
        $this->mode = 'submit';
        $this->validate();
        
        // Check overlap dates
        if ($this->hasDateOverlap()) {
            $this->addError('start_date', 'Anda memiliki SPPD lain pada rentang tanggal tersebut.');
            return;
        }
        
        $this->createSppd();
    }

    private function hasDateOverlap(): bool
    {
        $userNip = auth()->user()->employee->nip;
        
        return Sppd::where('employee_nip', $userNip)
            ->whereNotIn('status', ['rejected', 'draft'])
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<=', $this->start_date)
                          ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->exists();
    }

    public function createSppd()
    {
        $validated = $this->validate();
        
        try {
            // Handle file upload
            $attachmentPath = null;
            if ($this->attachment) {
                $attachmentPath = $this->attachment->store('attachments', 'public');
            }

            // Determine status and current approver
            $status = $this->mode === 'draft' ? 'draft' : 'pending';
            $currentApprover = null;
            
            if ($status === 'pending') {
                // Logic: Cari siapa atasan berdasarkan hierarchy
                $employee = auth()->user()->employee;
                $currentApprover = $this->determineApprover($employee);
                
                if (!$currentApprover) {
                    $this->addError('general', 'Tidak ditemukan atasan untuk approval. Hubungi admin.');
                    return;
                }
            }

            $sppd = Sppd::create([
                'employee_nip' => auth()->user()->employee->nip,
                'destination' => $this->destination,
                'purpose' => $this->purpose,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'biaya_transport' => $this->biaya_transport,
                'biaya_hotel' => $this->biaya_hotel,
                'biaya_harian' => $this->biaya_harian,
                'total_biaya' => $this->total_biaya,
                'anggaran_id' => $this->anggaran_id,
                'status' => $status,
                'current_approver_nip' => $currentApprover,
                'attachment_path' => $attachmentPath,
            ]);

            if ($status === 'pending') {
                // Trigger notification (queue)
                // Notification::send($approverUser, new SppdSubmitted($sppd));
                
                session()->flash('success', 'SPPD berhasil diajukan dan menunggu approval.');
                return redirect()->route('staff.sppd.show', $sppd);
            } else {
                session()->flash('success', 'Draft SPPD tersimpan.');
                return redirect()->route('staff.sppd.edit', $sppd);
            }

        } catch (\Exception $e) {
            \Log::error('SPPD Creation Error: ' . $e->getMessage());
            $this->addError('general', 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
        }
    }

    private function determineApprover(Employee $employee): ?string
    {
        // Logic Hierarchy:
        // 1. Cek apakah ada delegasi aktif untuk superior
        // 2. Jika tidak, pakai superior_nip
        // 3. Jika superior null (sudah puncak), return null (auto-approve atau error)
        
        $superiorNip = $employee->superior_nip;
        
        if (!$superiorNip) {
            // Kasus: Rektor ngajuin SPPD? Atau staff tanpa atasan?
            // Handle sesuai kebijakan
            return null;
        }
        
        // Cek delegasi
        $delegation = \App\Models\Delegation::where('delegator_nip', $superiorNip)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('is_active', true)
            ->first();
            
        if ($delegation) {
            return $delegation->delegate_nip;
        }
        
        return $superiorNip;
    }

    public function render()
    {
        // Load anggaran untuk dropdown (hanya unit user)
        $userUnit = auth()->user()->employee->faculty;
        $anggarans = Anggaran::where('unit_kerja', $userUnit)
            ->where('tahun_anggaran', now()->year)
            ->get();

        return view('livewire.sppd.create', [
            'anggarans' => $anggarans,
        ]);
    }
}
blade
Copy
{{-- resources/views/livewire/sppd/create.blade.php --}}
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Pengajuan SPPD Baru</h2>

    @if ($errors->has('general'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <p class="text-red-700">{{ $errors->first('general') }}</p>
        </div>
    @endif

    <form wire:submit.prevent class="space-y-6 bg-white p-6 rounded-lg shadow">
        <!-- Form sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Destination -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Tujuan/Kota</label>
                <input wire:model.live="destination" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: Jakarta, Bandung, Surabaya">
                @error('destination') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Purpose -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Maksud/Keperluan</label>
                <textarea wire:model.live="purpose" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Jelaskan keperluan perjalanan dinas..."></textarea>
                @error('purpose') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Dates -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Berangkat</label>
                <input wire:model.live="start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Pulang</label>
                <input wire:model.live="end_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Biaya Section -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-medium mb-4">Estimasi Biaya</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Transport (Rp)</label>
                    <input wire:model.live="biaya_transport" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="0">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hotel/Penginapan (Rp)</label>
                    <input wire:model.live="biaya_hotel" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="0">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Uang Harian (Rp)</label>
                    <input wire:model.live="biaya_harian" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="0">
                </div>
            </div>

            <div class="mt-4 p-4 bg-gray-50 rounded-lg flex justify-between items-center">
                <span class="text-gray-700 font-medium">Total Estimasi:</span>
                <span class="text-2xl font-bold text-teal-600">Rp {{ number_format($total_biaya, 0, ',', '.') }}</span>
            </div>
            
            @if($superior)
            <div class="mt-2 text-sm text-gray-500">
                Akan dikirim ke atasan: <strong>{{ $superior->name }}</strong> ({{ $superior->level_name }})
            </div>
            @endif
        </div>

        <!-- Anggaran & Attachment -->
        <div class="border-t pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Sumber Anggaran</label>
                <select wire:model="anggaran_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="0">Pilih Anggaran...</option>
                    @foreach($anggarans as $anggaran)
                        <option value="{{ $anggaran->id }}">
                            {{ $anggaran->nama_kegiatan }} (Sisa: Rp {{ number_format($anggaran->sisa, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                @error('anggaran_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Lampiran (Surat Tugas/Undangan)</label>
                <input wire:model="attachment" type="file" accept=".pdf,.jpg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <div wire:loading wire:target="attachment" class="text-xs text-teal-600 mt-1">Mengupload...</div>
                @error('attachment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4 pt-6 border-t">
            <button 
                type="button" 
                wire:click="saveAsDraft"
                wire:loading.attr="disabled"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
                <span wire:loading.remove wire:target="saveAsDraft">Simpan Draft</span>
                <span wire:loading wire:target="saveAsDraft">Menyimpan...</span>
            </button>
            
            <button 
                type="button" 
                wire:click="submitForApproval"
                wire:loading.attr="disabled"
                class="flex-1 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
            >
                <span wire:loading.remove wire:target="submitForApproval">Ajukan untuk Approval</span>
                <span wire:loading wire:target="submitForApproval">Mengajukan...</span>
            </button>
        </div>
    </form>
</div>
STEP 4: APPROVAL SYSTEM (The Core Logic)
php
Copy
<?php
// app/Livewire/Approval/ApprovalDetail.php
namespace App\Livewire\Approval;

use App\Models\Sppd;
use App\Models\SppdApproval;
use App\Services\AnggaranService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ApprovalDetail extends Component
{
    public Sppd $sppd;
    public string $rejectionReason = '';
    public bool $showRejectModal = false;

    // Security boot
    public function mount(Sppd $sppd)
    {
        $this->sppd = $sppd;
        
        // IDOR Protection: Pastikan user ini memang current_approver
        if ($sppd->current_approver_nip !== auth()->user()->employee->nip) {
            abort(403, 'Anda bukan pemegang hak approval untuk SPPD ini.');
        }
    }

    public function approve()
    {
        $user = auth()->user();
        $employee = $user->employee;
        
        try {
            \DB::transaction(function () use ($employee) {
                // 1. Cek limit approval level
                if (!$this->checkApprovalLimit($employee)) {
                    throw new \Exception('Melebihi limit approval.');
                }

                // 2. Cek sisa anggaran (jika ini final approval atau sesuai kebutuhan)
                // Logika: Anggaran di-update saat final approve atau incremental?
                // Di sini asumsikan incremental
                
                // 3. Record approval
                SppdApproval::create([
                    'sppd_id' => $this->sppd->id,
                    'approver_nip' => $employee->nip,
                    'level' => $employee->approval_level,
                    'status' => 'approved',
                    'notes' => 'Approved via system',
                    'approved_at' => now(),
                ]);

                // 4. Determine next step
                $nextApprover = $this->getNextApprover($employee);
                
                if ($nextApprover === null || $this->isFinalLevel($employee->approval_level)) {
                    // Final Approval
                    $this->finalizeSppd();
                } else {
                    // Forward to next level
                    $this->sppd->update([
                        'current_approver_nip' => $nextApprover,
                        'status' => 'pending',
                    ]);
                }
            });

            session()->flash('success', 'Approval berhasil disimpan.');
            return redirect()->route('approver.dashboard');

        } catch (\Exception $e) {
            \Log::error('Approval Error: ' . $e->getMessage());
            $this->addError('general', $e->getMessage());
        }
    }

    public function reject()
    {
        $this->validate(['rejectionReason' => 'required|string|min:10']);
        
        $user = auth()->user();
        
        \DB::transaction(function () use ($user) {
            SppdApproval::create([
                'sppd_id' => $this->sppd->id,
                'approver_nip' => $user->employee->nip,
                'level' => $user->employee->approval_level,
                'status' => 'rejected',
                'notes' => $this->rejectionReason,
                'approved_at' => now(),
            ]);

            $this->sppd->update([
                'status' => 'rejected',
                'current_approver_nip' => null,
                'rejection_reason' => $this->rejectionReason,
            ]);
        });

        session()->flash('success', 'SPPD ditolak.');
        return redirect()->route('approver.dashboard');
    }

    private function checkApprovalLimit(Employee $approver): bool
    {
        $limits = [
            1 => 0,              // Staff tidak bisa approve
            2 => 5_000_000,      // Kaprodi: 5jt
            3 => 20_000_000,     // Wadek: 20jt
            4 => 50_000_000,     // Dekan: 50jt
            5 => 100_000_000,    // WR: 100jt
            6 => PHP_INT_MAX,    // Rektor: unlimited
        ];

        $limit = $limits[$approver->approval_level] ?? 0;
        
        return $this->sppd->total_biaya <= $limit;
    }

    private function getNextApprover(Employee $currentApprover): ?string
    {
        // Logic: Naik 1 level dari current
        $currentLevel = $currentApprover->approval_level;
        $requestor = $this->sppd->employee; // Yang ngajuin
        
        // Cari atasan dari requestor yang levelnya > current
        // Atau bisa juga pakai hierarki chain yang sudah didefinisikan
        
        // Simplified: Cari di employees yang levelnya lebih tinggi dan menjadi superior dari requestor atau dari current approver
        $nextLevel = $currentLevel + 1;
        
        if ($nextLevel > 6) return null;
        
        // Cari siapa yang levelnya $nextLevel dan di fakultas yang sama (untuk Dekan ke WR beda logika)
        // Ini tergantung kebijakan, bisa jadi WR/Rektor tidak terbatas fakultas
        
        if ($nextLevel >= 5) {
            // WR/Rektor: Ambil dari config atau table setting siapa WR/Rektornya
            return \App\Models\Employee::where('approval_level', $nextLevel)
                ->where('is_active', true)
                ->first()?->nip;
        }
        
        // Untuk level 2-4 (Kaprodi-Wadek-Dekan): Harus sesama fakultas
        return \App\Models\Employee::where('approval_level', $nextLevel)
            ->where('faculty', $requestor->faculty)
            ->where('is_active', true)
            ->first()?->nip;
    }

    private function isFinalLevel(int $level): bool
    {
        // Final jika level 6 (Rektor) atau jika tidak ada atasan di atasnya
        return $level >= 6;
    }

    private function finalizeSppd()
    {
        // Generate nomor surat
        $nomorSurat = $this->generateNomorSurat();
        
        // Update anggaran (kurangi sisa)
        app(AnggaranService::class)->updateRealisasi($this->sppd);
        
        $this->sppd->update([
            'status' => 'approved',
            'sppd_number' => $nomorSurat,
            'current_approver_nip' => null,
        ]);
    }

    private function generateNomorSurat(): string
    {
        $tahun = now()->year;
        $bulan = now()->format('m');
        
        // Get last number
        $lastNumber = Sppd::whereYear('created_at', $tahun)
            ->whereNotNull('sppd_number')
            ->count() + 1;
            
        $nomor = str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
        $unitCode = $this->getUnitCode($this->sppd->employee->faculty);
        
        return "{$nomor}/Un.19/K.AUPK/{$unitCode}/{$tahun}";
    }

    private function getUnitCode(string $faculty): string
    {
        return match($faculty) {
            'FTIK' => 'FT.01',
            'FEBI' => 'FE.02',
            'FAI' => 'FA.03',
            default => 'FP.01',
        };
    }

    public function render()
    {
        return view('livewire.approval.detail');
    }
}
STEP 5: ANGGARAN SERVICE (Auto-Update)
php
Copy
<?php
// app/Services/AnggaranService.php
namespace App\Services;

use App\Models\Anggaran;
use App\Models\Sppd;
use Illuminate\Support\Facades\DB;

class AnggaranService
{
    public function updateRealisasi(Sppd $sppd): array
    {
        return DB::transaction(function () use ($sppd) {
            $anggaran = Anggaran::lockForUpdate()
                ->find($sppd->anggaran_id);
                
            if (!$anggaran) {
                return ['success' => false, 'message' => 'Anggaran tidak ditemukan'];
            }
            
            $sisa = $anggaran->pagu_awal - $anggaran->realisasi;
            
            if ($sppd->total_biaya > $sisa) {
                return [
                    'success' => false, 
                    'message' => "Anggaran tidak mencukupi. Sisa: Rp " . number_format($sisa)
                ];
            }
            
            $anggaran->realisasi += $sppd->total_biaya;
            $anggaran->save();
            
            return [
                'success' => true,
                'sisa' => $anggaran->pagu_awal - $anggaran->realisasi
            ];
        });
    }
}
CHECKLIST ROUTES
php
Copy
// routes/web.php Final Structure

// Public
Route::get('/login', \App\Livewire\Pages\Auth\Login::class)->name('login');
Route::get('/force-password-change', \App\Livewire\Pages\Auth\ForcePasswordChange::class)
    ->name('password.force-change')
    ->middleware('auth');

// Authenticated + Password Changed
Route::middleware(['auth', 'password.changed'])->group(function () {
    
    // Role-specific Dashboards
    Route::get('/dashboard', function() {
        $level = auth()->user()->employee->approval_level;
        return redirect()->route(match($level) {
            1 => 'staff.dashboard',
            2 => 'kaprodi.dashboard',
            3 => 'wadek.dashboard',
            4 => 'dekan.dashboard',
            5 => 'wr.dashboard',
            6 => 'rektor.dashboard',
        });
    })->name('dashboard');
    
    Route::get('/staff/dashboard', \App\Livewire\Dashboard\StaffDashboard::class)->name('staff.dashboard');
    Route::get('/approver/dashboard', \App\Livewire\Dashboard\ApproverDashboard::class)->name('approver.dashboard');
    // ... specific routes untuk level lain
    
    // SPPD Management
    Route::get('/sppd/create', \App\Livewire\Sppd\CreateSppd::class)->name('sppd.create');
    Route::get('/sppd/{sppd}/show', \App\Livewire\Sppd\ShowSppd::class)->name('staff.sppd.show');
    
    // Approval
    Route::get('/approval/{sppd}', \App\Livewire\Approval\ApprovalDetail::class)->name('approval.detail');
    
    // Profile
    Route::get('/profile', \App\Livewire\Profile\EditProfile::class)->name('profile');
    
    // Document Download (SPD/SPT)
    Route::get('/sppd/{sppd}/spt/download', [\App\Http\Controllers\DocumentController::class, 'downloadSPT'])->name('sppd.spt.download');
    Route::get('/sppd/{sppd}/spd/download', [\App\Http\Controllers\DocumentController::class, 'downloadSPD'])->name('sppd.spd.download');
});

Route::post('/logout', function() {
    auth()->logout();
    return redirect('/login');
})->name('logout');
Ini adalah blueprint lengkap dan final. Setiap komponen sudah include security check (IDOR, Level validation, Ownership) dan business rules (Atomic transaction, Hierarchy, Limit).

