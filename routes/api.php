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

    // Lembur routes
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::put('/lembur/{id}/approve', [LemburController::class, 'approve']);
    Route::put('/lembur/{id}/decline', [LemburController::class, 'decline']);
    Route::put('/lembur/{id}', [LemburController::class, 'update']);
    Route::delete('/lembur/{id}', [LemburController::class, 'destroy']);

    // Cuti routes
    Route::get('/cuti', [CutiController::class, 'index']);
    Route::post('/cuti', [CutiController::class, 'store']);
    Route::put('/cuti/{id}/approve', [CutiController::class, 'approve']);
    Route::put('/cuti/{id}/decline', [CutiController::class, 'decline']);
    Route::put('/cuti/{id}', [CutiController::class, 'update']);
    Route::delete('/cuti/{id}', [CutiController::class, 'destroy']);

    // Tugas routes
    Route::get('/tugas', [TugasController::class, 'index']);
    Route::post('/tugas', [TugasController::class, 'store']);
    Route::put('/tugas/{id}', [TugasController::class, 'update']);
    Route::delete('/tugas/{id}', [TugasController::class, 'destroy']);

    // Departemen routes
    route::get('/departemen', [DepartemenController::class, 'index']);
    route::post('/departemen', [DepartemenController::class, 'store']);
    route::put('/departemen/{id}', [DepartemenController::class, 'update']);
    route::delete('/departemen/{id}', [DepartemenController::class, 'destroy']);

    // Peran routes //note mungkin belum bisa di kerjakan sekedar membuat controler dan route nunggu  perancangan fitur dan izin fitur
    route::get('/peran', [PeranController::class, 'index']);
    route::post('/peran', [PeranController::class, 'store']);
    route::put('/peran/{id}', [PeranController::class, 'update']);
    route::delete('/peran/{id}', [PeranController::class, 'destroy']);

    // Jabatan routes
    route::get('/jabatan', [JabatanController::class, 'index']);
    route::post('/jabatan', [JabatanController::class, 'store']);
    route::put('/jabatan/{id}', [JabatanController::class, 'update']);
    route::delete('/jabatan/{id}', [JabatanController::class, 'destroy']);

    // User routes
    route::get('/user', [UserController::class, 'index']);
    route::post('/user', [UserController::class, 'store']);
    route::put('/user/{id}',[UserController::class, 'update']);
    route::delete('/user/{id}',[UserController::class, 'destroy']);

    // Gaji routes
    Route::post('/gaji/generate', [GajiController::class, 'generateGaji']);
    Route::get('/gaji/list', [GajiController::class, 'listGaji']);

    // Potongan gaji
    route::get('/potongan_gaji',[PotonganGajiController::class, 'index']);
    route::post('/potongan_gaji',[PotonganGajiController::class, 'store']);
    route::put('/potongan_gaji/{id}',[PotonganGajiController::class, 'update']);
    route::delete('/potongan_gaji/{id}',[PotonganGajiController::class, 'destroy']);




});
