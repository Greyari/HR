<?php

use App\Http\Controllers\Api\GajiController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\PeranController;
use App\Http\Controllers\Api\PotonganGajiController;
use App\Http\Controllers\Api\TugasController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CutiController;
use App\Http\Controllers\Api\DepartemenController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Profile Route
    Route::put('/email',[AuthController::class, 'updateEmail']);
    // nenti buat route untuk ubah password dari email yang terdaftar (ubah paswordnya ada di kirim link ke email)

    // Lembur Routes
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::put('/lembur/{id}/approve', [LemburController::class, 'approve']);
    Route::put('/lembur/{id}/decline', [LemburController::class, 'decline']);
    Route::put('/lembur/{id}', [LemburController::class, 'update']);
    Route::delete('/lembur/{id}', [LemburController::class, 'destroy']);

    // Cuti Routes
    Route::get('/cuti', [CutiController::class, 'index']);
    Route::post('/cuti', [CutiController::class, 'store']);
    Route::put('/cuti/{id}/approve', [CutiController::class, 'approve']);
    Route::put('/cuti/{id}/decline', [CutiController::class, 'decline']);
    Route::put('/cuti/{id}', [CutiController::class, 'update']);
    Route::delete('/cuti/{id}', [CutiController::class, 'destroy']);

    // Tugas Routes Admin
    Route::get('/tugas', [TugasController::class, 'index']);
    Route::post('/tugas', [TugasController::class, 'store']);
    Route::put('/tugas/{id}', [TugasController::class, 'update']);
    Route::delete('/tugas/{id}', [TugasController::class, 'destroy']);

    // Tugas Route Karyawan (sudah termasuk dalam upload dan edit)
    Route::post('/tugas/{id}/upload-file', [TugasController::class, 'uploadLampiran']);

    // Departemen Routes
    Route::get('/departemen', [DepartemenController::class, 'index']);
    Route::post('/departemen', [DepartemenController::class, 'store']);
    Route::put('/departemen/{id}', [DepartemenController::class, 'update']);
    Route::delete('/departemen/{id}', [DepartemenController::class, 'destroy']);

    // Peran Routes //note mungkin belum bisa di kerjakan sekedar membuat controler dan Route nunggu  perancangan fitur dan izin fitur
    Route::get('/peran', [PeranController::class, 'index']);
    Route::post('/peran', [PeranController::class, 'store']);
    Route::put('/peran/{id}', [PeranController::class, 'update']);
    Route::delete('/peran/{id}', [PeranController::class, 'destroy']);

    // Jabatan Routes
    Route::get('/jabatan', [JabatanController::class, 'index']);
    Route::post('/jabatan', [JabatanController::class, 'store']);
    Route::put('/jabatan/{id}', [JabatanController::class, 'update']);
    Route::delete('/jabatan/{id}', [JabatanController::class, 'destroy']);

    // User Routes
    Route::get('/user', [UserController::class, 'index']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}',[UserController::class, 'update']);
    Route::delete('/user/{id}',[UserController::class, 'destroy']);

    // Gaji Routes
    Route::post('/gaji/generate', [GajiController::class, 'generateGaji']);
    Route::get('/gaji/list', [GajiController::class, 'listGaji']);

    // Potongan gaji
    Route::get('/potongan_gaji',[PotonganGajiController::class, 'index']);
    Route::post('/potongan_gaji',[PotonganGajiController::class, 'store']);
    Route::put('/potongan_gaji/{id}',[PotonganGajiController::class, 'update']);
    Route::delete('/potongan_gaji/{id}',[PotonganGajiController::class, 'destroy']);

});
