<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\InternController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Intern\AttendanceController as InternAttendanceController;
use App\Http\Controllers\Intern\ProfileController;
use Database\Seeders\ProductionSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard (otomatis ditangani auth middleware)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Unified Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Akses Lampiran Presensi (Bisa diakses Admin & Peserta yang bersangkutan)
Route::get('/attendance/{attendance}/attachment', [AdminAttendanceController::class, 'showAttachment'])
    ->middleware('auth')
    ->name('attendance.attachment');

// Area Peserta Magang (Role: intern)
Route::middleware(['auth', 'role:intern'])->prefix('intern')->name('intern.')->group(function () {
    Route::post('/attendance/check-in', [InternAttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [InternAttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::get('/history', [InternAttendanceController::class, 'history'])->name('history');

    // Profil Peserta Magang
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Area Administrator Instansi (Role: admin, pengawas, mentor)
Route::middleware(['auth', 'role:admin,pengawas,mentor'])->prefix('admin')->name('admin.')->group(function () {

    // Manajemen Peserta Magang
    Route::resource('intern', InternController::class);
    Route::post('intern/{intern}/reset-password', [InternController::class, 'resetPassword'])->name('intern.reset_password');

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
    Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
});

// Endpoint untuk menjalankan post-deploy commands via GitHub Actions
// withoutMiddleware('web'): dipanggil oleh curl (tanpa CSRF token/cookie) dan harus tetap
// berjalan pada deploy pertama ketika tabel sessions/cache belum dibuat oleh migrasi.
Route::post('/_deploy', function (Request $request) {
    $token = config('app.deploy_token');

    if (! $token || ! hash_equals($token, (string) $request->header('X-Deploy-Token', ''))) {
        abort(403, 'Unauthorized');
    }

    try {
        // Jalankan migrasi
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();

        // Seed data awal produksi (opsional, hanya jika diminta dari workflow)
        $seedOutput = null;
        if ($request->header('X-Deploy-Seed') === '1') {
            Artisan::call('db:seed', [
                '--class' => ProductionSeeder::class,
                '--force' => true,
            ]);
            $seedOutput = Artisan::output();
        }

        // Optimize / clear cache
        Artisan::call('optimize');
        $optimizeOutput = Artisan::output();
    } catch (Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'status' => 'success',
        'migrate' => $migrateOutput,
        'seed' => $seedOutput,
        'optimize' => $optimizeOutput,
    ]);
})->withoutMiddleware('web');
