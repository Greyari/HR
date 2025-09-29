<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DengerController;
use App\Http\Controllers\Api\FiturController;
use App\Http\Controllers\Api\GajiController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\PengingatController;
use App\Http\Controllers\Api\PeranController;
use App\Http\Controllers\Api\PotonganGajiController;
use App\Http\Controllers\Api\TugasController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CutiController;
use App\Http\Controllers\Api\DepartemenController;
use App\Http\Controllers\Api\KantorController;
use App\Http\Middleware\CheckFitur;
use Illuminate\Support\Facades\Route;
use Cloudinary\Cloudinary;


Route::get('/debug-env-clean', function () {
    return [
        'CLOUDINARY_KEY' => env_clean('CLOUDINARY_API_KEY'),
        'CLOUDINARY_SECRET' => env_clean('CLOUDINARY_API_SECRET'),
        'CLOUDINARY_CLOUD' => env_clean('CLOUDINARY_CLOUD_NAME'),
    ];
});

Route::get('/test-upload', function () {
    // Buat instance Cloudinary langsung
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => config('cloudinary.cloud.cloud_name'),
            'api_key'    => config('cloudinary.cloud.api_key'),
            'api_secret' => config('cloudinary.cloud.api_secret'),
        ],
        'url' => [
            'secure' => true,
        ],
    ]);

    // Path file lokal
    $path = storage_path('app/public/videos/tes.mp4');

    // Upload video
    $result = $cloudinary->uploadApi()->upload($path, [
        'resource_type' => 'video',
        'folder'        => 'tugas/videos',
    ]);

    return response()->json([
        'message' => 'Upload berhasil!',
        'result'  => $result,
    ]);
});


// publik route
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // route untuk logout dan token user
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Route
    Route::put('/email',[AuthController::class, 'updateEmail']);
    // nanti buat route untuk ubah password dari email yang terdaftar (ubah passwordnya ada di kirim link ke email)

    // Lembur Routes
    Route::prefix('lembur')->group(function () {
        Route::get('', [LemburController::class, 'index'])->middleware(CheckFitur::class . ':lihat_lembur');
        Route::post('', [LemburController::class, 'store'])->middleware(CheckFitur::class . ':tambah_lembur');
        Route::put('{id}/approve', [LemburController::class, 'approve'])->middleware(CheckFitur::class . ':approve_lembur');
        Route::put('{id}/decline', [LemburController::class, 'decline'])->middleware(CheckFitur::class . ':decline_lembur');
    });

    // note besar untuk cuti :: masih bingung gimana bagusnya untuk konsep jatah cuti tahunan
    // kemudian seharusnya kalau cuti di acc dia sudah bisa ke generate ke absensi kalau hari ini dia cuti
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

        // ⬅️ taruh upload-file dulu
        Route::post('{id}/upload-file', [TugasController::class, 'uploadLampiran'])->middleware(CheckFitur::class . ':tambah_lampiran_tugas');
        Route::put('{id}/status', [TugasController::class, 'updateStatus'])->middleware(CheckFitur::class . ':ubah_status_tugas');
        // baru route yang generik {id}
        Route::put('{id}', [TugasController::class, 'update'])->middleware(CheckFitur::class . ':edit_tugas');
        Route::delete('{id}', [TugasController::class, 'destroy'])->middleware(CheckFitur::class . ':hapus_tugas');
    });

    // Departemen Routes
    Route::prefix('departemen')->middleware(CheckFitur::class . ':departemen')->group(function() {
        Route::get('', [DepartemenController::class, 'index']);
        Route::post('', [DepartemenController::class, 'store']);
        Route::put('{id}', [DepartemenController::class, 'update']);
        Route::delete('{id}', [DepartemenController::class, 'destroy']);
    });

    // Peran Routes
    Route::prefix('peran')->middleware(CheckFitur::class . ':peran')->group(function() {
        // note mungkin belum bisa di kerjakan sekedar membuat controller dan route
        // nunggu perancangan fitur dan izin fitur
        Route::get('', [PeranController::class, 'index']);
        Route::post('', [PeranController::class, 'store']);
        Route::put('{id}', [PeranController::class, 'update']);
        Route::delete('{id}', [PeranController::class, 'destroy']);
    });

    // Jabatan Routes
    Route::prefix('jabatan')->middleware(CheckFitur::class . ':jabatan')->group(function() {
        Route::get('', [JabatanController::class, 'index']);
        Route::post('', [JabatanController::class, 'store']);
        Route::put('{id}', [JabatanController::class, 'update']);
        Route::delete('{id}', [JabatanController::class, 'destroy']);
    });

    // User Routes
    Route::prefix('user')->middleware(CheckFitur::class . ':karyawan')->group(function() {
        Route::get('', [UserController::class, 'index']);
        Route::post('', [UserController::class, 'store']);
        Route::put('{id}',[UserController::class, 'update']);
        Route::delete('{id}',[UserController::class, 'destroy']);
    });

    // Gaji Routes
    Route::prefix('gaji')->middleware(CheckFitur::class . ':gaji')->group(function() {
        Route::get('', [GajiController::class, 'calculateAll']);
        Route::get('/periods', [GajiController::class, 'availablePeriods']);
        Route::put('{id}/status', [GajiController::class, 'updateStatus']);
        Route::get('/export', [GajiController::class, 'export']);
    });

    // Potongan gaji
    Route::prefix('potongan_gaji')->middleware(CheckFitur::class . ':potongan_gaji')->group(function() {
        Route::get('',[PotonganGajiController::class, 'index']);
        Route::post('',[PotonganGajiController::class, 'store']);
        Route::put('{id}',[PotonganGajiController::class, 'update']);
        Route::delete('{id}',[PotonganGajiController::class, 'destroy']);
    });

    // Kantor Routes
    Route::prefix('kantor')->middleware(CheckFitur::class . ':kantor')->group(function() {
        Route::get('',[KantorController::class, 'index']);
        Route::post('',[KantorController::class, 'saveProfile']);
    });

    // Absensi Route
    Route::prefix('absensi')->middleware(CheckFitur::class . ':absensi')->group(function() {
        Route::get('',[AbsensiController::class, 'getAbsensi']);
        Route::post('checkin',[AbsensiController::class, 'checkin']);
        Route::post('checkout',[AbsensiController::class, 'checkout']);
    });

    // Log Activity Route
    Route::prefix('log')->middleware(CheckFitur::class . ':log_aktifitas')->group(function() {
        Route::get('', [ActivityLogController::class, 'index']);
    });

    // Pengingat Route
    Route::prefix('pengingat')->middleware(CheckFitur::class . ':pengingat')->group(function() {
        Route::get('', [PengingatController::class, 'index']);
        Route::post('', [PengingatController::class, 'store']);
        Route::put('{id}', [PengingatController::class, 'update']);
        Route::delete('{id}', [PengingatController::class, 'destroy']);
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
    Route::get('kantor/jam',[KantorController::class, 'index']);
    Route::get('/fitur', [FiturController::class, 'index']);
});



















// ini untuk debug email di hostingan
Route::get('/scheduler-log', function () {
    $file = storage_path('logs/scheduler_output.log');
    if (!file_exists($file)) {
        return "Belum ada log scheduler.";
    }
    return nl2br(file_get_contents($file));
});
