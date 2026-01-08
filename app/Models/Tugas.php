<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'user_id',
        'nama_tugas',
        'tanggal_penugasan',
        'batas_penugasan',
        'instruksi_tugas',
        'status',
        'terlambat',
        'nama_lokasi_penugasan',
        'lampiran',
        'lampiran_lat',
        'lampiran_lng',
        'waktu_upload',
        'menit_terlambat',
    ];

    protected $casts = [
        'tanggal_penugasan' => 'datetime',
        'batas_penugasan' => 'datetime',
        'waktu_upload' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
