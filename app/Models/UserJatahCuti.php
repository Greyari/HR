<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserJatahCuti extends Model
{
    use HasFactory;

    protected $table = 'user_jatah_cuti';

    protected $fillable = [
        'user_id',
        'kantor_id',
        'tahun',
        'jatah',
        'terpakai',
        'sisa',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kantor()
    {
        return $this->belongsTo(Kantor::class);
    }
}
