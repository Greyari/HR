<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fitur;
use Illuminate\Http\Request;

class FiturController extends Controller
{
    // Ambil semua fitur
    public function index()
    {
        $fiturs = Fitur::all();
        return response()->json([
            'status' => 'success',
            'data' => $fiturs
        ]);
    }
}
