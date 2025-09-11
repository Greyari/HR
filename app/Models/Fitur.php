<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fitur extends Model
{
    protected $table = 'fitur';
    protected $fillable = [
        'nama_fitur',
        'deskripsi'
    ];

    // Relasi ke peran
    public function peran()
    {
        return $this->belongsToMany(Peran::class, 'izin_fitur', 'fitur_id', 'peran_id');
    }
}
