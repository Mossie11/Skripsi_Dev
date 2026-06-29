<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'nama',
        'role',
        'nip',
        'nuptk',
        'mata_pelajaran_id',
        'kelas',
        'no_hp',
        'email',
        'foto',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => 'string',
        ];
    }

    /**
     * Get the user's role
     */
    public function getRoleAttribute()
    {
        return $this->attributes['role'] ?? 'guru';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is guru
     */
    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    /**
     * Check if user is wali_kelas
     */
    public function isWaliKelas(): bool
    {
        return $this->role === 'wali_kelas';
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'guru_id');
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'siswa_id');
    }
}
