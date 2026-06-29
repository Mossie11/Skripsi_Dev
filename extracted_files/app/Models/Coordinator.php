<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Coordinator extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'nama',
        'role',
        'no_hp',
        'foto',
    ];

    protected $hidden = [
        'password',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Only query users with role=coordinator
        static::addGlobalScope('coordinator_role', function ($query) {
            $query->where('role', 'coordinator');
        });

        // Automatically set role to coordinator when creating
        static::creating(function ($model) {
            $model->role = 'coordinator';
        });
    }
}
