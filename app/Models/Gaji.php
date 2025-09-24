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
        'gaji_per_hari',
        'total_lembur',
        'gaji_kotor',
        'total_kehadiran',
        'total_potongan',
        'detail_potongan',
        'gaji_bersih',
        'status'
    ];

    protected $casts = [
        'detail_potongan' => 'array',
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
