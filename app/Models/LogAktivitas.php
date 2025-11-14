<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'activity_log';
    protected $fillable = [
        'user_id',
        'action',       // sebelumnya 'aksi'
        'description',  // sebelumnya 'deskripsi'
        'created_at'
    ];
}
