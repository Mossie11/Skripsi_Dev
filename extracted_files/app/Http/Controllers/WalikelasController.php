<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalikelasController extends Controller
{
    private function getMyKelas()
    {
        return DB::table('kelas')->where('user_walikelas_id', auth()->id())->first();
    }

    public function dashboard()
    {
        $user    = auth()->user();
        $myKelasObj = $this->getMyKelas();
        $myKelas = $myKelasObj ? $myKelasObj->nama_kelas : '';
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;

        $siswaList = [];
        $totalSiswa = $totalLaki = $totalPerempuan = 0;
        $avgHadirClass = 0;
        $jadwalData = [];

        if ($myKelasId) {
            $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
            $activeTahunId = $activeTahun ? $activeTahun->id : 1;
            $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

            $today = date('Y-m-d');
            $rows = DB::table('siswa as u')
                ->leftJoin('kehadiran as k', function($join) use ($today) {
                    $join->on('u.id', '=', 'k.siswa_id')
                         ->where('k.tanggal', '=', $today);
                })
                ->where('u.kelas_id', $myKelasId)
                ->where('u.tahun_ajaran_id', $activeTahunId)
                ->select('u.nama', 'u.nis', 'u.nisn', 'u.jenis_kelamin', 'k.status')
                ->orderBy('u.nama')
                ->get();

            $hadirCount = 0;
            foreach ($rows as $r) {
                $status = $r->status ? $r->status : 'Belum Diabsen';
                $r->status_kehadiran = $status;
                $siswaList[]         = $r;
                $totalSiswa++;
                if ($r->jenis_kelamin === 'Laki-laki') $totalLaki++;
                else $totalPerempuan++;
                
                if ($status === 'Hadir') $hadirCount++;
            }
            if ($totalSiswa > 0) $avgHadirClass = round(($hadirCount / $totalSiswa) * 100, 1);

            $jadwals = DB::table('jadwal')
                ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->where('jadwal.kelas_id', $myKelasId)
                ->where('jadwal.tahun_ajaran', $activeTahunName)
                ->select('jadwal.*', 'mata_pelajaran.nama_mapel as mata_pelajaran')
                ->get();

            foreach ($jadwals as $j) {
                $jm = substr($j->jam_mulai, 0, 5);
                if (!isset($jadwalData[$jm])) $jadwalData[$jm] = [];
                $jadwalData[$jm][strtoupper($j->hari)] = $j->mata_pelajaran;
            }
        }

        $TIME_SLOTS = [
            ['jam' => '07:30-08:10', 'jam_id' => '07:30', 'les' => 1],
            ['jam' => '08:10-08:50', 'jam_id' => '08:10', 'les' => 2],
            ['jam' => '08:50-09:30', 'jam_id' => '08:50', 'les' => 3],
            ['jam' => '09:30-09:50', 'jam_id' => null, 'les' => null, 'break' => 'ISTIRAHAT I'],
            ['jam' => '09:50-10:30', 'jam_id' => '09:50', 'les' => 4],
            ['jam' => '10:30-11:10', 'jam_id' => '10:30', 'les' => 5],
            ['jam' => '11:10-11:25', 'jam_id' => null, 'les' => null, 'break' => 'ISTIRAHAT II'],
            ['jam' => '11:25-12:05', 'jam_id' => '11:25', 'les' => 6],
            ['jam' => '12:05-12:45', 'jam_id' => '12:05', 'les' => 7],
            ['jam' => '12:45-13:15', 'jam_id' => null, 'les' => null, 'break' => 'ISTIRAHAT III'],
            ['jam' => '13:15-13:55', 'jam_id' => '13:15', 'les' => 8],
            ['jam' => '13:55-14:35', 'jam_id' => '13:55', 'les' => 9],
        ];
        $DAYS = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT'];

        return view('walikelas.dashboard', compact(
            'user', 'myKelas', 'siswaList', 'totalSiswa', 'totalLaki', 'totalPerempuan',
            'avgHadirClass', 'jadwalData', 'TIME_SLOTS', 'DAYS'
        ));
    }

    // ── Profile ────────────────────────────────────────────────
    public function profile()
    {
        $user = auth()->user();
        $myKelasObj = $this->getMyKelas();
        $user->kelas_diajar = $myKelasObj ? $myKelasObj->nama_kelas : '-';
        return view('walikelas.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'nama'   => $request->nama,
            'no_hp'  => $request->no_hp,
        ];

        if ($request->input('delete_foto') === '1') {
            $oldFoto = DB::table('users')->where('id', auth()->id())->value('foto');
            if ($oldFoto && \Storage::disk('public')->exists($oldFoto)) {
                \Storage::disk('public')->delete($oldFoto);
            }
            $data['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'profile_' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_photos', $filename, 'public');
            $data['foto'] = 'profile_photos/' . $filename;

            $oldFoto = DB::table('users')->where('id', auth()->id())->value('foto');
            if ($oldFoto && \Storage::disk('public')->exists($oldFoto)) {
                \Storage::disk('public')->delete($oldFoto);
            }
        }

        DB::table('users')->where('id', auth()->id())->update($data);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    // ── Kelas (List Siswa) ─────────────────────────────────────
    public function kelas()
    {
        $user    = auth()->user();
        $myKelasObj = $this->getMyKelas();
        $myKelas = $myKelasObj ? $myKelasObj->nama_kelas : '';
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;

        if (!$myKelasId) return back()->with('error', 'Anda belum ditugaskan ke kelas manapun.');

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        // Handle POST via method spoofing or just redirect back for form submissions
        $siswaList = DB::table('siswa')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->select('siswa.*', 'kelas.nama_kelas as kelas')
            ->where('siswa.kelas_id', $myKelasId)
            ->where('siswa.tahun_ajaran_id', $activeTahunId)
            ->orderBy('siswa.nama')
            ->get();

        return view('walikelas.kelas', compact('user', 'myKelas', 'siswaList'));
    }

    public function kelasCreate(Request $request)
    {
        $myKelasObj = $this->getMyKelas();
        if (!$myKelasObj) return back();
        $myKelasId = $myKelasObj->id;

        $request->validate([
            'nama' => 'required|string|max:255',
            'nis'  => 'nullable|string|max:20|unique:siswa,nis',
            'nisn' => 'nullable|string|max:20|unique:siswa,nisn',
        ], [
            'nis.unique'  => 'NIS sudah digunakan oleh siswa lain.',
            'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
        ]);

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        DB::table('siswa')->insert([
            'nama'            => $request->nama,
            'nis'             => $request->nis ?: null,
            'nisn'            => $request->nisn ?: null,
            'kelas_id'        => $myKelasId,
            'jenis_kelamin'   => $request->jenis_kelamin ?? 'Laki-laki',
            'no_hp'           => $request->no_hp ?? '',
            'no_hp_orangtua'  => $request->no_hp_orangtua ?? '',
            'tempat_lahir'          => $request->tempat_lahir ?? '',
            'tanggal_lahir'   => $request->tanggal_lahir ?: null,
            'tahun_ajaran_id' => $activeTahunId,
        ]);
        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function kelasUpdate(Request $request, $id)
    {
        $myKelasObj = $this->getMyKelas();
        if (!$myKelasObj) return back();
        $myKelasId = $myKelasObj->id;

        $request->validate([
            'nama' => 'required|string|max:255',
            'nis'  => 'nullable|string|max:20|unique:siswa,nis,' . $id . ',id',
            'nisn' => 'nullable|string|max:20|unique:siswa,nisn,' . $id . ',id',
        ], [
            'nis.unique'  => 'NIS sudah digunakan oleh siswa lain.',
            'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
        ]);

        DB::table('siswa')->where('id', $id)->where('kelas_id', $myKelasId)->update([
            'nama'           => $request->nama,
            'nis'            => $request->nis ?: null,
            'nisn'           => $request->nisn ?: null,
            'jenis_kelamin'  => $request->jenis_kelamin ?? 'Laki-laki',
            'no_hp'          => $request->no_hp ?? '',
            'no_hp_orangtua' => $request->no_hp_orangtua ?? '',
            'tempat_lahir'         => $request->tempat_lahir ?? '',
            'tanggal_lahir'  => $request->tanggal_lahir ?: null,
        ]);
        return back()->with('success', 'Data siswa diperbarui.');
    }

    // ── Jadwal ─────────────────────────────────────────────────
    public function jadwal()
    {
        $user  = auth()->user();
        $myKelasObj = $this->getMyKelas();
        $myKelas = $myKelasObj ? $myKelasObj->nama_kelas : '';
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;

        if (!$myKelasId) return back()->with('error', 'Anda belum ditugaskan ke kelas manapun.');

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        $rows = DB::table('jadwal')
            ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->leftJoin('users', 'jadwal.guru_id', '=', 'users.id')
            ->where('jadwal.kelas_id', $myKelasId)
            ->where('jadwal.tahun_ajaran', $activeTahunName)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat')")
            ->orderBy('jadwal.jam_mulai')
            ->select('jadwal.*', 'mata_pelajaran.nama_mapel as mata_pelajaran', 'users.nama as nama_guru')
            ->get();

        $jadwalMap = [];
        foreach ($rows as $r) {
            $jadwalMap[$r->hari][$r->jam_mulai] = [
                'mata_pelajaran' => $r->mata_pelajaran,
                'nama_guru' => $r->nama_guru
            ];
        }

        $les = [
            ['no' => 1, 'mulai' => '07:30:00', 'selesai' => '08:10:00'],
            ['no' => 2, 'mulai' => '08:10:00', 'selesai' => '08:50:00'],
            ['no' => 3, 'mulai' => '08:50:00', 'selesai' => '09:30:00'],
            ['break' => 'ISTIRAHAT I'],
            ['no' => 4, 'mulai' => '09:50:00', 'selesai' => '10:30:00'],
            ['no' => 5, 'mulai' => '10:30:00', 'selesai' => '11:10:00'],
            ['break' => 'ISTIRAHAT II'],
            ['no' => 6, 'mulai' => '11:25:00', 'selesai' => '12:05:00'],
            ['no' => 7, 'mulai' => '12:05:00', 'selesai' => '12:45:00'],
            ['break' => 'ISTIRAHAT III'],
            ['no' => 8, 'mulai' => '13:15:00', 'selesai' => '13:55:00'],
            ['no' => 9, 'mulai' => '13:55:00', 'selesai' => '14:35:00'],
        ];
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $todayMap = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat'];
        $todayHari = $todayMap[date('l')] ?? '';

        $mapelList = \Illuminate\Support\Facades\DB::table('mata_pelajaran')->orderBy('nama_mapel')->pluck('nama_mapel')->toArray();
        $mapelColor = [];
        foreach ($mapelList as $m) {
            $subj = strtoupper($m);
            if (preg_match('/MATEMATIKA|FISIKA|KIMIA|BIOLOGI|INFORMATIKA|TIK|EKONOMI|PROGRAMMING/', $subj)) {
                $mapelColor[$m] = [
                    'bg' => '#eaf3fc',
                    'border' => '#378ADD',
                    'text' => '#0C447C'
                ];
            } elseif (preg_match('/BAHASA|CONVERSATION|MANDARIN/', $subj)) {
                $mapelColor[$m] = [
                    'bg' => '#fdf0ef',
                    'border' => '#e74c3c',
                    'text' => '#c0392b'
                ];
            } elseif (preg_match('/SEJARAH|GEOGRAFI|SOSIOLOGI|PKN|PANCASILA|AGAMA/', $subj)) {
                $mapelColor[$m] = [
                    'bg' => '#edfaf3',
                    'border' => '#27ae60',
                    'text' => '#1e8a5a'
                ];
            } elseif (preg_match('/SENI|PRAKARYA|PENJAS|BASKET|FUTSAL|ORKES/', $subj)) {
                $mapelColor[$m] = [
                    'bg' => '#fff8ed',
                    'border' => '#EF9F27',
                    'text' => '#b25e00'
                ];
            } else {
                $mapelColor[$m] = [
                    'bg' => '#f7edfb',
                    'border' => '#8e44ad',
                    'text' => '#703893'
                ];
            }
        }

        return view('walikelas.jadwal', compact('user', 'myKelas', 'jadwalMap', 'les', 'hari', 'todayHari', 'mapelColor', 'mapelList'));
    }

    // ── Absensi ────────────────────────────────────────────────
    public function absensi(Request $request)
    {
        $user    = auth()->user();
        $myKelasObj = $this->getMyKelas();
        $myKelas = $myKelasObj ? $myKelasObj->nama_kelas : '';
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;

        if (!$myKelasId) return back()->with('error', 'Anda belum ditugaskan ke kelas manapun.');

        $selectedMonth = $request->get('month', date('Y-m'));
        $year  = (int)substr($selectedMonth, 0, 4);
        $month = (int)substr($selectedMonth, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        $siswaList = DB::table('siswa')->where('kelas_id', $myKelasId)->where('tahun_ajaran_id', $activeTahunId)->orderBy('nama')->get();

        $kehadiranMap = [];
        if ($siswaList->isNotEmpty()) {
            $startDate = "$selectedMonth-01";
            $endDate   = "$selectedMonth-$daysInMonth";
            $kRows = DB::table('kehadiran')
                ->whereIn('siswa_id', $siswaList->pluck('id'))
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->selectRaw('siswa_id, DAY(tanggal) as day_num, status')
                ->get();
            $statusMap = ['Hadir' => 'H', 'Sakit' => 'S', 'Izin' => 'I', 'Alpha' => 'A', 'Libur' => 'L'];
            foreach ($kRows as $r) {
                $kehadiranMap[$r->siswa_id][$r->day_num] = $statusMap[$r->status] ?? '';
            }
        }

        $monthsIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $monthName = $monthsIndo[$month] . ' ' . $year;

        $isOpen = \App\Models\PeriodeNilai::isAnyOpen($activeTahunId);

        // Collect all open period date ranges for frontend editable check
        $editableRanges = [];
        $detectedSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        $periodeObj = \App\Models\PeriodeNilai::where('tahun_ajaran_id', $activeTahunId)->where('semester', $detectedSemester)->first();
        if ($periodeObj) {
            $types = ['uh1', 'uh2', 'uts', 'uas'];
            $today = date('Y-m-d');
            foreach ($types as $type) {
                $startField = $type . '_start';
                $endField = $type . '_end';
                $start = $periodeObj->$startField;
                $end = $periodeObj->$endField;
                if ($start && $end && $today >= $start && $today <= $end) {
                    $editableRanges[] = ['start' => $start, 'end' => $end];
                }
            }
        }

        return view('walikelas.absensi', compact('user', 'myKelas', 'siswaList', 'kehadiranMap', 'daysInMonth', 'selectedMonth', 'monthName', 'isOpen', 'editableRanges'));
    }

    public function absensiSave(Request $request)
    {
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        
        if (!\App\Models\PeriodeNilai::isAnyOpen($activeTahunId)) {
            return response()->json(['error' => 'Semua periode nilai telah ditutup.']);
        }

        // Build editable date ranges for server-side validation
        $editableRanges = [];
        $detectedSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        $periodeObj = \App\Models\PeriodeNilai::where('tahun_ajaran_id', $activeTahunId)->where('semester', $detectedSemester)->first();
        if ($periodeObj) {
            $types = ['uh1', 'uh2', 'uts', 'uas'];
            $today = date('Y-m-d');
            foreach ($types as $type) {
                $startField = $type . '_start';
                $endField = $type . '_end';
                $start = $periodeObj->$startField;
                $end = $periodeObj->$endField;
                if ($start && $end && $today >= $start && $today <= $end) {
                    $editableRanges[] = ['start' => $start, 'end' => $end];
                }
            }
        }

        $myKelasObj = $this->getMyKelas();
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;
        $data    = $request->json()->all()['data'] ?? [];

        foreach ($data as $row) {
            $siswaId = trim($row['siswa_id'] ?? '');
            $tanggal = $row['tanggal'];
            $statusLetter = $row['status'];
            $statusMap = ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpha', 'L' => 'Libur'];
            $status = $statusMap[$statusLetter] ?? null;
            if (!$siswaId || !$tanggal || !$status) continue;

            // Server-side: only allow dates within active period ranges
            $dateInRange = false;
            foreach ($editableRanges as $range) {
                if ($tanggal >= $range['start'] && $tanggal <= $range['end']) {
                    $dateInRange = true;
                    break;
                }
            }
            if (!$dateInRange) continue;

            DB::table('kehadiran')->upsert([
                'siswa_id' => $siswaId,
                'kelas_id' => $myKelasId,
                'tanggal'  => $tanggal,
                'status'   => $status,
            ], ['siswa_id', 'tanggal'], ['status', 'kelas_id']);
        }
        return response()->json(['success' => true]);
    }

    public function nilai(Request $request)
    {
        $user    = auth()->user();
        $myKelasObj = $this->getMyKelas();
        $myKelas = $myKelasObj ? $myKelasObj->nama_kelas : '';
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;

        if (!$myKelasId) return back()->with('error', 'Anda belum ditugaskan ke kelas manapun.');

        // Determine active semester
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        
        $reqSemester = $request->get('semester');
        if ($reqSemester === '1' || $reqSemester === '2') {
            $semesterNum = $reqSemester;
            $activeSemester = $reqSemester === '1' ? 'Ganjil' : 'Genap';
        } else {
            $semesterNum = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
            $activeSemester = $semesterNum === '1' ? 'Ganjil' : 'Genap';
        }
        $allowedTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];

        $validTypes = $allowedTypes;
        $jenis = in_array($request->get('jenis'), $validTypes) ? $request->get('jenis') : $validTypes[0];

        $siswaList = DB::table('siswa')->where('kelas_id', $myKelasId)->where('tahun_ajaran_id', $activeTahunId)->orderBy('nama')->get();
        $siswaIds = $siswaList->pluck('id')->toArray();

        $gradesRaw = [];
        $labGrades = [];
        $labSubjects = ['FISIKA', 'KIMIA', 'BIOLOGI'];
        if (in_array($jenis, $validTypes) && !empty($siswaIds)) {
            $nilaiRows = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('penilaian.siswa_id', $siswaIds)
                ->where('penilaian.assessment_type', $jenis)
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $semesterNum)
                ->selectRaw('penilaian.siswa_id, mata_pelajaran.nama_mapel as mata_pelajaran, penilaian.nilai as score')
                ->get();
            foreach ($nilaiRows as $r) {
                $mapel = $r->mata_pelajaran;
                if (str_starts_with($mapel, 'PEND. AGAMA')) {
                    $mapel = 'PEND. AGAMA & BUDI PEKERTI';
                    if (isset($gradesRaw[$r->siswa_id][$mapel])) {
                        $existingScore = $gradesRaw[$r->siswa_id][$mapel];
                        if (($existingScore === null || $existingScore == 0) && $r->score !== null && $r->score != 0) {
                            $gradesRaw[$r->siswa_id][$mapel] = $r->score;
                        }
                        continue;
                    }
                }
                $gradesRaw[$r->siswa_id][$mapel] = $r->score;
            }

            // Fetch lab grades for FISIKA, KIMIA, BIOLOGI (always load uas_lab as the semester grade)
            $labRows = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('penilaian.siswa_id', $siswaIds)
                ->where('penilaian.assessment_type', 'uas_lab')
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $semesterNum)
                ->whereIn('mata_pelajaran.nama_mapel', $labSubjects)
                ->selectRaw('penilaian.siswa_id, mata_pelajaran.nama_mapel as mata_pelajaran, penilaian.nilai as score')
                ->get();
            foreach ($labRows as $r) {
                $labGrades[$r->siswa_id][$r->mata_pelajaran] = $r->score;
            }
        }

        $subjects = ['PEND. AGAMA & BUDI PEKERTI', 'PEND. PANCASILA / PKN','BAHASA INDONESIA','BAHASA INGGRIS','MATEMATIKA / MATEMATIKA WAJIB','SENI MUSIK / SENI BUDAYA','PENJAS ORKES','SEJARAH / SEJARAH INDONESIA','PRAKARYA & KEWIRAUSAHAAN','GEOGRAFI','EKONOMI','SOSIOLOGI','SEJARAH (Tingkat Lanjut)','FISIKA','KIMIA','BIOLOGI','MATEMATIKA (Tingkat Lanjut)','INFORMATIKA / TIK','BAHASA MANDARIN','CONVERSATION'];

        // Load ekskul records as flat list (record-based, not per-siswa)
        $ekskulRecords = collect();
        if (!empty($siswaIds)) {
            $ekskulRecords = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->join('siswa', 'penilaian.siswa_id', '=', 'siswa.id')
                ->whereIn('penilaian.siswa_id', $siswaIds)
                ->where('penilaian.assessment_type', 'ekskul')
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $semesterNum)
                ->select(
                    'penilaian.id as record_id',
                    'penilaian.siswa_id',
                    'siswa.nama as siswa_nama',
                    'mata_pelajaran.nama_mapel',
                    'penilaian.nilai_deskriptif',
                    'penilaian.ekskul_keterangan'
                )
                ->orderBy('siswa.nama')
                ->get();
            foreach ($ekskulRecords as $r) {
                $r->ekskul_name = str_replace('EKSKUL ', '', $r->nama_mapel);
            }
        }

        // Load available ekskul options from mata_pelajaran
        $ekskulOptions = DB::table('mata_pelajaran')
            ->where('nama_mapel', 'LIKE', 'EKSKUL %')
            ->orderBy('nama_mapel')
            ->pluck('nama_mapel')
            ->map(fn($n) => str_replace('EKSKUL ', '', $n))
            ->values();

        // Load lab records as flat list (record-based)
        $labRecords = collect();
        if (!empty($siswaIds)) {
            $labRecords = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->join('siswa', 'penilaian.siswa_id', '=', 'siswa.id')
                ->whereIn('penilaian.siswa_id', $siswaIds)
                ->where('penilaian.assessment_type', 'uas_lab')
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $semesterNum)
                ->select(
                    'penilaian.id as record_id',
                    'penilaian.siswa_id',
                    'siswa.nama as siswa_nama',
                    'mata_pelajaran.nama_mapel',
                    'penilaian.nilai'
                )
                ->orderBy('siswa.nama')
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get();
        }
        // Group lab records by siswa for display
        $labRecordsBySiswa = $labRecords->groupBy('siswa_id');

        // Load kepribadian records
        $kepribadianRecords = [];
        if (!empty($siswaIds)) {
            $kepRows = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('penilaian.siswa_id', $siswaIds)
                ->whereIn('penilaian.assessment_type', ['kepribadian_uh1', 'kepribadian_uts', 'kepribadian_uh2'])
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $semesterNum)
                ->select('penilaian.siswa_id', 'mata_pelajaran.nama_mapel', 'penilaian.nilai_deskriptif', 'penilaian.assessment_type')
                ->get();
            foreach ($kepRows as $row) {
                $aspect = str_replace('KEPRIBADIAN ', '', $row->nama_mapel);
                $kepribadianRecords[$row->siswa_id][$row->assessment_type][$aspect] = $row->nilai_deskriptif;
            }
        }

        $kepribadianAspects = ['Kelakuan', 'Kerajinan', 'Kerapian', 'Kedisiplinan'];
        $kepribadianPeriods = [
            'kepribadian_uh1' => 'Laporan 1 (UH1)',
            'kepribadian_uts' => 'Laporan 2 (UTS)',
            'kepribadian_uh2' => 'Laporan 3 (UH2)',
        ];

        $isOpen = \App\Models\PeriodeNilai::isAnyOpen($activeTahunId);

        return view('walikelas.nilai', compact('user', 'myKelas', 'siswaList', 'gradesRaw', 'labGrades', 'subjects', 'jenis', 'activeSemester', 'allowedTypes', 'isOpen', 'semesterNum', 'ekskulRecords', 'ekskulOptions', 'labRecordsBySiswa', 'kepribadianRecords', 'kepribadianAspects', 'kepribadianPeriods'));
    }

    public function nilaiSave(Request $request)
    {
        $body = $request->json()->all();

        $siswaId = trim($body['siswa_id'] ?? '');
        $mapel   = trim($body['mata_pelajaran'] ?? '');
        $jenis   = trim($body['jenis'] ?? '');
        $value   = $body['value'] !== '' ? intval($body['value']) : null;

        // Determine active semester
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $tahunAjaranId = $activeTahun ? $activeTahun->id : 1;
        $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($tahunAjaranId) === '1' ? 'Ganjil' : 'Genap';
        $semesterNum = (int) \App\Models\PeriodeNilai::detectActiveSemester($tahunAjaranId);
        $allowedTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];

        // Allow editing without period check for lab (uas_lab) assessments
        if (!str_ends_with($jenis, '_lab') && !\App\Models\PeriodeNilai::isAnyOpen($tahunAjaranId)) {
            return response()->json(['error' => 'Semua periode nilai telah ditutup.']);
        }

        if (!$siswaId || !$mapel || !in_array($jenis, $allowedTypes)) {
            return response()->json(['error' => 'Invalid input']);
        }

        // For lab types, validate the subject is FISIKA, KIMIA or BIOLOGI
        if (str_ends_with($jenis, '_lab')) {
            $labSubjects = ['FISIKA', 'KIMIA', 'BIOLOGI'];
            if (!in_array($mapel, $labSubjects)) {
                return response()->json(['error' => 'Lab scores only for Fisika, Kimia, Biologi']);
            }
        }
        
        $mapelId = DB::table('mata_pelajaran')->where('nama_mapel', $mapel)->value('id');
        if (!$mapelId) return response()->json(['error' => 'Mapel not found']);

        if ($value !== null) {
            // Find the guru_id from jadwal or guru_mapel for this student's class and subject to satisfy foreign key constraint
            $kelasId = DB::table('siswa')->where('id', $siswaId)->value('kelas_id');
            $guruId = DB::table('jadwal')
                ->where('kelas_id', $kelasId)
                ->where('mata_pelajaran_id', $mapelId)
                ->value('guru_id');

            if (!$guruId) {
                $guruId = DB::table('guru_mapel')
                    ->where('mata_pelajaran_id', $mapelId)
                    ->value('guru_id');
            }

            if (!$guruId) {
                $guruId = DB::table('users')->where('role', 'guru')->orderBy('id')->value('id');
            }

            if (!$guruId) {
                $guruId = DB::table('users')->orderBy('id')->value('id');
            }

            DB::table('penilaian')->upsert([
                'siswa_id'          => $siswaId,
                'mata_pelajaran_id' => $mapelId,
                'guru_id'           => $guruId ?: 0,
                'tahun_ajaran_id'   => $tahunAjaranId,
                'semester'          => $semesterNum,
                'assessment_type'   => $jenis,
                'nilai'             => $value,
            ], ['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'semester', 'assessment_type'], ['nilai']);
        } else {
            DB::table('penilaian')
                ->where('siswa_id', $siswaId)
                ->where('mata_pelajaran_id', $mapelId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where('semester', $semesterNum)
                ->where('assessment_type', $jenis)
                ->delete();
        }

        return response()->json(['success' => true]);
    }

    public function ekskulSave(Request $request)
    {
        $body = $request->json()->all();

        $recordId = $body['record_id'] ?? null;
        $siswaId = trim($body['siswa_id'] ?? '');
        $field   = trim($body['field'] ?? '');
        $value   = trim($body['value'] ?? '');

        $allowedFields = ['ekskul', 'nilai_ekskul', 'ekskul_keterangan'];
        if (!in_array($field, $allowedFields)) {
            return response()->json(['error' => 'Invalid input']);
        }
        if (!$recordId && !$siswaId) {
            return response()->json(['error' => 'Siswa ID or Record ID required']);
        }

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $tahunAjaranId = $activeTahun ? $activeTahun->id : 1;
        $semesterNum = (int) \App\Models\PeriodeNilai::detectActiveSemester($tahunAjaranId);

        $allowedEkskul = DB::table('mata_pelajaran')->where('nama_mapel', 'LIKE', 'EKSKUL %')->pluck('nama_mapel')->map(fn($n) => str_replace('EKSKUL ', '', $n))->toArray();
        $allowedNilai  = ['A','B','C'];

        if ($field === 'ekskul' && $value !== '' && !in_array($value, $allowedEkskul)) {
            return response()->json(['error' => 'Invalid ekskul value']);
        }
        if ($field === 'nilai_ekskul' && $value !== '' && !in_array($value, $allowedNilai)) {
            return response()->json(['error' => 'Invalid nilai ekskul value']);
        }

        // Ensure class context
        $myKelasObj = $this->getMyKelas();
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;
        if (!$myKelasId) {
            return response()->json(['error' => 'Kelas not found']);
        }

        $existingEkskul = null;

        if ($recordId) {
            // Updating an existing record
            $existingEkskul = DB::table('penilaian')
                ->join('siswa', 'penilaian.siswa_id', '=', 'siswa.id')
                ->where('penilaian.id', $recordId)
                ->where('penilaian.assessment_type', 'ekskul')
                ->where('siswa.kelas_id', $myKelasId)
                ->select('penilaian.*')
                ->first();
            
            if (!$existingEkskul) {
                return response()->json(['error' => 'Record not found or access denied']);
            }
            $siswaId = $existingEkskul->siswa_id;
        } else {
            // Creating a new record via siswaId
            $siswa = DB::table('siswa')->where('id', $siswaId)->where('kelas_id', $myKelasId)->first();
            if (!$siswa) {
                return response()->json(['error' => 'Siswa not found in your class']);
            }
        }

        if ($field === 'ekskul') {
            if ($value === '') {
                // Delete if empty ekskul name
                if ($existingEkskul) {
                    DB::table('penilaian')->where('id', $existingEkskul->id)->delete();
                }
            } else {
                $mapelName = 'EKSKUL ' . $value;
                $mapelId = DB::table('mata_pelajaran')->where('nama_mapel', $mapelName)->value('id');
                if (!$mapelId) return response()->json(['error' => 'Ekskul mata pelajaran not found']);

                if ($existingEkskul) {
                    // Update existing
                    DB::table('penilaian')->where('id', $existingEkskul->id)->update(['mata_pelajaran_id' => $mapelId]);
                } else {
                    // Find a valid guru_id to satisfy foreign key constraint
                    $guruId = DB::table('guru_mapel')->where('mata_pelajaran_id', $mapelId)->value('guru_id');
                    if (!$guruId) {
                        $guruId = DB::table('users')->where('role', 'guru')->orderBy('id')->value('id');
                    }
                    if (!$guruId) {
                        $guruId = DB::table('users')->orderBy('id')->value('id');
                    }

                    // Create new
                    DB::table('penilaian')->insert([
                        'siswa_id' => $siswaId,
                        'guru_id' => $guruId ?: 1, // Fallback to 1 if absolutely no users found, though highly unlikely
                        'mata_pelajaran_id' => $mapelId,
                        'tahun_ajaran_id' => $tahunAjaranId,
                        'semester' => $semesterNum,
                        'assessment_type' => 'ekskul',
                        'nilai' => null,
                        'nilai_deskriptif' => null,
                        'ekskul_keterangan' => null,
                    ]);
                }
            }
        } elseif ($field === 'nilai_ekskul') {
            if ($existingEkskul) {
                DB::table('penilaian')->where('id', $existingEkskul->id)->update(['nilai_deskriptif' => $value ?: null]);
            } else {
                // If creating via API for a specific field, we must find the record first, or error. 
                // In our new flow, saveNewEkskul first saves 'ekskul' which creates the record, then calls for 'nilai_ekskul' with siswaId.
                // But wait! If saveNewEkskul calls with siswa_id to update nilai, it might update ALL ekskuls for that student!
                // Let's find the latest ekskul record for this student created in this session (or just order by id desc)
                $latest = DB::table('penilaian')->where('siswa_id', $siswaId)->where('assessment_type', 'ekskul')->orderBy('id', 'desc')->first();
                if ($latest) {
                    DB::table('penilaian')->where('id', $latest->id)->update(['nilai_deskriptif' => $value ?: null]);
                } else {
                    return response()->json(['error' => 'Pilih ekskul terlebih dahulu']);
                }
            }
        } elseif ($field === 'ekskul_keterangan') {
            if ($existingEkskul) {
                DB::table('penilaian')->where('id', $existingEkskul->id)->update(['ekskul_keterangan' => $value ?: null]);
            } else {
                $latest = DB::table('penilaian')->where('siswa_id', $siswaId)->where('assessment_type', 'ekskul')->orderBy('id', 'desc')->first();
                if ($latest) {
                    DB::table('penilaian')->where('id', $latest->id)->update(['ekskul_keterangan' => $value ?: null]);
                } else {
                    return response()->json(['error' => 'Pilih ekskul terlebih dahulu']);
                }
            }
        }


        return response()->json(['success' => true]);
    }

    public function ekskulDelete(Request $request)
    {
        $body = $request->json()->all();
        $recordId = $body['record_id'] ?? null;

        if (!$recordId) {
            return response()->json(['error' => 'Invalid input']);
        }

        // Verify the record belongs to walikelas's class
        $myKelasObj = $this->getMyKelas();
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;
        if (!$myKelasId) {
            return response()->json(['error' => 'Kelas not found']);
        }

        $record = DB::table('penilaian')
            ->join('siswa', 'penilaian.siswa_id', '=', 'siswa.id')
            ->where('penilaian.id', $recordId)
            ->where('penilaian.assessment_type', 'ekskul')
            ->where('siswa.kelas_id', $myKelasId)
            ->select('penilaian.id')
            ->first();

        if (!$record) {
            return response()->json(['error' => 'Record not found']);
        }

        DB::table('penilaian')->where('id', $recordId)->delete();

        return response()->json(['success' => true]);
    }

    public function kepribadianSave(Request $request)
    {
        $body = $request->json()->all();

        $siswaId = trim($body['siswa_id'] ?? '');
        $aspect  = trim($body['aspect'] ?? '');
        $periode = trim($body['periode'] ?? ''); // kepribadian_uh1, kepribadian_uts, kepribadian_uh2
        $value   = trim($body['value'] ?? ''); // A, B, C or empty

        $allowedAspects = ['Kelakuan', 'Kerajinan', 'Kerapian', 'Kedisiplinan'];
        $allowedPeriods = ['kepribadian_uh1', 'kepribadian_uts', 'kepribadian_uh2'];
        $allowedGrades  = ['A', 'B', 'C'];

        if (!in_array($aspect, $allowedAspects) || !in_array($periode, $allowedPeriods)) {
            return response()->json(['error' => 'Invalid parameters']);
        }

        if ($value !== '' && !in_array($value, $allowedGrades)) {
            return response()->json(['error' => 'Invalid grade value']);
        }

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $tahunAjaranId = $activeTahun ? $activeTahun->id : 1;
        $semesterNum = (int) \App\Models\PeriodeNilai::detectActiveSemester($tahunAjaranId);

        // Ensure period check
        if (!\App\Models\PeriodeNilai::isAnyOpen($tahunAjaranId)) {
            return response()->json(['error' => 'Semua periode nilai telah ditutup.']);
        }

        // Verify the student belongs to walikelas's class
        $myKelasObj = $this->getMyKelas();
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;
        if (!$myKelasId) {
            return response()->json(['error' => 'Kelas not found']);
        }

        $siswa = DB::table('siswa')->where('id', $siswaId)->where('kelas_id', $myKelasId)->first();
        if (!$siswa) {
            return response()->json(['error' => 'Siswa not found in your class']);
        }

        $mapelName = 'KEPRIBADIAN ' . $aspect;
        $mapelId = DB::table('mata_pelajaran')->where('nama_mapel', $mapelName)->value('id');
        if (!$mapelId) {
            return response()->json(['error' => 'Personality aspect not found in database']);
        }

        if ($value === '') {
            DB::table('penilaian')
                ->where('siswa_id', $siswaId)
                ->where('mata_pelajaran_id', $mapelId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where('semester', $semesterNum)
                ->where('assessment_type', $periode)
                ->delete();
        } else {
            // Find a valid guru_id to satisfy foreign key constraint
            $guruId = DB::table('users')->where('role', 'wali_kelas')->where('id', auth()->id())->value('id');
            if (!$guruId) {
                $guruId = DB::table('users')->orderBy('id')->value('id');
            }

            DB::table('penilaian')->upsert([
                'siswa_id'          => $siswaId,
                'guru_id'           => $guruId ?: 1,
                'mata_pelajaran_id' => $mapelId,
                'tahun_ajaran_id'   => $tahunAjaranId,
                'semester'          => $semesterNum,
                'assessment_type'   => $periode,
                'nilai'             => null,
                'nilai_deskriptif'  => $value,
            ], ['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'semester', 'assessment_type'], ['nilai_deskriptif']);
        }

        return response()->json(['success' => true]);
    }

    public function labDelete(Request $request)
    {
        return response()->json(['error' => 'Wali kelas tidak memiliki akses untuk menghapus nilai laboratorium.'], 403);
    }

    public function cetak(Request $request)
    {
        $user    = auth()->user();
        $myKelasObj = $this->getMyKelas();
        $myKelas = $myKelasObj ? $myKelasObj->nama_kelas : '';
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;

        if (!$myKelasId) return back()->with('error', 'Anda belum ditugaskan ke kelas manapun.');

        // Active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeYear = $activeTahun ? $activeTahun->nama : '2025/2026';

        $activeSemester = $request->get('semester');
        if (!$activeSemester || !in_array($activeSemester, ['1', '2'])) {
            $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }

        $validTypes = ['UH1', 'UH2', 'UTS', 'UAS'];
        $jenisRapor = in_array(strtoupper($request->get('tipe', 'UH1')), $validTypes)
            ? strtoupper($request->get('tipe', 'UH1'))
            : 'UH1';

        // Check if this class's progress is complete
        $isGlobalComplete = \App\Models\PeriodeNilai::isClassProgressComplete($myKelasId, strtolower($jenisRapor), $activeTahunId, $activeSemester);

        $siswaList = DB::table('siswa')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->where('siswa.kelas_id', $myKelasId)
            ->where('siswa.tahun_ajaran_id', $activeTahunId)
            ->orderBy('siswa.nama')
            ->select('siswa.id', 'siswa.nama', 'siswa.nis', 'siswa.nisn', 'siswa.kelas_id', 'kelas.nama_kelas as kelas')
            ->get();

        return view('walikelas.cetak', compact('user', 'myKelas', 'siswaList', 'activeYear', 'jenisRapor', 'isGlobalComplete', 'activeSemester'));
    }

    public function printRapor(Request $request)
    {
        $user    = auth()->user();

        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        $activeSemesterReq = $request->get('semester');
        if ($activeSemesterReq === '1' || $activeSemesterReq === '2') {
            $activeSemesterNum = $activeSemesterReq;
        } else {
            $activeSemesterNum = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }

        $myKelasObj = $this->getMyKelas();
        $myKelas = $myKelasObj ? $myKelasObj->nama_kelas : '';
        $myKelasId = $myKelasObj ? $myKelasObj->id : null;

        $jenis   = strtolower($request->get('tipe', 'uh1'));

        if (!$myKelasId || !\App\Models\PeriodeNilai::isClassProgressComplete($myKelasId, $jenis, $activeTahunId, $activeSemesterNum)) {
            return redirect()->back()->with('error', 'Cetak rapor ditangguhkan karena penginputan nilai/absensi kelas Anda belum mencapai progress 100%.');
        }

        $ids     = array_filter(array_map('trim', explode(',', $request->get('siswa_ids', ''))));
        $tahun   = $request->get('tahun', '2025/2026');
        $kelas   = $request->get('kelas', $myKelas);

        // Merge into one generic subject for Rapor
        $subjects = ['PEND. AGAMA & BUDI PEKERTI','PEND. PANCASILA / PKN','BAHASA INDONESIA','BAHASA INGGRIS','MATEMATIKA / MATEMATIKA WAJIB','SENI MUSIK / SENI BUDAYA','PENJAS ORKES','SEJARAH / SEJARAH INDONESIA','PRAKARYA & KEWIRAUSAHAAN','GEOGRAFI','EKONOMI','SOSIOLOGI','SEJARAH (Tingkat Lanjut)','FISIKA','KIMIA','BIOLOGI','MATEMATIKA (Tingkat Lanjut)','INFORMATIKA / TIK','BAHASA MANDARIN','CONVERSATION'];

        $tahunRow = DB::table('tahun_ajaran')->where('nama', $tahun)->first();
        $tahunId = $tahunRow ? $tahunRow->id : 1;
        $activeSemester = $activeSemesterNum === '1' ? 'Ganjil' : 'Genap';

        $siswaList = DB::table('siswa')
            ->leftJoin('kelas as k', 'siswa.kelas_id', '=', 'k.id')
            ->leftJoin('users as wk', 'k.user_walikelas_id', '=', 'wk.id')
            ->whereIn('siswa.id', $ids)
            ->where('siswa.kelas_id', $myKelasId)
            ->select('siswa.*', 'k.nama_kelas as kelas', 'wk.nama as wali_kelas_nama')
            ->orderBy('siswa.nama')
            ->get();

        $grades = [];
        if ($siswaList->isNotEmpty() && in_array($jenis, ['uh1','uh2','uts','uas', 'tugas1', 'tugas2'])) {
            // For UAS (Rapor Akhir), load ALL assessment types to calculate Nilai Akhir
            $allAssessmentTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];
            $typesToLoad = in_array($jenis, ['uh1', 'uts', 'uh2'])
                ? ['uh1', 'tugas1', 'uts', 'uh2', 'tugas2']
                : ($jenis === 'uas' ? $allAssessmentTypes : [$jenis]);

            $nilaiRows = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('penilaian.siswa_id', $ids)
                ->where('penilaian.tahun_ajaran_id', $tahunId)
                ->where('penilaian.semester', $activeSemesterNum)
                ->whereIn('penilaian.assessment_type', $typesToLoad)
                ->selectRaw('penilaian.siswa_id, mata_pelajaran.nama_mapel as mata_pelajaran, penilaian.nilai as score, penilaian.nilai_deskriptif, penilaian.assessment_type')
                ->get();

            if ($jenis === 'uas') {
                // Calculate Nilai Akhir using bobot weights
                $bobot = DB::table('bobot_nilai')->where('tahun_ajaran_id', $tahunId)->first();
                if (!$bobot) {
                    $bobot = (object)['tugas1' => 7.5, 'uh1' => 7.5, 'tugas2' => 7.5, 'uh2' => 7.5, 'uts' => 30, 'uas' => 40];
                }
                
                // Group all scores by siswa and mapel
                $allScores = [];
                foreach ($nilaiRows as $n) {
                    $mapel = $n->mata_pelajaran;
                    if (str_starts_with($mapel, 'PEND. AGAMA')) {
                        $mapel = 'PEND. AGAMA & BUDI PEKERTI';
                    }
                    $allScores[$n->siswa_id][$mapel][$n->assessment_type] = $n;
                }
                
                // Calculate weighted Nilai Akhir for each siswa/mapel
                foreach ($allScores as $siswaId => $mapelScores) {
                    foreach ($mapelScores as $mapel => $typeScores) {
                        $nilaiAkhir = null;
                        $deskriptif = isset($typeScores['uas']) ? ($typeScores['uas']->nilai_deskriptif ?? '') : '';
                        
                        if ($bobot) {
                            $filledCount = 0;
                            $na_raw = 0;
                            foreach ($allAssessmentTypes as $type) {
                                if (isset($typeScores[$type]) && $typeScores[$type]->score !== null) {
                                    $filledCount++;
                                    $na_raw += $typeScores[$type]->score * ($bobot->{$type} / 100);
                                }
                            }
                            if ($filledCount === count($allAssessmentTypes)) {
                                $totalWeight = array_sum(array_map(fn($t) => $bobot->{$t}, $allAssessmentTypes));
                                if ($totalWeight > 0 && $totalWeight < 100) {
                                    $na_raw = $na_raw / ($totalWeight / 100);
                                }
                                $nilaiAkhir = ceil($na_raw);
                            }
                        }
                        
                        // Store as a grade object with calculated score
                        $grades[$siswaId][$mapel] = (object)[
                            'siswa_id' => $siswaId,
                            'mata_pelajaran' => $mapel,
                            'score' => $nilaiAkhir,
                            'nilai_deskriptif' => $deskriptif,
                            'assessment_type' => 'uas',
                        ];
                    }
                }
            } else {
                foreach ($nilaiRows as $n) {
                    $mapel = $n->mata_pelajaran;
                    if (str_starts_with($mapel, 'PEND. AGAMA')) {
                        $mapel = 'PEND. AGAMA & BUDI PEKERTI';
                    }
                    
                    if (in_array($jenis, ['uh1', 'uts', 'uh2'])) {
                        $grades[$n->siswa_id][$mapel][$n->assessment_type] = $n;
                    } else {
                        if (isset($grades[$n->siswa_id][$mapel])) {
                            $existingScore = $grades[$n->siswa_id][$mapel]->score;
                            if (($existingScore === null || $existingScore == 0) && $n->score !== null && $n->score != 0) {
                                $grades[$n->siswa_id][$mapel] = $n;
                            }
                            continue;
                        }
                        $grades[$n->siswa_id][$mapel] = $n;
                    }
                }
            }
        }

        // ── Ekskul ─────────────────────────────────────────────────
        $ekskulRecords = DB::table('penilaian')
            ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->whereIn('penilaian.siswa_id', $ids)
            ->where('penilaian.tahun_ajaran_id', $tahunId)
            ->where('penilaian.semester', $activeSemesterNum)
            ->where('penilaian.assessment_type', 'ekskul')
            ->select('penilaian.siswa_id', 'mata_pelajaran.nama_mapel', 'penilaian.nilai_deskriptif', 'penilaian.ekskul_keterangan')
            ->get();
        
        $ekskulData = [];
        foreach ($ekskulRecords as $rec) {
            $name = str_replace('EKSKUL ', '', $rec->nama_mapel);
            $ekskulData[$rec->siswa_id][] = (object)[
                'nama' => $name,
                'predikat' => $rec->nilai_deskriptif,
                'keterangan' => $rec->ekskul_keterangan
            ];
        }

        // ── Kehadiran ──────────────────────────────────────────────
        $kehadiranQuery = DB::table('kehadiran')
            ->whereIn('siswa_id', $ids)
            ->whereIn('status', ['Sakit', 'Izin', 'Alpha']);

        $detectedSemester = $activeSemesterNum;
        $periodeObj = \App\Models\PeriodeNilai::where('tahun_ajaran_id', $tahunId)->where('semester', $detectedSemester)->first();
        if ($periodeObj) {
            $pType = $jenis;
            if ($pType === 'tugas1') $pType = 'uh1';
            if ($pType === 'tugas2') $pType = 'uh2';

            $sField = $pType . '_start';
            $eField = $pType . '_end';

            if (isset($periodeObj->$sField) && isset($periodeObj->$eField) && $periodeObj->$sField && $periodeObj->$eField) {
                $kehadiranQuery->whereBetween('tanggal', [$periodeObj->$sField, $periodeObj->$eField]);
            }
        }

        $kehadiranRecords = $kehadiranQuery
            ->select('siswa_id', 'status', DB::raw('count(*) as total'))
            ->groupBy('siswa_id', 'status')
            ->get();
        
        $absensiData = [];
        foreach ($kehadiranRecords as $rec) {
            $absensiData[$rec->siswa_id][$rec->status] = $rec->total;
        }

        $absensiDataByPeriod = [];
        if (in_array($jenis, ['uh1', 'uts', 'uh2'])) {
            $periods = ['uh1', 'uts', 'uh2'];
            foreach ($periods as $pKey) {
                $sField = $pKey . '_start';
                $eField = $pKey . '_end';
                
                if ($periodeObj && isset($periodeObj->$sField) && isset($periodeObj->$eField) && $periodeObj->$sField && $periodeObj->$eField) {
                    $periodKehadiran = DB::table('kehadiran')
                        ->whereIn('siswa_id', $ids)
                        ->whereIn('status', ['Sakit', 'Izin', 'Alpha'])
                        ->whereBetween('tanggal', [$periodeObj->$sField, $periodeObj->$eField])
                        ->select('siswa_id', 'status', DB::raw('count(*) as total'))
                        ->groupBy('siswa_id', 'status')
                        ->get();
                    
                    foreach ($periodKehadiran as $rec) {
                        $absensiDataByPeriod[$rec->siswa_id][$pKey][$rec->status] = $rec->total;
                    }
                }
            }
        }

        // ── Nilai Laboratorium (always uas_lab, entered once per semester) ──
        $labRecords = DB::table('penilaian')
            ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->whereIn('penilaian.siswa_id', $ids)
            ->where('penilaian.tahun_ajaran_id', $tahunId)
            ->where('penilaian.semester', $activeSemesterNum)
            ->where('penilaian.assessment_type', 'uas_lab')
            ->select('penilaian.siswa_id', 'mata_pelajaran.nama_mapel', 'penilaian.nilai')
            ->get();

        $labData = [];
        foreach ($labRecords as $rec) {
            $labData[$rec->siswa_id][$rec->nama_mapel] = $rec->nilai;
        }

        // ── Kepribadian ────────────────────────────────────────────
        // Determine mapping period: uh1 -> kepribadian_uh1, uts -> kepribadian_uts, uh2/uas -> kepribadian_uh2
        $kepPeriodMap = [
            'uh1'   => 'kepribadian_uh1',
            'tugas1'=> 'kepribadian_uh1',
            'uts'   => 'kepribadian_uts',
            'uh2'   => 'kepribadian_uh2',
            'tugas2'=> 'kepribadian_uh2',
            'uas'   => 'kepribadian_uh2',
        ];
        $kepPeriodTypes = ['kepribadian_uh1', 'kepribadian_uts', 'kepribadian_uh2'];

        $kepribadianRows = DB::table('penilaian')
            ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->whereIn('penilaian.siswa_id', $ids)
            ->where('penilaian.tahun_ajaran_id', $tahunId)
            ->where('penilaian.semester', $activeSemesterNum)
            ->whereIn('penilaian.assessment_type', $kepPeriodTypes)
            ->select('penilaian.siswa_id', 'mata_pelajaran.nama_mapel', 'penilaian.nilai_deskriptif', 'penilaian.assessment_type')
            ->get();

        $kepribadianData = [];
        foreach ($kepribadianRows as $row) {
            $aspect = str_replace('KEPRIBADIAN ', '', $row->nama_mapel);
            $kepribadianData[$row->siswa_id][$row->assessment_type][$aspect] = $row->nilai_deskriptif;
        }

        // For the print view, determine which kepribadian period corresponds to jenis
        $kepPeriodForJenis = $kepPeriodMap[$jenis] ?? 'kepribadian_uh1';

        return view('walikelas.print_rapor', compact('siswaList', 'grades', 'subjects', 'jenis', 'tahun', 'kelas', 'user', 'ekskulData', 'absensiData', 'absensiDataByPeriod', 'labData', 'activeSemester', 'kepribadianData', 'kepPeriodForJenis'));
    }
}
