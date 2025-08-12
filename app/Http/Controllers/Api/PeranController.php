<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peran;
use Illuminate\Http\Request;

class PeranController extends Controller
{
    // List semua peran
    public function index()
    {
        $perans = Peran::all();

        return response()->json([
            'message' => 'Data peran berhasil diambil',
            'data' => $perans
        ]);
    }

    // Simpan peran baru
    

}
