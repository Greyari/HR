<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'checkin_lat',
        'checkin_lng',
        'checkin_date',
        'checkin_time',
        'status',
        'video_user',
        'checkout_lat',
        'checkout_lng',
        'checkout_date',
        'checkout_time',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Tugas (optional)
    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }
}
