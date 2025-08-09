<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    use HasFactory;

    protected $table = 'cuti';

    protected $fillable = [
        'user_id',
        'tipe_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
    ];


    // Relasi ke tabel User
    public function user() {
        return $this ->belongsTo(User::class);
    }
}
