<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'departemen_id',
        'nama_tugas',
        'jam_mulai',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'instruksi_tugas',
        'status',
    ];

    // Relasi ke tabel user
    public function users()
    {
        return $this->belongsToMany(User::class, 'tugas_user')
                    ->withPivot('status', 'laporan_user')
                    ->withTimestamps();
    }


    // Relasi ke tabel departemen
    public function department()
    {
        return $this->belongsTo(Departemen::class);
    }
}
