<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';
    public string $password_confirmation = '';
    public bool $showPassword = false;
    public bool $showPasswordConfirmation = false;
    public bool $isLoading = false;

    /**
     * Mount: Check user is logged in and NOT reset password yet
     */
    public function mount(): void
    {
        $user = Auth::user();

        // Jika user belum login atau sudah ganti password, redirect ke dashboard
        if (!$user || $user->is_password_reset) {
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    /**
     * Change password
     */
    public function changePassword(): void
    {
        $this->isLoading = true;

        $this->validate([
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        $user = Auth::user();

        if (!$user) {
            $this->isLoading = false;
            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($this->password),
            'is_password_reset' => true, // Set flag = user sudah ganti password
            'password_changed_at' => now(),
        ]);

        // Clear session and redirect to dashboard
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function togglePasswordConfirmationVisibility(): void
    {
        $this->showPasswordConfirmation = !$this->showPasswordConfirmation;
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('login'), navigate: true);
    }
};
