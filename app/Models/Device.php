<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'device';

    protected $fillable = [
        'user_id',
        'device_id',
        'last_login'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
