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
        "device_model",
        "device_manufacturer",
        "device_version",
        'device_hash',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
