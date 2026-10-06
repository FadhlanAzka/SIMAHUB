<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\intern\AttendanceController as internAttendanceController;
use App\Http\Controllers\intern\DashboardController as internDashboardController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\internController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard (otomatis ditangani auth middleware)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Unified Dashboard Route
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Area Peserta Magang (Role: intern)
Route::middleware(['auth', 'role:intern'])->prefix('intern')->name('intern.')->group(function () {
    Route::post('/attendance/check-in', [internAttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [internAttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/history', [internAttendanceController::class, 'history'])->name('history');
    
    // Profil Peserta Magang
    Route::get('/profile', [\App\Http\Controllers\Intern\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Intern\ProfileController::class, 'update'])->name('profile.update');
});

// Area Administrator Instansi (Role: admin, pengawas, mentor)
Route::middleware(['auth', 'role:admin,pengawas,mentor'])->prefix('admin')->name('admin.')->group(function () {
    
    // Manajemen Peserta Magang
    Route::resource('intern', internController::class);
    Route::post('intern/{intern}/reset-password', [internController::class, 'resetPassword'])->name('intern.reset_password');
    
    // Monitoring & Rekap Absensi
    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AdminAttendanceController::class, 'store'])->name('attendance.store');
    Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/attendance/{attendance}', [AdminAttendanceController::class, 'destroy'])->name('attendance.destroy');
    
    // Export Data Bulanan
    Route::get('/export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');
    Route::get('/export/print', [ExportController::class, 'printReport'])->name('export.print');
    
    // Pengaturan Instansi & Geofencing
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Profil Admin / Mentor
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
});
