<?php

use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\TugasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CutiController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);


    // Lembur routes
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::put('/lembur/{id}/approve', [LemburController::class, 'approve']);
    Route::put('/lembur/{id}/decline', [LemburController::class, 'decline']);

    // Cuti routes
    Route::get('/cuti', [CutiController::class, 'index']);
    Route::post('/cuti', [CutiController::class, 'store']);
    Route::put('/cuti/{id}/approve', [CutiController::class, 'approve']);
    Route::put('/cuti/{id}/decline', [CutiController::class, 'decline']);

    // Tugas routes
    Route::get('/tugas', [TugasController::class, 'index']);
    Route::post('/tugas', [TugasController::class, 'store']);
    Route::put('/tugas/{id}', [TugasController::class, 'update']);
    Route::delete('/tugas/{id}', [TugasController::class, 'destroy']);
});
