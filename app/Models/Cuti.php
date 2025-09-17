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
        'status',
        'approval_step',
    ];

    // Supaya accessor ikut muncul di JSON
    protected $appends = ['keterangan_status'];

    /**
     * Relasi ke User
     */
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
                return "Menunggu diproses tahap awal";
            case 1:
                return "Menunggu persetujuan final";
            case 2:
                return "Cuti anda telah disetujui";
            case 3:
                return "Cuti Anda ditolak, silahkan ajukan cuti kembali";
            default:
                return "Status tidak diketahui";
        }
    }
}
