<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class WaliKelas extends Authenticatable
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
        'kelas',
        'no_hp',
        'foto',
    ];

    protected $hidden = [
        'password',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Only query users with role=wali_kelas
        static::addGlobalScope('wali_kelas_role', function ($query) {
            $query->where('role', 'wali_kelas');
        });

        // Automatically set role to wali_kelas when creating
        static::creating(function ($model) {
            $model->role = 'wali_kelas';
        });
    }

    /**
     * Get the class (kelas) this wali_kelas advises
     */
    public function kelasAdvisee()
    {
        return $this->belongsTo(Kelas::class, 'kelas', 'id');
    }
}
