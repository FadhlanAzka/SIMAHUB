<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Mahasiswa\AttendanceController as MahasiswaAttendanceController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke login / dashboard
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('mahasiswa.dashboard');
    }
    return redirect()->route('login');
});

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Area Mahasiswa Magang (Role: mahasiswa)
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    Route::post('/attendance/check-in', [MahasiswaAttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [MahasiswaAttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/history', [MahasiswaAttendanceController::class, 'history'])->name('history');
});

// Area Administrator Instansi (Role: admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Manajemen Mahasiswa Magang
    Route::resource('mahasiswa', MahasiswaController::class);
    Route::post('mahasiswa/{mahasiswa}/reset-password', [MahasiswaController::class, 'resetPassword'])->name('mahasiswa.reset_password');
    
    // Monitoring & Rekap Absensi
    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    
    // Export Data Bulanan
    Route::get('/export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');
    Route::get('/export/print', [ExportController::class, 'printReport'])->name('export.print');
    
    // Pengaturan Instansi & Geofencing
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
