<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeNilai extends Model
{
    use HasFactory;

    protected $table = 'periode_nilai';

    protected $guarded = ['id'];
    
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /**
     * Auto-detect the active semester for a given tahun ajaran
     * based on which semester's periods contain today's date.
     * Falls back to semester 1 (Ganjil) if no periods are active.
     */
    public static function detectActiveSemester($tahunAjaranId)
    {
        $today = date('Y-m-d');
        
        // Check semester 2 (Genap) first — if any period in semester 2 
        // contains today or is in the future, we might be in semester 2
        $periodes = self::where('tahun_ajaran_id', $tahunAjaranId)->get();
        
        // Check if today falls within any period of semester 2
        $sem2 = $periodes->where('semester', '2')->first();
        if ($sem2) {
            $types = ['uh1', 'uh2', 'uts', 'uas'];
            foreach ($types as $type) {
                $start = $sem2->{$type . '_start'};
                $end = $sem2->{$type . '_end'};
                if ($start && $end && $today >= $start && $today <= $end) {
                    return '2';
                }
            }
            // If sem2 has dates set and we're past sem1's last end date
            $sem1 = $periodes->where('semester', '1')->first();
            if ($sem1 && $sem2) {
                $sem1LastEnd = max(
                    $sem1->uh1_end ?? '0000-00-00',
                    $sem1->uts_end ?? '0000-00-00',
                    $sem1->uh2_end ?? '0000-00-00',
                    $sem1->uas_end ?? '0000-00-00'
                );
                $sem2FirstStart = min(
                    $sem2->uh1_start ?: '9999-12-31',
                    $sem2->uts_start ?: '9999-12-31',
                    $sem2->uh2_start ?: '9999-12-31',
                    $sem2->uas_start ?: '9999-12-31'
                );
                if ($sem1LastEnd !== '0000-00-00' && $sem2FirstStart !== '9999-12-31' && $today > $sem1LastEnd) {
                    return '2';
                }
            }
        }
        
        // Check if today falls within any period of semester 1
        $sem1 = $periodes->where('semester', '1')->first();
        if ($sem1) {
            $types = ['uh1', 'uh2', 'uts', 'uas'];
            foreach ($types as $type) {
                $start = $sem1->{$type . '_start'};
                $end = $sem1->{$type . '_end'};
                if ($start && $end && $today >= $start && $today <= $end) {
                    return '1';
                }
            }
        }
        
        // Default to semester 1 (Ganjil)
        // If no periods are set, detect by month
        $month = (int) date('m');
        if (in_array($month, [7, 8, 9, 10, 11, 12])) {
            return '1';
        } else {
            return '2';
        }
    }

    /**
     * Check if the current date is within the specified period type for the auto-detected semester.
     * types: uh1, tugas1, uh2, tugas2, uts, uas
     */
    public static function isOpen($tahunAjaranId, $type)
    {
        $semester = self::detectActiveSemester($tahunAjaranId);
        return self::isOpenForSemester($tahunAjaranId, $semester, $type);
    }

    /**
     * Check if the current date is within the specified period type for a specific semester.
     */
    public static function isOpenForSemester($tahunAjaranId, $semester, $type)
    {
        $periode = self::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->first();
        if (!$periode) return false;

        $today = date('Y-m-d');
        
        // Map tugas to uh
        if ($type === 'tugas1') $type = 'uh1';
        if ($type === 'tugas2') $type = 'uh2';

        $startField = $type . '_start';
        $endField = $type . '_end';

        $start = $periode->$startField;
        $end = $periode->$endField;

        if (!$start || !$end) return false;

        return ($today >= $start && $today <= $end);
    }

    /**
     * Check if ANY of the main assessment periods (uh1, uh2, uts, uas) are open
     * across both semesters.
     */
    public static function isAnyOpen($tahunAjaranId)
    {
        $types = ['uh1', 'uh2', 'uts', 'uas'];
        $semesters = ['1', '2'];
        
        foreach ($semesters as $semester) {
            foreach ($types as $type) {
                if (self::isOpenForSemester($tahunAjaranId, $semester, $type)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if the global system-wide grades and attendance progress is complete (100%).
     */
    public static function isSystemProgressComplete($tahunAjaranId)
    {
        $activeTahun = \Illuminate\Support\Facades\DB::table('tahun_ajaran')->where('id', $tahunAjaranId)->first();
        if (!$activeTahun) return false;
        
        $activeTahunName = $activeTahun->nama;
        
        // 1. Check Grades (Penilaian) Progress
        $assignments = \Illuminate\Support\Facades\DB::table('jadwal')
            ->where('tahun_ajaran', $activeTahunName)
            ->select('kelas_id', 'mata_pelajaran_id')
            ->distinct()
            ->get();
            
        foreach ($assignments as $asm) {
            $siswaCount = \Illuminate\Support\Facades\DB::table('siswa')
                ->where('kelas_id', $asm->kelas_id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->count();
            if ($siswaCount === 0) continue;
            
            $expected = $siswaCount * 6; // 6 assessment types: tugas1, uh1, tugas2, uh2, uts, uas
            
            $siswaIds = \Illuminate\Support\Facades\DB::table('siswa')
                ->where('kelas_id', $asm->kelas_id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->pluck('id')
                ->toArray();
                
            $completed = \Illuminate\Support\Facades\DB::table('penilaian')
                ->where('mata_pelajaran_id', $asm->mata_pelajaran_id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->whereIn('siswa_id', $siswaIds)
                ->whereIn('assessment_type', ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'])
                ->whereNotNull('nilai')
                ->count();
                
            if ($completed < $expected) {
                return false;
            }
        }
        
        // 2. Check Attendance (Kehadiran) Progress
        $classes = \Illuminate\Support\Facades\DB::table('siswa')
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('kelas_id')
            ->distinct()
            ->pluck('kelas_id')
            ->toArray();
            
        $startYear = substr($activeTahunName, 0, 4) ?: date('Y');
        $endYear = substr($activeTahunName, 5, 4) ?: (date('Y') + 1);
        $dateRanges = [
            ["{$startYear}-07-01", "{$startYear}-08-31"],
            ["{$startYear}-09-01", "{$startYear}-09-30"],
            ["{$startYear}-10-01", "{$startYear}-11-30"],
            ["{$startYear}-12-01", "{$startYear}-12-31"],
            ["{$endYear}-01-01", "{$endYear}-02-28"],
            ["{$endYear}-03-01", "{$endYear}-03-31"],
            ["{$endYear}-04-01", "{$endYear}-05-31"],
            ["{$endYear}-06-01", "{$endYear}-06-30"]
        ];
        $schoolDays = 0;
        foreach ($dateRanges as $range) {
            $schoolDays += self::calculateSchoolDays($range[0], $range[1]);
        }
        
        foreach ($classes as $kelasId) {
            $siswaCount = \Illuminate\Support\Facades\DB::table('siswa')
                ->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->count();
            if ($siswaCount === 0) continue;
            
            $expected = $siswaCount * $schoolDays;
            
            $siswaIds = \Illuminate\Support\Facades\DB::table('siswa')
                ->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->pluck('id')
                ->toArray();
                
            $completed = \Illuminate\Support\Facades\DB::table('kehadiran')
                ->whereIn('siswa_id', $siswaIds)
                ->where('kelas_id', $kelasId)
                ->where(function($q) use ($dateRanges) {
                    foreach ($dateRanges as $range) {
                        $q->orWhereBetween('tanggal', [$range[0], $range[1]]);
                    }
                })
                ->count();
                
            if ($completed < $expected) {
                return false;
            }
        }
        
        return true;
    }

    public static function detectActivePeriodType($tahunAjaranId)
    {
        $semester = self::detectActiveSemester($tahunAjaranId);
        $periode = self::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->first();

        $today = date('Y-m-d');
        $types = ['uh1', 'uts', 'uh2', 'uas'];

        if (!$periode) {
            // If no periods are set in database, detect dynamically by current month
            $month = (int) date('m');
            if (in_array($month, [7, 8])) return 'uh1';
            if ($month === 9) return 'uts';
            if (in_array($month, [10, 11])) return 'uh2';
            if ($month === 12) return 'uas';
            
            if (in_array($month, [1, 2])) return 'uh1';
            if ($month === 3) return 'uts';
            if (in_array($month, [4, 5])) return 'uh2';
            if ($month === 6) return 'uas';
            
            return 'uh1';
        }

        // 1. Check if today falls within any period
        foreach ($types as $type) {
            $start = $periode->{$type . '_start'};
            $end = $periode->{$type . '_end'};
            if ($start && $end && $today >= $start && $today <= $end) {
                return $type;
            }
        }

        // 2. Find the nearest upcoming period
        $nearestUpcoming = null;
        $nearestDist = PHP_INT_MAX;
        foreach ($types as $type) {
            $start = $periode->{$type . '_start'};
            if ($start && $start > $today) {
                $dist = strtotime($start) - strtotime($today);
                if ($dist < $nearestDist) {
                    $nearestDist = $dist;
                    $nearestUpcoming = $type;
                }
            }
        }
        if ($nearestUpcoming) return $nearestUpcoming;

        // 3. Fall back to the last completed period
        $lastCompleted = null;
        $lastEndDate = '0000-00-00';
        foreach ($types as $type) {
            $end = $periode->{$type . '_end'};
            if ($end && $end < $today && $end > $lastEndDate) {
                $lastEndDate = $end;
                $lastCompleted = $type;
            }
        }
        if ($lastCompleted) return $lastCompleted;

        // 4. Default to uh1
        return 'uh1';
    }

    /**
     * Get the actual date range [start, end] for a given period type
     * from the periode_nilai record. Returns default ranges if not set.
     */
    public static function getDateRangeForType($tahunAjaranId, $semester, $type)
    {
        $periode = self::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->first();

        // Map tugas to uh for date ranges
        $cleanType = $type;
        if ($cleanType === 'tugas1') $cleanType = 'uh1';
        if ($cleanType === 'tugas2') $cleanType = 'uh2';

        if (!$periode) {
            // Return default date ranges dynamically based on academic year name
            $activeTahun = \Illuminate\Support\Facades\DB::table('tahun_ajaran')->where('id', $tahunAjaranId)->first();
            if (!$activeTahun) return null;
            $parts = explode('/', $activeTahun->nama);
            $startYear = isset($parts[0]) ? (int)$parts[0] : (int)date('Y');
            $endYear = isset($parts[1]) ? (int)$parts[1] : $startYear + 1;

            if ($semester === '1') {
                $defaults = [
                    'uh1' => ["{$startYear}-07-01", "{$startYear}-08-31"],
                    'uts' => ["{$startYear}-09-01", "{$startYear}-09-30"],
                    'uh2' => ["{$startYear}-10-01", "{$startYear}-11-30"],
                    'uas' => ["{$startYear}-12-01", "{$startYear}-12-31"],
                ];
            } else {
                $defaults = [
                    'uh1' => ["{$endYear}-01-01", "{$endYear}-02-28"],
                    'uts' => ["{$endYear}-03-01", "{$endYear}-03-31"],
                    'uh2' => ["{$endYear}-04-01", "{$endYear}-05-31"],
                    'uas' => ["{$endYear}-06-01", "{$endYear}-06-30"],
                ];
            }

            return $defaults[$cleanType] ?? null;
        }

        $start = $periode->{$cleanType . '_start'};
        $end = $periode->{$cleanType . '_end'};

        if (!$start || !$end) {
            // Return dynamic fallback if specific dates are not saved yet
            $activeTahun = \Illuminate\Support\Facades\DB::table('tahun_ajaran')->where('id', $tahunAjaranId)->first();
            if (!$activeTahun) return null;
            $parts = explode('/', $activeTahun->nama);
            $startYear = isset($parts[0]) ? (int)$parts[0] : (int)date('Y');
            $endYear = isset($parts[1]) ? (int)$parts[1] : $startYear + 1;

            if ($semester === '1') {
                $defaults = [
                    'uh1' => ["{$startYear}-07-01", "{$startYear}-08-31"],
                    'uts' => ["{$startYear}-09-01", "{$startYear}-09-30"],
                    'uh2' => ["{$startYear}-10-01", "{$startYear}-11-30"],
                    'uas' => ["{$startYear}-12-01", "{$startYear}-12-31"],
                ];
            } else {
                $defaults = [
                    'uh1' => ["{$endYear}-01-01", "{$endYear}-02-28"],
                    'uts' => ["{$endYear}-03-01", "{$endYear}-03-31"],
                    'uh2' => ["{$endYear}-04-01", "{$endYear}-05-31"],
                    'uas' => ["{$endYear}-06-01", "{$endYear}-06-30"],
                ];
            }

            return $defaults[$cleanType] ?? null;
        }

        return [$start, $end];
    }

    /**
     * Calculate the number of weekdays (Mon-Fri) between two dates (inclusive).
     */
    public static function calculateSchoolDays($startDate, $endDate)
    {
        if (!$startDate || !$endDate) return 0;

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end->modify('+1 day'); // make end inclusive

        $count = 0;
        $current = clone $start;
        while ($current < $end) {
            $dow = (int) $current->format('N'); // 1=Monday, 7=Sunday
            if ($dow >= 1 && $dow <= 5) {
                $count++;
            }
            $current->modify('+1 day');
        }

        return $count;
    }

    /**
     * Map a filter type to the assessment types it represents.
     * uh1 → ['tugas1', 'uh1'], uts → ['uts'], uh2 → ['tugas2', 'uh2'], uas → ['uas']
     * all → all 6 types
     */
    public static function getAssessmentTypesForFilter($filterType)
    {
        $map = [
            'uh1' => ['tugas1', 'uh1'],
            'uts' => ['uts'],
            'uh2' => ['tugas2', 'uh2'],
            'uas' => ['uas'],
        ];

        if (isset($map[$filterType])) {
            return $map[$filterType];
        }

        // 'all' or unknown → all types
        return ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];
    }

    /**
     * Get a human-readable label for a period type.
     */
    public static function getPeriodLabel($type)
    {
        $labels = [
            'uh1' => 'UH1',
            'uts' => 'UTS',
            'uh2' => 'UH2',
            'uas' => 'Rapor Akhir',
        ];
        return $labels[$type] ?? strtoupper($type);
    }

    /**
     * Check if a student's religion matches a subject name.
     */
    public static function matchesReligion($siswaAgama, $mapelNama)
    {
        $mapelUpper = strtoupper($mapelNama);
        if (!str_starts_with($mapelUpper, 'PEND. AGAMA')) {
            return true;
        }
        $agama = strtolower($siswaAgama ?? '');
        $mapelLower = strtolower($mapelNama);
        if ($agama === 'islam' && str_contains($mapelLower, 'islam')) return true;
        if ($agama === 'kristen' && str_contains($mapelLower, 'kristen')) return true;
        if ($agama === 'katolik' && str_contains($mapelLower, 'katolik')) return true;
        if ($agama === 'hindu' && str_contains($mapelLower, 'hindu')) return true;
        if ($agama === 'budha' && str_contains($mapelLower, 'buddha')) return true;
        if ($agama === 'buddha' && str_contains($mapelLower, 'buddha')) return true;
        if ($agama === 'konghucu' && str_contains($mapelLower, 'konghucu')) return true;
        return false;
    }

    /**
     * Check if the progress for a specific class (attendance and grades) is complete (100%).
     */
    public static function isClassProgressComplete($kelasId, $periodType, $tahunAjaranId, $semester = null)
    {
        $activeTahun = \Illuminate\Support\Facades\DB::table('tahun_ajaran')->where('id', $tahunAjaranId)->first();
        if (!$activeTahun) return false;

        $activeTahunName = $activeTahun->nama;
        $activeSemester = $semester ?: self::detectActiveSemester($tahunAjaranId);

        // 1. Check Grades (Penilaian) Completeness for this class
        $mapelsInKelas = \Illuminate\Support\Facades\DB::table('jadwal')
            ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->where('jadwal.kelas_id', $kelasId)
            ->where('jadwal.tahun_ajaran', $activeTahunName)
            ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
            ->distinct()
            ->get();

        $siswaInKelas = \Illuminate\Support\Facades\DB::table('siswa')
            ->where('siswa.kelas_id', $kelasId)
            ->where('siswa.tahun_ajaran_id', $tahunAjaranId)
            ->select('siswa.id', 'siswa.agama')
            ->get();

        if ($siswaInKelas->isEmpty()) return false; // No students in class, cannot be complete
        if ($mapelsInKelas->isEmpty()) return false; // No subjects scheduled, cannot be complete

        $assessmentTypes = self::getAssessmentTypesForFilter($periodType);
        
        $expectedGrades = 0;
        $mapelIdsInKelas = [];
        $siswaIds = $siswaInKelas->pluck('id')->toArray();

        foreach ($mapelsInKelas as $mapel) {
            $mapelIdsInKelas[] = $mapel->id;
            foreach ($siswaInKelas as $siswa) {
                if (self::matchesReligion($siswa->agama, $mapel->nama_mapel)) {
                    $expectedGrades += count($assessmentTypes);
                }
            }
        }

        $completedGrades = \Illuminate\Support\Facades\DB::table('penilaian')
            ->whereIn('siswa_id', $siswaIds)
            ->whereIn('mata_pelajaran_id', $mapelIdsInKelas)
            ->whereIn('assessment_type', $assessmentTypes)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $activeSemester)
            ->whereNotNull('nilai')
            ->count();

        if ($completedGrades < $expectedGrades) {
            return false;
        }

        // 2. Check Attendance (Kehadiran) Completeness for this class
        $dateRanges = [];
        $schoolDays = 0;

        if ($periodType === 'all') {
            $types = ['uh1', 'uts', 'uh2', 'uas'];
            foreach ($types as $t) {
                $r = self::getDateRangeForType($tahunAjaranId, $activeSemester, $t);
                if ($r) {
                    $dateRanges[] = $r;
                    $schoolDays += self::calculateSchoolDays($r[0], $r[1]);
                }
            }
        } else {
            $r = self::getDateRangeForType($tahunAjaranId, $activeSemester, $periodType);
            if ($r) {
                $dateRanges[] = $r;
                $schoolDays = self::calculateSchoolDays($r[0], $r[1]);
            }
        }

        if ($schoolDays === 0) return false;

        $expectedAttendance = count($siswaIds) * $schoolDays;

        $completedAttendance = \Illuminate\Support\Facades\DB::table('kehadiran')
            ->whereIn('siswa_id', $siswaIds)
            ->where('kelas_id', $kelasId)
            ->where(function($q) use ($dateRanges) {
                foreach ($dateRanges as $range) {
                    $q->orWhereBetween('tanggal', [$range[0], $range[1]]);
                }
            })
            ->count();

        if ($completedAttendance < $expectedAttendance) {
            return false;
        }

        return true;
    }
}
