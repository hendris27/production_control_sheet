<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Auth\LoginController;

use Illuminate\Support\Facades\Redirect;
// Route logout untuk Filament agar tidak error


// Redirect root URL ke halaman login admin custom
Route::get('/', function () {
    return Redirect::to('/admin/login');
});



// Route logout khusus untuk Filament (sidebar/widget), harus POST dan nama sesuai Filament
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('filament.admin.auth.logout');



// Login custom (Blade)
// GET: Tampilkan halaman login custom (login-dashboard.blade.php) untuk admin
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
// GET: Route dengan nama yang dibutuhkan Filament agar redirect login ke custom login
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('filament.admin.auth.login');
// POST: Proses login custom (NIK & password), nama route agar form action route('admin.login') tidak error
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login');


// Optional: jika ingin login umum (bukan admin) juga pakai custom login
// GET: Tampilkan halaman login custom untuk /login umum
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// POST: Proses login umum
Route::post('/login', [LoginController::class, 'login']);
// POST: Proses logout umum (bukan Filament)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



use App\Http\Controllers\ProductionControlController;

// Download PDF report for a production control record
Route::get('/production-controls/{production_control}/download', [ProductionControlController::class, 'downloadPdf'])
    ->name('production_control.download')
    ->middleware('auth');

// Bulk download route (accepts ?ids=1,2,3)
Route::get('/production-controls/download-many', [ProductionControlController::class, 'downloadMultiple'])
    ->name('production_control.download_many')
    ->middleware('auth');

// Schedule generation (save to server after 1 hour)
Route::get('/production-controls/schedule-download', [ProductionControlController::class, 'scheduleDownload'])
    ->name('production_control.schedule_download')
    ->middleware('auth');

// Diagnostic route: test writing a small file to the preferred report location
Route::get('/production-controls/test-report-path', [ProductionControlController::class, 'testReportPath'])
    ->name('production_control.test_report_path')
    ->middleware('auth');


