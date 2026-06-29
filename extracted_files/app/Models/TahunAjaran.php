<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all periode nilai for this tahun ajaran (both semesters).
     */
    public function periodes()
    {
        return $this->hasMany(PeriodeNilai::class, 'tahun_ajaran_id');
    }

    /**
     * Get the periode for a specific semester.
     */
    public function periodeForSemester($semester)
    {
        return $this->periodes()->where('semester', $semester)->first();
    }
}
