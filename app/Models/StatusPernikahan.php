<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusPernikahan extends Model
{
    use HasFactory;

    protected $fillable = ['nama_status'];

    public function users()
    {
        return $this->hasMany(User::class, 'status_pernikahan_id');
    }
}
