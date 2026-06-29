<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $guarded = ['id'];
}
