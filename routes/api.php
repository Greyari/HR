<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DengerController;
use App\Http\Controllers\Api\FiturController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\GajiController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\ManajemenAkunController;
use App\Http\Controllers\Api\ManajemenDeviceController;
use App\Http\Controllers\Api\PengaturanController;
use App\Http\Controllers\Api\PengingatController;
use App\Http\Controllers\Api\PeranController;
use App\Http\Controllers\Api\PotonganGajiController;
use App\Http\Controllers\Api\TugasController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CutiController;
use App\Http\Controllers\Api\DepartemenController;
use App\Http\Controllers\Api\KantorController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckFitur;

// publik route
Route::post('/login', [AuthController::class, 'login']);

// lupa kata sandi route
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink']);

// auth sanctum
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/test-lembur', [LemburController::class, 'submitDummy']);

    // route token user
    Route::get('/me', [AuthController::class, 'me']);

    // route logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Route
    Route::put('/email', [AuthController::class, 'updateEmail']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Route setting
    Route::get('pengaturan', [PengaturanController::class, 'show']);
    Route::post('pengaturan', [PengaturanController::class, 'update']);

    // Lembur Routes
    Route::prefix('lembur')->group(function () {
        Route::get('', [LemburController::class, 'index'])->middleware(CheckFitur::class . ':lihat_lembur');
        Route::post('', [LemburController::class, 'store'])->middleware(CheckFitur::class . ':tambah_lembur');
        Route::put('{id}/approve', [LemburController::class, 'approve'])->middleware(CheckFitur::class . ':approve_lembur');
        Route::put('{id}/decline', [LemburController::class, 'decline'])->middleware(CheckFitur::class . ':decline_lembur');
    });

    // Cuti Routes
    Route::prefix('cuti')->group(function () {
        Route::get('', [CutiController::class, 'index'])->middleware(CheckFitur::class . ':lihat_cuti');
        Route::post('', [CutiController::class, 'store'])->middleware(CheckFitur::class . ':tambah_cuti');
        Route::put('{id}/approve', [CutiController::class, 'approve'])->middleware(CheckFitur::class . ':approve_cuti');
        Route::put('{id}/decline', [CutiController::class, 'decline'])->middleware(CheckFitur::class . ':decline_cuti');
    });

    // Tugas Routes
    Route::prefix('tugas')->group(function () {
        Route::get('', [TugasController::class, 'index'])->middleware(CheckFitur::class . ':lihat_tugas');
        Route::post('', [TugasController::class, 'store'])->middleware(CheckFitur::class . ':tambah_tugas');
        Route::post('{id}/upload-file', [TugasController::class, 'uploadLampiran'])->middleware(CheckFitur::class . ':tambah_lampiran_tugas');
        Route::put('{id}/status', [TugasController::class, 'updateStatus'])->middleware(CheckFitur::class . ':ubah_status_tugas');
        Route::put('{id}', [TugasController::class, 'update'])->middleware(CheckFitur::class . ':edit_tugas');
        Route::delete('{id}', [TugasController::class, 'destroy'])->middleware(CheckFitur::class . ':hapus_tugas');
    });

    // Departemen Routes
    Route::prefix('departemen')->middleware(CheckFitur::class . ':departemen')->group(function () {
        Route::get('', [DepartemenController::class, 'index']);
        Route::post('', [DepartemenController::class, 'store']);
        Route::put('{id}', [DepartemenController::class, 'update']);
        Route::delete('{id}', [DepartemenController::class, 'destroy']);
    });

    // Peran Routes
    Route::prefix('peran')->middleware(CheckFitur::class . ':peran')->group(function () {
        Route::get('', [PeranController::class, 'index']);
        Route::post('', [PeranController::class, 'store']);
        Route::put('{id}', [PeranController::class, 'update']);
        Route::delete('{id}', [PeranController::class, 'destroy']);
    });

    // Jabatan Routes
    Route::prefix('jabatan')->middleware(CheckFitur::class . ':jabatan')->group(function () {
        Route::get('', [JabatanController::class, 'index']);
        Route::post('', [JabatanController::class, 'store']);
        Route::put('{id}', [JabatanController::class, 'update']);
        Route::delete('{id}', [JabatanController::class, 'destroy']);
    });

    // User Routes
    Route::prefix('user')->middleware(CheckFitur::class . ':karyawan')->group(function () {
        Route::get('', [UserController::class, 'index']);
        Route::post('', [UserController::class, 'store']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    // Gaji Routes
    Route::prefix('gaji')->middleware(CheckFitur::class . ':gaji')->group(function () {
        Route::get('', [GajiController::class, 'calculateAll']);
        Route::get('/periods', [GajiController::class, 'availablePeriods']);
        Route::put('{id}/status', [GajiController::class, 'updateStatus']);
        Route::get('/export', [GajiController::class, 'export']);
    });

    // Potongan gaji
    Route::prefix('potongan_gaji')->middleware(CheckFitur::class . ':potongan_gaji')->group(function () {
        Route::get('', [PotonganGajiController::class, 'index']);
        Route::post('', [PotonganGajiController::class, 'store']);
        Route::put('{id}', [PotonganGajiController::class, 'update']);
        Route::delete('{id}', [PotonganGajiController::class, 'destroy']);
    });

    // Kantor Routes
    Route::prefix('kantor')->middleware(CheckFitur::class . ':kantor')->group(function () {
        Route::get('', [KantorController::class, 'index']);
        Route::post('', [KantorController::class, 'saveProfile']);
    });

    // Route untuk tindakan absensi (checkin / checkout)
    Route::prefix('absensi')->group(function () {
        Route::middleware(CheckFitur::class . ':absensi')->group(function () {
            Route::post('checkin', [AbsensiController::class, 'checkin']);
            Route::post('checkout', [AbsensiController::class, 'checkout']);
        });

        Route::middleware(CheckFitur::class . ':lihat_absensi_sendiri,lihat_semua_absensi')->group(function () {
            Route::get('', [AbsensiController::class, 'getAbsensi']);
        });
    });
    
    // Log Activity Route
    Route::prefix('log')->middleware(CheckFitur::class . ':log_aktifitas')->group(function () {
        Route::get('', [ActivityLogController::class, 'index']);
    });

    // Pengingat Route
    Route::prefix('pengingat')->middleware(CheckFitur::class . ':pengingat')->group(function () {
        Route::get('', [PengingatController::class, 'index']);
        Route::post('', [PengingatController::class, 'store']);
        Route::put('{id}', [PengingatController::class, 'update']);
        Route::delete('{id}', [PengingatController::class, 'destroy']);
    });

    // Buka akun terkunci Route
    Route::prefix('akun')->middleware(CheckFitur::class . ':buka_akun')->group(function () {
        Route::get('terkunci', [ManajemenAkunController::class, 'lockedUsers']);
        Route::post('{id}/terkunci', [ManajemenAkunController::class, 'unlockUser']);
    });

    // update device Route
    Route::prefix('device')->middleware(CheckFitur::class . ':reset_device')->group(function () {
        Route::get('', [ManajemenDeviceController::class, 'allDevices']);
        Route::post('{id}/reset', [ManajemenDeviceController::class, 'resetDevice']);
    });

    // Route denger
    Route::prefix('danger')->group(function () {
        Route::post('cuti/reset', [DengerController::class, 'resetCutiByMonth']);
        Route::get('cuti/months', [DengerController::class, 'availableCutiMonths']);

        Route::post('lembur/reset', [DengerController::class, 'resetLemburByMonth']);
        Route::get('lembur/months', [DengerController::class, 'availableLemburMonths']);

        Route::post('gaji/reset', [DengerController::class, 'resetGajiByMonth']);
        Route::get('gaji/months', [DengerController::class, 'availableGajiMonths']);

        Route::post('tugas/reset', [DengerController::class, 'resetTugasByMonth']);
        Route::get('tugas/months', [DengerController::class, 'availableTugasMonths']);

        Route::post('log/reset', [DengerController::class, 'resetLogByMonth']);
        Route::get('log/months', [DengerController::class, 'availableLogMonths']);

        Route::post('absensi/reset', [DengerController::class, 'resetAbsenByMonth']);
        Route::get('absensi/months', [DengerController::class, 'availableAbsenMonths']);
    });

    // ambil data tampa midlaware
    Route::get('user/tugas', [UserController::class, 'index']);
    Route::get('kantor/jam', [KantorController::class, 'index']);
    Route::get('/fitur', [FiturController::class, 'index']);
});
