<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;

    protected $table = 'departemen';

    protected $fillable = ['nama_departemen'];

    // Relasi ke tabel User
    public function users()
    {
        return $this->hasMany(User::class, 'departemen_id');
    }

    // Relasi ke tabel Tugas
    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'departemen_id');
    }

}
