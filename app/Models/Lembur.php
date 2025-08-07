<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'deskripsi',
        'status',
    ];

    public function karyawan()
    {
        return $this->belongsTo(User::class, 'karyawan_id');
    }
}
