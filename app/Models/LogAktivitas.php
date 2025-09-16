<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'activity_log';
    protected $fillable = [
        'user_id', 
        'aksi', 
        'deskripsi', 
        'created_at'
    ];
}
