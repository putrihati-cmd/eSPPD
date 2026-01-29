<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send OTP via chosen channel (email/whatsapp)
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|min:10|max:20',
            'channel' => 'required|in:email,whatsapp'
        ], [
            'nip.required' => 'NIP wajib diisi',
            'channel.required' => 'Pilih metode verifikasi'
        ]);

        $nip = $request->nip;
        $channel = $request->channel;

        // Find user by NIP
        $user = User::where('nip', $nip)->first();
        
        if (!$user) {
            return back()->withErrors(['nip' => 'NIP tidak terdaftar dalam sistem.']);
        }

        // Rate limiting: Max 3 attempts per hour per NIP
        $recentAttempts = DB::table('password_resets_otp')
            ->where('nip', $nip)
            ->where('created_at', '>', Carbon::now()->subHour())
            ->count();

        if ($recentAttempts >= 3) {
            return back()->withErrors([
                'nip' => 'Terlalu banyak percobaan. Coba lagi dalam 1 jam.'
            ]);
        }

        // Generate OTP (6 digits) and Token
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        // Store in database (hashed)
        DB::table('password_resets_otp')->insert([
            'nip' => $nip,
            'token' => hash('sha256', $token),
            'otp' => Hash::make($otp),
            'channel' => $channel,
            'expires_at' => Carbon::now()->addMinutes(15),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Send OTP
        try {
            if ($channel === 'whatsapp') {
                $this->sendWhatsAppOTP($user, $otp);
                $maskedContact = $this->maskPhone($user->phone_wa ?? $user->phone);
            } else {
                $this->sendEmailOTP($user, $otp);
                $maskedContact = $this->maskEmail($user->email);
            }

            return redirect()->route('password.otp', ['token' => $token])
                ->with('status', "Kode verifikasi telah dikirim ke $maskedContact");

        } catch (\Exception $e) {
            \Log::error("Failed to send OTP: " . $e->getMessage());
            return back()->withErrors([
                'channel' => 'Gagal mengirim kode. Silakan coba metode lain atau hubungi admin.'
            ]);
        }
    }

    /**
     * Show OTP verification form
     */
    public function showVerifyForm(string $token)
    {
        return view('auth.verify-otp', ['token' => $token]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
            'token' => 'required|string'
        ]);

        $tokenHash = hash('sha256', $request->token);

        $resetRecord = DB::table('password_resets_otp')
            ->where('token', $tokenHash)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['otp' => 'Kode tidak valid atau sudah expired.']);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $resetRecord->otp)) {
            \Log::warning("Invalid OTP attempt for NIP: {$resetRecord->nip}");
            return back()->withErrors(['otp' => 'Kode verifikasi salah.']);
        }

        // Mark as used
        DB::table('password_resets_otp')
            ->where('id', $resetRecord->id)
            ->update(['is_used' => true]);

        // Create session token for password reset
        $resetToken = Str::random(32);
        session([
            'password_reset_authorized' => $resetToken,
            'reset_nip' => $resetRecord->nip
        ]);

        return redirect()->route('password.reset-form', ['token' => $resetToken]);
    }

    /**
     * Show password reset form
     */
    public function showResetForm(string $token)
    {
        if (session('password_reset_authorized') !== $token) {
            return redirect()->route('login')->withErrors([
                'error' => 'Sesi reset password tidak valid. Silakan ulangi.'
            ]);
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        if (!session('password_reset_authorized') || 
            session('password_reset_authorized') !== $request->token ||
            !session('reset_nip')) {
            return redirect()->route('login')->withErrors([
                'error' => 'Sesi reset password tidak valid. Silakan ulangi.'
            ]);
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required'
        ], [
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok'
        ]);

        $nip = session('reset_nip');

        // Update password
        User::where('nip', $nip)->update([
            'password' => Hash::make($request->password),
            'is_password_reset' => true,
            'last_password_reset' => now()
        ]);

        // Clear session
        session()->forget(['password_reset_authorized', 'reset_nip']);

        // Cleanup old reset records
        DB::table('password_resets_otp')->where('nip', $nip)->delete();

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }

    /**
     * Send WhatsApp OTP (using Fonnte API)
     */
    private function sendWhatsAppOTP($user, $otp)
    {
        $phone = $this->formatPhone($user->phone_wa ?? $user->phone);

        if (!$phone) {
            throw new \Exception("No WhatsApp number available for user.");
        }

        $message = "*e-SPPD - Reset Password*\n\n" .
                   "Yth. {$user->name},\n\n" .
                   "Kode verifikasi reset password Anda: *$otp*\n\n" .
                   "Kode berlaku 15 menit. Jangan bagikan kepada siapapun.\n" .
                   "Jika Anda tidak meminta reset password, abaikan pesan ini.\n\n" .
                   "_" . now()->format('d/m/Y H:i') . "_";

        // Check if Fonnte token is configured
        $fonnteToken = config('services.fonnte.token');

        if ($fonnteToken) {
            $response = Http::withHeaders([
                'Authorization' => $fonnteToken
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62'
            ]);

            if (!$response->successful()) {
                throw new \Exception("WhatsApp API Error: " . $response->body());
            }
        } else {
            // Fallback: Log the OTP for development
            \Log::info("WhatsApp OTP for {$user->nip}: $otp (Fonnte not configured)");
        }
    }

    /**
     * Send Email OTP
     */
    private function sendEmailOTP($user, $otp)
    {
        if (!$user->email) {
            throw new \Exception("No email available for user.");
        }

        Mail::send('emails.reset-password-otp', [
            'name' => $user->name,
            'otp' => $otp,
            'expiry' => '15 menit'
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                   ->subject('[e-SPPD] Kode Verifikasi Reset Password');
        });
    }

    /**
     * Format phone number for Indonesia
     */
    private function formatPhone($phone)
    {
        if (!$phone) return null;
        
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            return '62' . substr($phone, 1);
        }
        return $phone;
    }

    /**
     * Mask phone number for privacy
     */
    private function maskPhone($phone)
    {
        if (!$phone) return '****';
        return substr($phone, 0, 4) . '****' . substr($phone, -3);
    }

    /**
     * Mask email for privacy
     */
    private function maskEmail($email)
    {
        if (!$email) return '****';
        
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        
        if (strlen($name) <= 4) {
            $masked = str_repeat('*', strlen($name));
        } else {
            $masked = substr($name, 0, 2) . str_repeat('*', strlen($name) - 4) . substr($name, -2);
        }
        
        return $masked . '@' . $domain;
    }
}
