<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotonganGaji extends Model
{
    use HasFactory;

    protected $table = 'potongan_gaji';

    protected $fillable = [
        'nama_potongan',
        'nominal',
    ];

    // Relasi ke Gaji
    public function gaji()
    {
        return $this->belongsToMany(Gaji::class, 'gaji_potongan', 'potongan_gaji_id', 'gaji_id')
                    ->withPivot('nominal')
                    ->withTimestamps();
    }
}
