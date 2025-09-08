<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Pengingat extends Model
{
    use HasFactory;

    protected $table = 'pengingat';

    protected $casts = [
        'tanggal_jatuh_tempo' => 'datetime',
        'last_notified_at' => 'datetime',
    ];

    protected $fillable = [
        'peran_id',
        'judul',
        'deskripsi',
        'tanggal_jatuh_tempo',
        'mengulang',
        'status',
    ];

    public function peran()
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }


    public function getSisaWaktuAttribute()
    {
        $now = Carbon::now();
        $jatuhTempo = $this->tanggal_jatuh_tempo;

        // kalau sudah lewat
        if ($jatuhTempo->isPast()) {
            return $jatuhTempo->diffForHumans($now, [
                'parts' => 2,      // batasi detail (misal: "2 hari 3 jam yang lalu")
                'short' => false,  // pakai format panjang
                'syntax' => Carbon::DIFF_RELATIVE_TO_NOW
            ]);
        }

        // kalau masih ada sisa waktu
        return $now->diffForHumans($jatuhTempo, [
            'parts' => 2,      // batasi detail
            'short' => false,
            'syntax' => Carbon::DIFF_ABSOLUTE
        ]);
    }
}
