<?php

use App\Livewire\Approvals\ApprovalIndex;
use App\Livewire\Approvals\ApprovalQueue;
use App\Livewire\Budgets\BudgetIndex;
use App\Livewire\Dashboard;
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

Route::view('/', 'welcome');

// Dashboard
Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// SPD Routes (Dosen & Admin)
Route::middleware(['auth', 'role:employee,admin'])->prefix('spd')->name('spd.')->group(function () {
    Route::get('/', SpdIndex::class)->name('index');
    Route::get('/create', SpdCreate::class)->name('create');
    Route::get('/{spd}', SpdShow::class)->name('show');
    
    // PDF Routes
    Route::get('/{spd}/pdf/spt', [App\Http\Controllers\SpdPdfController::class, 'downloadSpt'])->name('pdf.spt');
    Route::get('/{spd}/pdf/spd', [App\Http\Controllers\SpdPdfController::class, 'downloadSpd'])->name('pdf.spd');
    Route::get('/{spd}/preview/spt', [App\Http\Controllers\SpdPdfController::class, 'viewSpt'])->name('preview.spt');
    Route::get('/{spd}/preview/spd', [App\Http\Controllers\SpdPdfController::class, 'viewSpd'])->name('preview.spd');
});

// Approval Routes (Atasan & Admin)
Route::middleware(['auth', 'role:approver,admin'])->prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', ApprovalIndex::class)->name('index');
    Route::get('/queue', ApprovalQueue::class)->name('queue');
});

// Reports / Trip Reports Routes (Dosen who travelled)
Route::middleware(['auth', 'role:employee,admin'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', ReportIndex::class)->name('index');
    Route::get('/builder', ReportBuilder::class)->name('builder');
    Route::get('/trip-report/create/{spd}', TripReportCreate::class)->name('create');
    Route::get('/trip-report/{report}', TripReportShow::class)->name('show');
    Route::get('/trip-report/{report}/edit', TripReportCreate::class)->name('edit');
    Route::get('/trip-report/{report}/download', [App\Http\Controllers\TripReportPdfController::class, 'download'])->name('download');
});

// Employee Routes (Admin only, or Read-only for others - adjusting to Admin for now)
Route::middleware(['auth', 'role:admin'])->prefix('employees')->name('employees.')->group(function () {
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

// Profile (from Breeze)
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
