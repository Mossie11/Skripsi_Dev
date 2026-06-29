<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\WalikelasController;

// Serve storage files without symlink (for shared hosting compatibility)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    $mimeType = mime_content_type($fullPath);
    return response()->file($fullPath, ['Content-Type' => $mimeType]);
})->where('path', '.*')->name('storage.serve');

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/force-reset-password', [AuthController::class, 'showForceResetPassword'])->name('force-reset-password');
Route::post('/force-reset-password', [AuthController::class, 'forceResetPassword']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtp'])->name('forgot-password.send-otp');
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtp'])->name('forgot-password.verify-otp');
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('forgot-password.reset');

// ── Coordinator ───────────────────────────────────────────────────
Route::middleware(['role:coordinator'])->prefix('coordinator')->name('coordinator.')->group(function () {
    Route::get('/dashboard', [CoordinatorController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [CoordinatorController::class, 'profile'])->name('profile');
    Route::post('/profile', [CoordinatorController::class, 'updateProfile'])->name('profile.update');

    // Manage Guru
    Route::get('/manage-guru', [CoordinatorController::class, 'manageGuru'])->name('manage-guru');
    Route::post('/manage-guru', [CoordinatorController::class, 'manageGuruAction'])->name('manage-guru.action');

    // Manage Wali Kelas
    Route::get('/manage-walikelas', [CoordinatorController::class, 'manageWalikelas'])->name('manage-walikelas');
    Route::post('/manage-walikelas', [CoordinatorController::class, 'manageWalikelasAction'])->name('manage-walikelas.action');

    // Manage Siswa
    Route::get('/manage-siswa', [CoordinatorController::class, 'manageSiswa'])->name('manage-siswa');
    Route::post('/manage-siswa', [CoordinatorController::class, 'manageSiswaAction'])->name('manage-siswa.action');

    // Manage Tahun Ajaran
    Route::get('/manage-tahun', [CoordinatorController::class, 'manageTahun'])->name('manage-tahun');
    Route::post('/manage-tahun', [CoordinatorController::class, 'manageTahunAction'])->name('manage-tahun.action');

    // Manage Bobot Nilai
    Route::get('/manage-bobot', [CoordinatorController::class, 'manageBobot'])->name('manage-bobot');
    Route::post('/manage-bobot', [CoordinatorController::class, 'manageBobotAction'])->name('manage-bobot.action');

    // Manage Kelas
    Route::get('/manage-kelas', [CoordinatorController::class, 'manageKelas'])->name('manage-kelas');
    Route::post('/manage-kelas', [CoordinatorController::class, 'manageKelasAction'])->name('manage-kelas.action');

    // Manage Jadwal
    Route::get('/manage-jadwal', [CoordinatorController::class, 'manageJadwal'])->name('manage-jadwal');
    Route::post('/manage-jadwal', [CoordinatorController::class, 'manageJadwalAction'])->name('manage-jadwal.action');

    // Manage Subjects
    Route::get('/manage-subjects', [CoordinatorController::class, 'manageSubjects'])->name('manage-subjects');
    Route::post('/manage-subjects', [CoordinatorController::class, 'manageSubjectsAction'])->name('manage-subjects.action');

    // Manage Nilai
    Route::get('/manage-nilai', [CoordinatorController::class, 'manageNilai'])->name('manage-nilai');

    // Manage Periode
    Route::get('/manage-periode', [CoordinatorController::class, 'managePeriode'])->name('manage-periode');
    Route::post('/manage-periode', [CoordinatorController::class, 'managePeriodeAction'])->name('manage-periode.action');

    // Cetak Rapor
    Route::get('/cetak', [CoordinatorController::class, 'cetak'])->name('cetak');
    Route::get('/print-rapor', [CoordinatorController::class, 'printRapor'])->name('print-rapor');

    // Progress Monitoring
    Route::get('/progress', [CoordinatorController::class, 'progress'])->name('progress');
    Route::get('/progress/detail', [CoordinatorController::class, 'progressDetail'])->name('progress.detail');
    Route::get('/progress/absensi-detail', [CoordinatorController::class, 'progressAbsensiDetail'])->name('progress.absensi-detail');

    // Kenaikan Kelas
    Route::get('/kenaikan-kelas', [CoordinatorController::class, 'kenaikanKelas'])->name('kenaikan-kelas');
    Route::post('/kenaikan-kelas', [CoordinatorController::class, 'kenaikanKelasAction'])->name('kenaikan-kelas.action');

    // Manage Absensi
    Route::get('/manage-absensi', [CoordinatorController::class, 'manageAbsensi'])->name('manage-absensi');
    Route::post('/api/absensi', [CoordinatorController::class, 'manageAbsensiSave'])->name('api.absensi');
});

// ── Guru ─────────────────────────────────────────────────────
Route::middleware(['role:guru,wali_kelas'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [GuruController::class, 'profile'])->name('profile');
    Route::post('/profile', [GuruController::class, 'updateProfile'])->name('profile.update');
    Route::get('/jadwal', [GuruController::class, 'jadwal'])->name('jadwal');
    Route::get('/nilai', [GuruController::class, 'nilai'])->name('nilai');

    // AJAX API endpoints
    Route::get('/api/jadwal', [GuruController::class, 'apiJadwal'])->name('api.jadwal');
    Route::get('/api/kelas', [GuruController::class, 'apiKelas'])->name('api.kelas');
    Route::get('/api/jadwal-kelas', [GuruController::class, 'apiJadwalKelas'])->name('api.jadwal-kelas');
    Route::get('/api/nilai', [GuruController::class, 'apiNilaiGet'])->name('api.nilai.get');
    Route::post('/api/nilai', [GuruController::class, 'apiNilaiPost'])->name('api.nilai.post');
    Route::post('/api/nilai/lab', [GuruController::class, 'apiNilaiLabPost'])->name('api.nilai.lab.post');
});

// ── Wali Kelas ───────────────────────────────────────────────
Route::middleware(['role:wali_kelas'])->prefix('walikelas')->name('walikelas.')->group(function () {
    Route::get('/dashboard', [WalikelasController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [WalikelasController::class, 'profile'])->name('profile');
    Route::post('/profile', [WalikelasController::class, 'updateProfile'])->name('profile.update');
    Route::get('/kelas', [WalikelasController::class, 'kelas'])->name('kelas');
    Route::post('/kelas', [WalikelasController::class, 'kelasCreate'])->name('kelas.create');
    Route::post('/kelas/{id}', [WalikelasController::class, 'kelasUpdate'])->name('kelas.update');
    Route::get('/jadwal', [WalikelasController::class, 'jadwal'])->name('jadwal');
    Route::get('/absensi', [WalikelasController::class, 'absensi'])->name('absensi');
    Route::post('/api/absensi', [WalikelasController::class, 'absensiSave'])->name('api.absensi');
    Route::get('/nilai', [WalikelasController::class, 'nilai'])->name('nilai');
    Route::post('/api/nilai', [WalikelasController::class, 'nilaiSave'])->name('api.nilai');
    Route::post('/api/ekskul', [WalikelasController::class, 'ekskulSave'])->name('api.ekskul');
    Route::post('/api/ekskul/delete', [WalikelasController::class, 'ekskulDelete'])->name('api.ekskul.delete');
    Route::post('/api/kepribadian', [WalikelasController::class, 'kepribadianSave'])->name('api.kepribadian');
    Route::post('/api/lab/delete', [WalikelasController::class, 'labDelete'])->name('api.lab.delete');
    Route::get('/cetak', [WalikelasController::class, 'cetak'])->name('cetak');
    Route::get('/print-rapor', [WalikelasController::class, 'printRapor'])->name('print-rapor');
});
