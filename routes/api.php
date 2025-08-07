<?php

use App\Http\Controllers\Api\LemburController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);


    // Lembur routes
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::patch('/lembur/{id}/status', [LemburController::class, 'updateStatus']);
});
