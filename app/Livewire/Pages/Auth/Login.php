<?php

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
use Illuminate\Support\Facades\Log;

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
        $this->errorMessage = '';
        $this->resetValidation();
        $key = $this->getRateLimiterKey();
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'nip' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik."
            ]);
        }
        try {
            $this->validate();
            $cleanNip = preg_replace('/[^0-9]/', '', $this->nip);
            $employee = Employee::with('user')->where('nip', $cleanNip)->first();
            if (!$employee) {
                RateLimiter::hit($key);
                $this->errorMessage = 'NIP atau Password salah';
                return;
            }
            if (is_null($employee->user_id)) {
                RateLimiter::hit($key);
                $this->errorMessage = 'NIP belum aktivasi akun. Hubungi Admin.';
                return;
            }
            $user = $employee->user;
            if (!$user) {
                $this->errorMessage = 'Data user tidak ditemukan.';
                return;
            }
            $credentials = [
                'email' => $user->email,
                'password' => $this->password
            ];
            if (!Auth::attempt($credentials, $this->remember)) {
                RateLimiter::hit($key);
                $this->errorMessage = 'NIP atau Password salah';
                $this->password = '';
                return;
            }
            Request::session()->regenerate();
            RateLimiter::clear($key);
            if (!$user->is_password_reset) {
                return redirect()->route('password.force-change');
            }
            return $this->redirectBasedOnLevel($employee->approval_level);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Login system error: ' . $e->getMessage(), [
                'nip' => $this->nip,
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            $this->password = '';
        }
    }

    protected function redirectBasedOnLevel(int $level)
    {
        $route = match ($level) {
            6 => 'rektor.dashboard',
            5 => 'wr.dashboard',
            4 => 'dekan.dashboard',
            3 => 'wadek.dashboard',
            2 => 'kaprodi.dashboard',
            1 => 'staff.dashboard',
            default => 'dashboard'
        };
        return redirect()->route($route)->with('success', 'Selamat datang, ' . Auth::user()->employee->name);
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}
