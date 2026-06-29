<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated nilai_siswa table has been removed.
 * Use individual tables instead: nilai_tugas1, nilai_tugas2, nilai_uh1, nilai_uh2, nilai_uts, nilai_uas
 */
class NilaiSiswa extends Model
{
    use HasFactory;

    // Table no longer exists - model is deprecated
    protected $table = 'nilai_siswa_deprecated';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
