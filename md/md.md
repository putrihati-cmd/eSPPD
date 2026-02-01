Berikut satu block Markdown siap copy-paste:
Markdown
Copy
Code
Preview
# E-SPPD COMPLETE DOCUMENTATION
## Version 1.0 - Production Ready

### 1. DATABASE ARCHITECTURE

```sql
-- employees (Master Data)
CREATE TABLE employees (
    nip VARCHAR(18) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    faculty VARCHAR(50) NOT NULL,
    approval_level TINYINT DEFAULT 1,
    superior_nip VARCHAR(18) NULL,
    is_active BOOLEAN DEFAULT true,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (superior_nip) REFERENCES employees(nip)
);

-- users (Auth Table)
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(18) UNIQUE NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_password_reset BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- sppds
CREATE TABLE sppds (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sppd_number VARCHAR(50) UNIQUE NULL,
    employee_nip VARCHAR(18) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    purpose TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    biaya_transport DECIMAL(15,2) DEFAULT 0,
    biaya_hotel DECIMAL(15,2) DEFAULT 0,
    biaya_harian DECIMAL(15,2) DEFAULT 0,
    total_biaya DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'draft',
    current_approver_nip VARCHAR(18) NULL,
    anggaran_id BIGINT UNSIGNED NOT NULL,
    attachment_path VARCHAR(255) NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (employee_nip) REFERENCES employees(nip),
    FOREIGN KEY (current_approver_nip) REFERENCES employees(nip)
);
2. MODEL RELATIONSHIPS
php
Copy
// app/Models/Employee.php
class Employee extends Model {
    use SoftDeletes;
    
    protected $primaryKey = 'nip';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = ['nip', 'user_id', 'name', 'birth_date', 'faculty', 'approval_level', 'superior_nip', 'is_active'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function superior() {
        return $this->belongsTo(Employee::class, 'superior_nip', 'nip');
    }
    
    public function getDefaultPasswordAttribute() {
        return $this->birth_date->format('dmY');
    }
    
    public function getLevelNameAttribute() {
        return match($this->approval_level) {
            1 => 'Dosen/Staff',
            2 => 'Kaprodi',
            3 => 'Wadek',
            4 => 'Dekan',
            5 => 'WR',
            6 => 'Rektor',
            default => 'Unknown'
        };
    }
}

// app/Models/User.php
class User extends Authenticatable {
    protected $fillable = ['nip', 'email', 'password', 'is_password_reset'];
    
    public function employee() {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }
}
3. LOGIN FLOW (LIVEWIRE COMPONENT)
php
Copy
<?php
// app/Livewire/Pages/Auth/Login.php
namespace App\Livewire\Pages\Auth;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('layouts.guest')]
class Login extends Component {
    #[Validate('required|string|min:18|max:18')]
    public string $nip = '';
    
    #[Validate('required|string|min:8')]
    public string $password = '';
    
    public bool $remember = false;
    public bool $showPassword = false;
    public string $errorMessage = '';

    public function togglePasswordVisibility(): void {
        $this->showPassword = !$this->showPassword;
    }

    public function login() {
        $this->errorMessage = '';
        $this->resetValidation();
        
        $key = 'login.' . request()->ip() . '.' . $this->nip;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->errorMessage = 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.';
            return;
        }

        try {
            $this->validate();
            $cleanNip = preg_replace('/[^0-9]/', '', $this->nip);
            
            $employee = Employee::with('user')
                ->where('nip', $cleanNip)
                ->where('is_active', true)
                ->first();

            if (!$employee) {
                RateLimiter::hit($key);
                $this->errorMessage = 'NIP tidak ditemukan atau tidak aktif.';
                $this->password = '';
                return;
            }

            if (is_null($employee->user_id)) {
                RateLimiter::hit($key);
                $this->errorMessage = 'NIP belum diaktivasi. Hubungi Admin TU.';
                $this->password = '';
                return;
            }

            $user = $employee->user;
            
            if (!$user) {
                $this->errorMessage = 'Terjadi kesalahan data pengguna.';
                return;
            }

            if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
                RateLimiter::hit($key);
                $this->errorMessage = 'Password salah.';
                $this->password = '';
                return;
            }

            request()->session()->regenerate();
            RateLimiter::clear($key);

            if (!$user->is_password_reset) {
                return redirect()->route('password.force-change');
            }

            return $this->redirectBasedOnLevel($employee->approval_level);

        } catch (\Throwable $e) {
            \Log::error('Login error: ' . $e->getMessage());
            $this->errorMessage = 'Terjadi kesalahan sistem.';
            $this->password = '';
        }
    }

    protected function redirectBasedOnLevel(int $level) {
        $route = match($level) {
            6 => 'rektor.dashboard',
            5 => 'wr.dashboard',
            4 => 'dekan.dashboard',
            3 => 'wadek.dashboard',
            2 => 'kaprodi.dashboard',
            1 => 'staff.dashboard',
            default => 'dashboard'
        };
        return redirect()->route($route)->with('success', 'Selamat datang!');
    }

    public function render() {
        return view('livewire.pages.auth.login');
    }
}
?>
4. LOGIN BLADE VIEW (PURE LIVEWIRE - NO ALPINE)
blade
Copy
{{-- resources/views/livewire/pages/auth/login.blade.php --}}
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">e-SPPD</h2>
            <p class="mt-2 text-sm text-gray-600">Sistem Perjalanan Dinas</p>
        </div>

        @if ($errorMessage)
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit="login" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">NIP (18 Digit)</label>
                <input wire:model.live="nip" type="text" maxlength="18" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" 
                       placeholder="197505051999031001">
                @error('nip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative mt-1">
                    <input wire:model.live="password" 
                           type="{{ $showPassword ? 'text' : 'password' }}" 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 pr-10">
                    <button type="button" wire:click="togglePasswordVisibility" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        @if($showPassword) 👁️ @else 👁️‍🗨️ @endif
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">Default: DDMMYYYY dari tanggal lahir</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                </label>
                <a href="#" class="text-sm text-teal-600 hover:text-teal-500">Lupa password?</a>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="login"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50">
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login">Memproses...</span>
            </button>
        </form>
    </div>
</div>
5. APPROVAL CONTROLLER LOGIC
php
Copy
<?php
// app/Livewire/Approval/ApprovalDetail.php
namespace App\Livewire\Approval;

use App\Models\Sppd;
use App\Models\SppdApproval;
use Livewire\Component;

class ApprovalDetail extends Component {
    public Sppd $sppd;

    public function mount(Sppd $sppd) {
        if ($sppd->current_approver_nip !== auth()->user()->employee->nip) {
            abort(403, 'Anda bukan pemegang hak approval.');
        }
        $this->sppd = $sppd;
    }

    public function approve() {
        $user = auth()->user();
        $employee = $user->employee;
        
        // Check limit
        $limits = [1 => 0, 2 => 5000000, 3 => 20000000, 4 => 50000000, 5 => 100000000, 6 => PHP_INT_MAX];
        if ($this->sppd->total_biaya > ($limits[$employee->approval_level] ?? 0)) {
            $this->addError('general', 'Melebihi limit approval Anda.');
            return;
        }

        \DB::transaction(function () use ($employee) {
            SppdApproval::create([
                'sppd_id' => $this->sppd->id,
                'approver_nip' => $employee->nip,
                'level' => $employee->approval_level,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            if ($employee->approval_level >= 6) {
                $this->finalizeSppd();
            } else {
                $nextApprover = $this->getNextApprover($employee);
                $this->sppd->update([
                    'current_approver_nip' => $nextApprover,
                    'status' => $nextApprover ? 'pending' : 'approved'
                ]);
                
                if (!$nextApprover) $this->finalizeSppd();
            }
        });

        return redirect()->route('approver.dashboard')->with('success', 'Approval disimpan.');
    }

    private function finalizeSppd() {
        $tahun = now()->year;
        $count = Sppd::whereYear('created_at', $tahun)->whereNotNull('sppd_number')->count() + 1;
        $nomor = str_pad($count, 4, '0', STR_PAD_LEFT);
        
        $this->sppd->update([
            'status' => 'approved',
            'sppd_number' => "{$nomor}/Un.19/K.AUPK/FT.01/{$tahun}",
            'current_approver_nip' => null,
        ]);

        $anggaran = \App\Models\Anggaran::lockForUpdate()->find($this->sppd->anggaran_id);
        $anggaran->realisasi += $this->sppd->total_biaya;
        $anggaran->save();
    }

    private function getNextApprover($current): ?string {
        $nextLevel = $current->approval_level + 1;
        if ($nextLevel > 6) return null;
        
        $requestor = $this->sppd->employee;
        
        if ($nextLevel >= 5) {
            return \App\Models\Employee::where('approval_level', $nextLevel)
                ->where('is_active', true)->first()?->nip;
        }
        
        return \App\Models\Employee::where('approval_level', $nextLevel)
            ->where('faculty', $requestor->faculty)
            ->where('is_active', true)->first()?->nip;
    }
}
?>
6. ROUTES CONFIGURATION
php
Copy
<?php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::get('/login', \App\Livewire\Pages\Auth\Login::class)->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', \App\Livewire\Pages\Auth\ForcePasswordChange::class)
        ->name('password.force-change');
    
    Route::middleware(['password.changed'])->group(function () {
        Route::get('/dashboard', function() {
            $level = auth()->user()->employee->approval_level;
            return redirect()->route(match($level) {
                1 => 'staff.dashboard', 2 => 'kaprodi.dashboard', 3 => 'wadek.dashboard',
                4 => 'dekan.dashboard', 5 => 'wr.dashboard', 6 => 'rektor.dashboard',
                default => 'dashboard'
            });
        })->name('dashboard');
        
        Route::get('/sppd/create', \App\Livewire\Sppd\CreateSppd::class)->name('sppd.create');
        Route::get('/approval/{sppd}', \App\Livewire\Approval\ApprovalDetail::class)->name('approval.detail');
    });
});

Route::post('/logout', function() {
    auth()->logout();
    return redirect('/login');
})->name('logout');
?>
7. HIERARCHY LEVEL REFERENCE
Table
Copy
Level	Jabatan	Limit	Access
1	Dosen/Staff	Rp 0	Submit SPPD only
2	Kaprodi	Rp 5jt	Approve bawahan
3	Wadek	Rp 20jt	Approve + Monitor
4	Dekan	Rp 50jt	Approve + Report
5	WR	Rp 100jt	Multi-fakultas
6	Rektor	Unlimited	Final + Generate Nomor Surat
8. CRITICAL RULES
Auth Flow: NIP (input) → Find Employee → Get User Email → Auth::attempt via Email
Password Default: Format DDMMYYYY dari employees.birth_date
No Alpine.js: Use wire:loading + wire:target only
Security: Rate limit 5x per menit, session regenerate after login
Database: All financial transactions use lockForUpdate()
Audit: Soft delete only (no hard delete for SPPD)
Constraint: Check date overlap before submit SPPD