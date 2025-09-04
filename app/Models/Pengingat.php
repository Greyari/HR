<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
