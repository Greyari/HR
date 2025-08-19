<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    protected $table = 'gaji';

    protected $fillable = [
        'user_id',
        'bulan',
        'tahun',
        'gaji_pokok',
        'total_lembur',
        'gaji_bersih',
    ];

    // Relasi ke User (karyawan)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Potongan (many-to-many via pivot)
    public function potongan()
    {
        return $this->belongsToMany(PotonganGaji::class, 'gaji_potongan', 'gaji_id', 'potongan_gaji_id')
                    ->withPivot('nominal') // ambil juga nilai nominal dari pivot
                    ->withTimestamps();
    }
}
