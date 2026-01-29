<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class UserManagementController extends Controller
{
    /**
     * Daftar user untuk manajemen
     */
    public function index(Request $request)
    {
        // Gate check
        if (Gate::denies('access-admin')) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk admin.');
        }

        $query = User::with(['employee', 'roleModel'])->orderBy('name');
        
        // Search by NIP or Name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('nip', 'like', "%{$search}%")
                         ->orWhere('name', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        $users = $query->paginate(20)->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Reset Password ke Default (DDMMYYYY dari tanggal lahir)
     */
    public function resetPassword(Request $request, User $user)
    {
        // Gate check
        if (Gate::denies('access-admin')) {
            abort(403, 'Akses ditolak.');
        }

        // Admin tidak boleh reset password sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa reset password sendiri. Hubungi superadmin.');
        }

        // Ambil data employee untuk tanggal lahir
        $employee = $user->employee;
        
        if (!$employee || !$employee->birth_date) {
            return back()->with('error', "Data pegawai atau tanggal lahir tidak ditemukan untuk {$user->name}. Tidak bisa reset password.");
        }

        // Generate password default: DDMMYYYY
        $defaultPassword = $employee->birth_date->format('dmY');
        
        // Update user
        $user->update([
            'password' => Hash::make($defaultPassword),
            'is_password_reset' => false, // Flag untuk force ganti password
            'password_changed_at' => null,
        ]);

        // Log aktivitas (audit trail)
        Log::info('Password reset by admin', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
            'target_nip' => $employee->nip,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Pesan untuk copy ke WA
        $waMessage = "Yth. {$user->name},\n" .
                     "Password Anda telah direset oleh admin.\n\n" .
                     "NIP: {$employee->nip}\n" .
                     "Password Baru: {$defaultPassword}\n\n" .
                     "Silakan login dan ganti password segera.\n\n" .
                     "e-SPPD System";

        return back()
            ->with('success', "Password untuk {$user->name} berhasil direset ke: <strong>{$defaultPassword}</strong>. User wajib ganti password saat login berikutnya.")
            ->with('copy_message', $waMessage);
    }

    /**
     * Detail user (API response)
     */
    public function show(User $user)
    {
        if (Gate::denies('access-admin')) {
            abort(403);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nip' => $user->employee?->nip ?? '-',
            'role' => $user->role,
            'role_label' => $user->roleModel?->label ?? ucfirst($user->role ?? 'User'),
            'is_password_reset' => $user->is_password_reset ?? false,
            'last_login' => $user->last_login_at?->format('d/m/Y H:i') ?? 'Belum pernah login',
            'created_at' => $user->created_at->format('d/m/Y'),
        ]);
    }
}
