<?php

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
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
        $user = Auth::user();
        $employee = $user->employee;
        $defaultPass = $employee->default_password;
        if (!Hash::check($this->current_password, $user->password) && $this->current_password !== $defaultPass) {
            $this->addError('current_password', 'Password saat ini tidak sesuai.');
            return;
        }
        $user->update([
            'password' => Hash::make($this->new_password),
            'is_password_reset' => true,
        ]);
        Log::info('Password changed', ['nip' => $employee->nip, 'by_user' => $user->id]);
        return $this->redirectBasedOnLevel($employee->approval_level);
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
        return redirect()->route($route)->with('success', 'Password berhasil diubah. Selamat datang!');
    }

    public function render()
    {
        return view('livewire.pages.auth.force-password-change');
    }
}
