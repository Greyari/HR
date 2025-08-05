<?php

use App\Http\Controllers\DepartemenController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

//departemen
route::get('/admin/departemen', [DepartemenController::class, 'show'])->name('departemen');
Route::post('/admin/departemen', [DepartemenController::class, 'store'])->name('departemen.store');
Route::delete('/departemen/{id}', [DepartemenController::class, 'destroy'])->name('departemen.destroy');
Route::put('/departemen/{id}', [DepartemenController::class, 'update'])->name('departemen.update');
Route::get('/admin/departemen/search', [DepartemenController::class, 'search'])->name('departemen.search');


Route::get('/debug-db', function () {
    return [
        'host' => env('DB_HOST'),
        'port' => env('DB_PORT'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'database' => env('DB_DATABASE'),
    ];
});
