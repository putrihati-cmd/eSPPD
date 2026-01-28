<?php

use App\Livewire\Approvals\ApprovalIndex;
use App\Livewire\Budgets\BudgetIndex;
use App\Livewire\Dashboard;
use App\Livewire\Employees\EmployeeIndex;
use App\Livewire\Reports\ReportIndex;
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

// SPD Routes
Route::middleware(['auth'])->prefix('spd')->name('spd.')->group(function () {
    Route::get('/', SpdIndex::class)->name('index');
    Route::get('/create', SpdCreate::class)->name('create');
    Route::get('/{spd}', SpdShow::class)->name('show');
    
    // PDF Routes
    Route::get('/{spd}/pdf/spt', [App\Http\Controllers\SpdPdfController::class, 'downloadSpt'])->name('pdf.spt');
    Route::get('/{spd}/pdf/spd', [App\Http\Controllers\SpdPdfController::class, 'downloadSpd'])->name('pdf.spd');
    Route::get('/{spd}/preview/spt', [App\Http\Controllers\SpdPdfController::class, 'viewSpt'])->name('preview.spt');
    Route::get('/{spd}/preview/spd', [App\Http\Controllers\SpdPdfController::class, 'viewSpd'])->name('preview.spd');
});

// Approval Routes
Route::middleware(['auth'])->prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/', ApprovalIndex::class)->name('index');
});

// Reports Routes
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', ReportIndex::class)->name('index');
});

// Employee Routes
Route::middleware(['auth'])->prefix('employees')->name('employees.')->group(function () {
    Route::get('/', EmployeeIndex::class)->name('index');
});

// Budget Routes
Route::middleware(['auth'])->prefix('budgets')->name('budgets.')->group(function () {
    Route::get('/', BudgetIndex::class)->name('index');
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
