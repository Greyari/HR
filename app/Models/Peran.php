<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    use HasFactory;

    protected $table = 'peran';

    protected $fillable = ['nama_peran'];

    // Relasi ke User
    public function users()
    {
        return $this->hasMany(User::class, 'peran_id');
    }

    // Relasi many-to-many dengan fitur
    public function fitur()
    {
        return $this->belongsToMany(Fitur::class, 'izin_fitur', 'peran_id', 'fitur_id');
    }
}
