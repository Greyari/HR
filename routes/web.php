<?php

use App\Http\Controllers\Api\ForgotPasswordController;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

// landing page
Route::get('/', function () {
    return response()->json(['message' => 'Untuk api ke flutter hris']);
});

// Route lupa kata sandi
Route::get('url-reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
Route::get('reset-success', [ForgotPasswordController::class, 'pagesucces'])->name('password.reset.success');

// Route::get('/test-firebase', function () {
//     $messaging = app('firebase.messaging');
//     return response()->json(['status' => 'ok']);
// });

// // testing view email
// Route::get('/pengingat', function () {
//     $pengingat = (object) [
//         'status'     => 'Dummy status Pengingat',
//         'judul'      => 'Dummy Judul Pengingat',
//         'isi'        => 'Ini contoh isi pengingat untuk preview email.',
//         'deskripsi'  => 'Ini deskripsi tambahan supaya tidak error.',
//         'tanggal_jatuh_tempo' => Carbon::parse('2025-10-10 14:30:00'),
//         'peran'      => (object) [
//             'nama_peran' => 'Dummy nama_peran Pengingat'
//         ],
//         'sisa_waktu' => '2 hari 3 jam'
//     ];

//     return view('emails.pengingat', compact('pengingat'));
// });
// Route::get('/reset-success', function () {
//     return view('emails.reset-password',[
//         'url' => 'dummy-preview-url'
//     ]);
// });

// Route::get('/form', function () {
//     return view('form.reset-password',[
//         'token' => 'dummy-preview-token',
//         'email' => 'dummy-preview-email'
//     ]);
// });

// Route::get('/form-succes', function () {
//     return view('form.succes-reset-password',[
//     ]);
// });
