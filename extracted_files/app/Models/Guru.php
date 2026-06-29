<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Guru extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'nama',
        'role',
        'nip',
        'nuptk',
        'mata_pelajaran_id',
        'no_hp',
        'foto',
    ];

    protected $hidden = [
        'password',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Only query users with role=guru
        static::addGlobalScope('guru_role', function ($query) {
            $query->where('role', 'guru');
        });

        // Automatically set role to guru when creating
        static::creating(function ($model) {
            $model->role = 'guru';
        });
    }

    /**
     * Get the subject (mata pelajaran) this guru teaches
     */
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id', 'id');
    }
}
