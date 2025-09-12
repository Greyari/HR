<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'peran_id',
        'jabatan_id',
        'departemen_id',
        'gaji_pokok',
        'npwp',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'jenis_kelamin',
        'status_pernikahan',
        'onboarding'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // relasi ke tabel peran
    public function peran()
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    // relasi ke tabel jabatan
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    // relasi ke tabel departemen
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id');
    }

    // relasi ke tabel lembur
    public function lembur()
    {
        return $this->hasMany(Lembur::class);
    }

    // relasi ke tabel cuti
    public function cuti()
    {
        return $this->hasMany(Cuti::class);
    }

    // relasi kantor
    public function kantor()
    {
        return $this->belongsTo(Kantor::class, 'kantor_id');
    }

    // relasi jatah cuti tahunan
    public function jatahCutiTahunan()
    {
        return $this->hasMany(UserJatahCuti::class);
    }

    // relasi ke device
    public function device()
    {
        return $this->hasOne(Device::class);
    }
}
