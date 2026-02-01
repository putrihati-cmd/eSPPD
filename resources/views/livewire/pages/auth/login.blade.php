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

<div class="selection:bg-[#D4E157] selection:text-black font-sans min-h-screen relative overflow-hidden flex items-center justify-center p-6 bg-[#009ca6]">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: #009ca6;
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

        .main-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1280px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        /* Hero Text Styles */
        .hero-section {
            flex: 1;
            color: white;
            padding-right: 4rem;
        }

        .badge-saizu {
            display: inline-flex;
            align-items: center;
            padding: 4px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            color: #d4e157;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hero-title {
            font-size: 64px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero-title .highlight {
            color: #d4e157;
            display: block;
        }

        .hero-desc {
            font-size: 18px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.82);
            margin-bottom: 3rem;
            max-width: 520px;
        }

        .hero-actions {
            display: flex;
            gap: 1.25rem;
            margin-bottom: 5rem;
        }

        .btn-lime {
            background-color: #d4e157;
            color: #1a202c;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-outline {
            background-color: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.4);
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .stats-grid {
            display: flex;
            gap: 5rem;
        }

        .stat-item h3 {
            font-size: 44px;
            font-weight: 800;
            color: #d4e157;
            margin-bottom: 0px;
        }

        .stat-item p {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 1);
            margin-top: 4px;
        }

        /* Login Card Styles */
        .login-card-wrapper {
            flex: 0 0 440px;
        }

        .login-card {
            background: #f1f5f9;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
        }

        .logo-img {
            height: 70px;
            margin-bottom: 24px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .card-title {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 14px;
            color: #475569;
            margin-bottom: 32px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            margin-left: 4px;
        }

        .form-input {
            width: 100%;
            background: white;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #009ca6;
            box-shadow: 0 0 0 4px rgba(0, 156, 166, 0.08);
        }

        .form-footer-links {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 13px;
            font-weight: 700;
            color: #009ca6;
            text-decoration: none;
        }

        .btn-submit {
            width: 100%;
            background-color: #009ca6;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 24px;
        }

        .btn-submit:hover {
            background-color: #00838c;
            transform: translateY(-1px);
        }

        .footer-cta {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }

        .footer-cta a {
            color: #009ca6;
            font-weight: 800;
            text-decoration: none;
        }

        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
                text-align: center;
                padding-top: 2rem;
            }
            .hero-section {
                padding-right: 0;
                margin-bottom: 3rem;
            }
            .hero-desc {
                margin-left: auto;
                margin-right: auto;
            }
            .hero-actions {
                justify-content: center;
            }
            .stats-grid {
                justify-content: center;
                gap: 2rem;
            }
        }
    </style>

    <div class="pattern-bg"></div>

    <div class="main-container">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="badge-saizu">UIN SAIZU Purwokerto</div>
            <h1 class="hero-title">
                Sistem Informasi
                <span class="highlight">Perjalanan Dinas</span>
            </h1>
            <p class="hero-desc">
                Efisiensi Pengajuan dan Pelaporan Perjalanan Dinas UIN Saizu Purwokerto dengan sistem digital yang modern dan terintegrasi.
            </p>

            <div class="hero-actions">
                <a href="#" class="btn-lime">
                    Pelajari Lebih Lanjut
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="#" class="btn-outline">Panduan Pengguna</a>
            </div>

            <div class="stats-grid">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Perjalanan Dinas</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Dosen & Staff</p>
                </div>
                <div class="stat-item">
                    <h3>99%</h3>
                    <p>Kepuasan</p>
                </div>
            </div>
        </div>

        <!-- Login Section -->
        <div class="login-card-wrapper">
            <div class="login-card">
                <img src="{{ asset('images/logo.png') }}" alt="Logo UIN SAIZU" class="logo-img">
                <h2 class="card-title">Login e-SPPD</h2>
                <p class="card-subtitle">Masuk ke sistem perjalanan dinas</p>

                @if (session('error'))
                    <div style="background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 700;">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit="login">
                    <div class="form-group">
                        <label class="form-label">Username / NIP</label>
                        <input wire:model="nip" type="text" placeholder="Masukkan username atau NIP" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input wire:model="password" type="password" placeholder="Masukkan password" class="form-input" required>
                    </div>

                    <div class="form-footer-links">
                        <label class="remember-me">
                            <input type="checkbox" wire:model="remember" style="margin: 0; width: 16px; height: 16px;">
                            Ingat saya
                        </label>
                        <a href="#" class="forgot-link">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                        <span wire:loading.remove>Masuk</span>
                        <span wire:loading>Memproses...</span>
                    </button>
                    
                    <div class="footer-cta">
                        Belum punya akun? <a href="#">Hubungi Admin</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
