<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;

    protected $table = 'departemen';

    protected $fillable = ['nama_departemen'];

    public function users()
    {
        return $this->hasMany(User::class, 'departemen_id');
    }
}
