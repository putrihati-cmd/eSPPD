<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.login')] class extends Component
{
    public string $nip = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showPassword = false;
    public bool $isLoading = false;

    public function login(): void
    {
        $this->isLoading = true;

        try {
            $this->validate([
                'nip' => 'required',
                'password' => 'required|string|min:8',
            ]);

            $employee = Employee::where('nip', $this->nip)->first();

            if (!$employee) {
                if (Auth::attempt(['email' => $this->nip, 'password' => $this->password], $this->remember)) {
                    $this->handleSuccess();
                    return;
                }
                throw ValidationException::withMessages(['nip' => 'Kredensial tidak ditemukan.']);
            }

            $user = $employee->user;
            if (!$user) {
                throw ValidationException::withMessages(['nip' => 'Akun belum aktif.']);
            }

            if (!Auth::attempt(['email' => $user->email, 'password' => $this->password], $this->remember)) {
                throw ValidationException::withMessages(['password' => 'Password salah.']);
            }

            $this->handleSuccess();
        } catch (ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    protected function handleSuccess(): void
    {
        Session::regenerate();
        $user = Auth::user();

        if ($user && isset($user->is_password_reset) && !$user->is_password_reset) {
            $this->redirect(route('password.force-change'), navigate: true);
            return;
        }

        $this->redirectIntended(route('dashboard'), navigate: true);
    }
}
?>

<div class="selection:bg-[#D4E157] selection:text-black font-sans min-h-screen relative overflow-hidden flex items-center justify-center bg-[#009ca6]">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: #009ca6;
            overflow-x: hidden;
        }

        .pattern-bg {
            position: fixed;
            inset: 0;
            background-image: url('{{ asset('images/pattern.png') }}');
            background-size: 800px;
            background-repeat: repeat;
            opacity: 0.15;
            mix-blend-mode: overlay;
            z-index: 1;
        }

        .main-content {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1280px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 4rem;
            box-sizing: border-box;
        }

        /* Hero styles */
        .hero-left {
            flex: 1;
            color: white;
            padding-right: 2rem;
            animation: fadeIn 0.8s ease-out;
        }

        .badge-pill {
            background: rgba(255, 255, 255, 0.1);
            color: #d4e157;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hero-h1 {
            font-size: 64px;
            font-weight: 800;
            line-height: 1.1;
            margin: 0 0 20px 0;
        }

        .hero-h1 span.lime {
            color: #d4e157;
            display: block;
        }

        .hero-p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            max-width: 500px;
            margin-bottom: 32px;
        }

        .hero-btns {
            display: flex;
            gap: 16px;
            margin-bottom: 60px;
        }

        .hero-btns .btn-primary {
            background: #d4e157;
            color: #1a202c;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .hero-btns .btn-outline {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .stats-wrap {
            display: flex;
            gap: 60px;
        }

        .stat-box .num {
            font-size: 48px;
            font-weight: 800;
            color: #d4e157;
            line-height: 1;
        }

        .stat-box .label {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Card styles */
        .card-right {
            flex: 0 0 450px;
            animation: fadeIn 0.8s ease-out 0.2s backwards;
        }

        .login-card {
            background: #f1f5f9;
            border-radius: 20px;
            padding: 48px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .login-card .logo {
            display: block;
            margin: 0 auto 24px;
            height: 72px;
        }

        .login-card .title {
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .login-card .subtitle {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
        }

        .auth-form .group {
            margin-bottom: 20px;
        }

        .auth-form .label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
            margin-left: 2px;
        }

        .auth-form .input {
            width: 100%;
            padding: 12px 16px;
            background: white;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .auth-form .input:focus {
            outline: none;
            border-color: #009ca6;
            box-shadow: 0 0 0 3px rgba(0, 156, 166, 0.1);
        }

        .auth-form .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .auth-form .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }

        .auth-form .forgot {
            font-size: 13px;
            font-weight: 700;
            color: #009ca4;
            text-decoration: none;
        }

        .auth-form .submit-btn {
            width: 100%;
            background: #009ca4;
            color: white;
            font-weight: 800;
            font-size: 15px;
            padding: 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 24px;
        }

        .auth-form .submit-btn:hover {
            background: #008a91;
            transform: translateY(-1px);
        }

        .auth-form .reg-text {
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
        }

        .auth-form .reg-text a {
            color: #009ca4;
            font-weight: 800;
            text-decoration: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 1024px) {
            .main-content { flex-direction: column; padding: 4rem 2rem; text-align: center; }
            .hero-left { padding-right: 0; margin-bottom: 3rem; }
            .hero-p { margin: 0 auto 32px; }
            .hero-btns { justify-content: center; }
            .stats-wrap { justify-content: center; gap: 40px; }
        }
    </style>

    <div class="pattern-bg"></div>

    <div class="main-content">
        <!-- Hero Section -->
        <div class="hero-left">
            <div class="badge-pill">UIN SAIZU Purwokerto</div>
            <h1 class="hero-h1">
                Sistem Informasi
                <span class="lime">Perjalanan Dinas</span>
            </h1>
            <p class="hero-p">
                Efisiensi Pengajuan dan Pelaporan Perjalanan Dinas UIN Saizu Purwokerto dengan sistem digital yang modern dan terintegrasi.
            </p>

            <div class="hero-btns">
                <a href="#" class="btn-primary">
                    Pelajari Lebih Lanjut
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="#" class="btn-outline">Panduan Pengguna</a>
            </div>

            <div class="stats-wrap">
                <div class="stat-box">
                    <div class="num">500+</div>
                    <div class="label">Perjalanan Dinas</div>
                </div>
                <div class="stat-box">
                    <div class="num">50+</div>
                    <div class="label">Dosen & Staff</div>
                </div>
                <div class="stat-box">
                    <div class="num">99%</div>
                    <div class="label">Kepuasan</div>
                </div>
            </div>
        </div>

        <!-- Login Card -->
        <div class="card-right">
            <div class="login-card">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
                <h2 class="title">Login e-SPPD</h2>
                <p class="subtitle">Masuk ke sistem perjalanan dinas</p>

                @if (session('error'))
                    <div style="background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 13px; font-weight: 700; text-align: center;">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit="login" class="auth-form">
                    <div class="group">
                        <label class="label">Username / NIP</label>
                        <input wire:model="nip" type="text" placeholder="Masukkan username atau NIP" class="input" required>
                    </div>

                    <div class="group" style="margin-bottom: 24px;">
                        <label class="label">Password</label>
                        <input wire:model="password" type="password" placeholder="Masukkan password" class="input" required>
                    </div>

                    <div class="footer-row">
                        <label class="remember">
                            <input type="checkbox" wire:model="remember" style="width: 16px; height: 16px;">
                            Ingat saya
                        </label>
                        <a href="#" class="forgot">Lupa password?</a>
                    </div>

                    <button type="submit" class="submit-btn" wire:loading.attr="disabled">
                        <span wire:loading.remove>Masuk</span>
                        <span wire:loading>Memproses...</span>
                    </button>

                    <div class="reg-text">
                        Belum punya akun? <a href="#">Hubungi Admin</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
