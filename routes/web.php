<?php

use App\Http\Controllers\DepartemenController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

route::get('/departemen', [DepartemenController::class, 'show'])->name('departemen');
