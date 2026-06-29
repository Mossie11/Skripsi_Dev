<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'siswa_id',
        'guru_id',
        'mata_pelajaran_id',
        'tahun_ajaran_id',
        'semester',
        'assessment_type',
        'nilai',
        'nilai_deskriptif',
    ];

    protected $casts = [
        'assessment_type' => 'string',
        'nilai' => 'integer',
        'semester' => 'integer',
        'tahun_ajaran_id' => 'integer',
        'mata_pelajaran_id' => 'integer',
    ];

    // Relationships
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    // Scope for assessment types
    public function scopeByAssessmentType($query, $type)
    {
        return $query->where('assessment_type', $type);
    }

    public function scopeByStudent($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    public function scopeByTeacher($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    public function scopeBySubject($query, $mataPelajaranId)
    {
        return $query->where('mata_pelajaran_id', $mataPelajaranId);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByAcademicYear($query, $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }
}
