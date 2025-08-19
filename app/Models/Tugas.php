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
        'jam_mulai',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'instruksi_tugas',
        'status',
        'bukti_video',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
