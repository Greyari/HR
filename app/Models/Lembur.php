<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'deskripsi',
        'status',
        'approval_step',
    ];

    protected $appends = ['keterangan_status'];

    // Relasi ke tabel User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Akses keterangan status berdasarkan approval_step
     */
    public function getKeteranganStatusAttribute()
    {
        switch ($this->approval_step) {
            case 0:
                return "Menunggu diproses Admin Office";
            case 1:
                return "Menunggu persetujuan Super Admin";
            case 2:
                return "Lembur disetujui, Anda dapat melakukan lembur";
            case 3:
                return "Lembur Anda ditolak, silahkan ajukan lembur kembali";
            default:
                return "Status tidak diketahui";
        }
    }
}
