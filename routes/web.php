<?php

use App\Livewire\Approvals\ApprovalIndex;
use App\Livewire\Approvals\ApprovalQueue;
use App\Livewire\Budgets\BudgetIndex;
use App\Livewire\Dashboard;
use App\Livewire\DashboardEnhanced;
use App\Livewire\Employees\EmployeeIndex;
use App\Livewire\Excel\ExcelManager;
use App\Livewire\Reports\ReportBuilder;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Reports\TripReportCreate;
use App\Livewire\Reports\TripReportShow;
use App\Livewire\Settings\SettingsIndex;
use App\Livewire\Spd\SpdCreate;
use App\Livewire\Spd\SpdIndex;
use App\Livewire\Spd\SpdShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Test Routes & UI (Development only)
Route::get('/test-routes', function () {
    return view('test-routes');
})->name('test-routes');

// Dashboard (Enhanced)
Route::get('dashboard', DashboardEnhanced::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// SPD Routes (All authenticated users with level >= 1)
Route::middleware(['auth', 'role.level:1'])->prefix('spd')->name('spd.')->group(function () {
    Route::get('/', SpdIndex::class)->name('index');
    Route::get('/create', SpdCreate::class)->name('create');
    Route::get('/{spd}', SpdShow::class)->name('show');

    // PDF Routes
    Route::get('/{spd}/pdf/spt', [App\Http\Controllers\SpdPdfController::class, 'downloadSpt'])->name('pdf.spt');
    Route::get('/{spd}/pdf/spd', [App\Http\Controllers\SpdPdfController::class, 'downloadSpd'])->name('pdf.spd');
    Route::get('/{spd}/preview/spt', [App\Http\Controllers\SpdPdfController::class, 'viewSpt'])->name('preview.spt');
    Route::get('/{spd}/preview/spd', [App\Http\Controllers\SpdPdfController::class, 'viewSpd'])->name('preview.spd');

    // Revision Routes (dari fitur.md - Flow Revisi SPPD)
    Route::get('/{spd}/revisi', [App\Http\Controllers\SppdRevisionController::class, 'editRejected'])->name('revisi');
    Route::post('/{spd}/resubmit', [App\Http\Controllers\SppdRevisionController::class, 'resubmit'])->name('resubmit');
    Route::get('/{spd}/history', [App\Http\Controllers\SppdRevisionController::class, 'history'])->name('history');
});

// Approval Routes (Kaprodi+ Level >= 2)
Route::middleware(['auth', 'role.level:2'])->prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', ApprovalIndex::class)->name('index');
    Route::get('/queue', ApprovalQueue::class)->name('queue');
});

// Reports / Trip Reports Routes (All authenticated users)
Route::middleware(['auth', 'role.level:1'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', ReportIndex::class)->name('index');
    Route::get('/builder', ReportBuilder::class)->name('builder');
    Route::get('/trip-report/create/{spd}', TripReportCreate::class)->name('create');
    Route::get('/trip-report/{report}', TripReportShow::class)->name('show');
    Route::get('/trip-report/{report}/edit', TripReportCreate::class)->name('edit');
    Route::get('/trip-report/{report}/download', [App\Http\Controllers\TripReportPdfController::class, 'download'])->name('download');
});

// Employee Routes (Admin Level >= 98)
Route::middleware(['auth', 'role.level:98'])->prefix('employees')->name('employees.')->group(function () {
    Route::get('/', EmployeeIndex::class)->name('index');
});

// Budget Routes
Route::middleware(['auth'])->prefix('budgets')->name('budgets.')->group(function () {
    Route::get('/', BudgetIndex::class)->name('index');
});

// Excel Import/Export Routes
Route::middleware(['auth'])->prefix('excel')->name('excel.')->group(function () {
    Route::get('/', ExcelManager::class)->name('index');
    Route::get('/template', [App\Http\Controllers\ExcelController::class, 'template'])->name('template');
    Route::post('/import', [App\Http\Controllers\ExcelController::class, 'import'])->name('import');
    Route::get('/export', [App\Http\Controllers\ExcelController::class, 'export'])->name('export');
});

// Settings Routes
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', SettingsIndex::class)->name('index');
});

// ============================================
// EMPLOYEE IMPORT ROUTES (from imp.md)
// ============================================
use App\Http\Controllers\ImportController;

Route::middleware(['auth', 'role:admin'])->prefix('import')->name('import.')->group(function () {
    Route::get('/employees', [ImportController::class, 'showForm'])->name('form');
    Route::post('/employees', [ImportController::class, 'import'])->name('employees');
    Route::get('/template', [ImportController::class, 'template'])->name('template');
});

// ============================================
// SMART IMPORT (Python FastAPI with AI Detection)
// ============================================
use App\Http\Controllers\SmartImportController;

Route::middleware(['auth', 'role:admin'])->prefix('smart-import')->name('smart-import.')->group(function () {
    Route::get('/', [SmartImportController::class, 'index'])->name('index');
    Route::post('/upload', [SmartImportController::class, 'upload'])->name('upload');
    Route::post('/mapping', [SmartImportController::class, 'updateMapping'])->name('mapping');
    Route::post('/validate/{jobId}', [SmartImportController::class, 'validate'])->name('validate');
    Route::post('/process', [SmartImportController::class, 'process'])->name('process');
    Route::post('/rollback/{jobId}', [SmartImportController::class, 'rollback'])->name('rollback');
    Route::get('/status/{jobId}', [SmartImportController::class, 'status'])->name('status');
});

// Profile (from Breeze)
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// ============================================
// FORGOT PASSWORD WITH OTP (NEW)
// ============================================
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::middleware('guest')->prefix('password-otp')->group(function () {
    // Step 1: Request OTP
    Route::get('/forgot', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request.otp');
    Route::post('/forgot', [ForgotPasswordController::class, 'sendOtp'])->name('password.send-otp');

    // Step 2: Verify OTP
    Route::get('/verify/{token}', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.otp');
    Route::post('/verify', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify-otp');

    // Step 3: Reset Password
    Route::get('/reset/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form');
    Route::post('/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset.otp');
});

// Force Change Password (for first login)
Route::middleware('auth')->group(function () {
    Route::get('/change-password', function () {
        return view('auth.force-change-password');
    })->name('password.force-change');

    Route::post('/change-password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'is_password_reset' => true,
            'last_password_reset' => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah!');
    })->name('password.force-update');
});

// ============================================
// BENDAHARA / FINANCE ROUTES
// ============================================
use App\Http\Controllers\Finance\BendaharaController;

Route::middleware(['auth', 'role:bendahara,admin'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/dashboard', [BendaharaController::class, 'dashboard'])->name('dashboard');
    Route::get('/verification', [BendaharaController::class, 'pendingVerification'])->name('verification');
    Route::post('/verify/{spd}', [BendaharaController::class, 'verify'])->name('verify');
    Route::get('/payment', [BendaharaController::class, 'pendingPayment'])->name('payment');
    Route::post('/payment/{spd}', [BendaharaController::class, 'processPayment'])->name('process-payment');
    Route::get('/report', [BendaharaController::class, 'report'])->name('report');
});

// ============================================
// ADMIN USER MANAGEMENT (from lupa.md)
// ============================================
use App\Http\Controllers\Admin\UserManagementController;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\RoleManagement;
use App\Livewire\Admin\OrganizationManagement;
use App\Livewire\Admin\DelegationManagement;
use App\Livewire\Admin\AuditLogViewer;
use App\Livewire\Admin\ActivityDashboard;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // User management - Gate check is inside controller
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('/users/{user}/detail', [UserManagementController::class, 'show'])->name('users.show');

    // New Admin Livewire Components (Level >= 98)
    Route::middleware('role.level:98')->group(function () {
        Route::get('/user-management', UserManagement::class)->name('user-management');
        Route::get('/role-management', RoleManagement::class)->name('role-management');
        Route::get('/organization-management', OrganizationManagement::class)->name('organization-management');
        Route::get('/delegation-management', DelegationManagement::class)->name('delegation-management');
        Route::get('/audit-logs', AuditLogViewer::class)->name('audit-logs');
        Route::get('/activity-dashboard', ActivityDashboard::class)->name('activity-dashboard');
    });
});

require __DIR__.'/auth.php';

