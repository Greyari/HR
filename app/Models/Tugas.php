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
        'departemen_id',
        'nama_tugas',
        'jam_mulai',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'Note',
    ];

    // Relasi ke tabel user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke tabel departemen
    public function department()
    {
        return $this->belongsTo(Departemen::class);
    }
}
