<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kantor extends Model
{
    use HasFactory;

    protected $table = 'kantor';

    protected $fillable = [
        'jam_masuk',
        'jam_keluar',
        'minimal_keterlambatan',
        'lat',
        'lng',
        'radius_meter',
    ];
}
