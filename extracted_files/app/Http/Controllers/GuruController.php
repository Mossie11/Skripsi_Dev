<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    private function getGuruMapels($guruId)
    {
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        $ids1 = DB::table('guru_mapel')->where('guru_id', $guruId)->pluck('mata_pelajaran_id')->toArray();
        $ids2 = DB::table('jadwal')->where('guru_id', $guruId)->where('tahun_ajaran', $activeTahunName)->pluck('mata_pelajaran_id')->toArray();
        
        $allIds = array_unique(array_merge($ids1, $ids2));
        
        $mapels = DB::table('mata_pelajaran')
            ->whereIn('id', $allIds)
            ->where('nama_mapel', 'not like', 'EKSKUL%')
            ->orderBy('nama_mapel')
            ->pluck('nama_mapel')
            ->toArray();
        
        return [
            'ids' => $allIds,
            'names' => $mapels,
            'mapel_string' => count($mapels) > 0 ? implode(', ', $mapels) : '-'
        ];
    }

    // ── Dashboard ──────────────────────────────────────────
    public function dashboard()
    {
        $user = auth()->user();
        $guruId = $user->id;

        $mapelData = $this->getGuruMapels($guruId);
        $user->mapel = $mapelData['mapel_string'];

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        // Days this guru has jadwal based on guru_id
        $jadwalHari = DB::table('jadwal')
            ->where('guru_id', $guruId)
            ->where('tahun_ajaran', $activeTahunName)
            ->distinct()
            ->pluck('hari')
            ->map(fn($h) => strtolower(trim($h)))
            ->toArray();

        return view('guru.dashboard', compact('user', 'jadwalHari'));
    }

    // ── Profile ────────────────────────────────────────────
    public function profile()
    {
        $user = auth()->user();
        $guruId = $user->id;

        $mapelData = $this->getGuruMapels($guruId);
        $user->mapel = $mapelData['mapel_string'];
        $user->mata_pelajaran_ids = $mapelData['ids'];

        $mapelList = DB::table('mata_pelajaran')->orderBy('nama_mapel')->pluck('nama_mapel', 'id');
        return view('guru.profile', compact('user', 'mapelList'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mata_pelajaran_id' => 'nullable|array',
            'mata_pelajaran_id.*' => 'integer|exists:mata_pelajaran,id',
        ]);

        $data = [
            'nama' => $request->nama,
            'nuptk' => $request->nuptk,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
        ];

        if ($request->has('nip')) {
            $data['nip'] = $request->nip;
        }

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

    // ── Jadwal ─────────────────────────────────────────────
    public function jadwal()
    {
        $user = auth()->user();
        $guruId = $user->id;

        $mapelData = $this->getGuruMapels($guruId);
        $mapel = $mapelData['mapel_string'] !== '-' ? $mapelData['mapel_string'] : '';
        $user->mapel = $mapelData['mapel_string'];
        return view('guru.jadwal', compact('user', 'mapel'));
    }

    // ── Nilai ──────────────────────────────────────────────
    public function nilai()
    {
        $user = auth()->user();
        $guruId = $user->id;

        $mapelData = $this->getGuruMapels($guruId);
        $mapel = $mapelData['mapel_string'] !== '-' ? $mapelData['mapel_string'] : '';
        $user->mapel = $mapelData['mapel_string'];

        // Determine active semester / tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';
        $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahun ? $activeTahun->id : 1) === '1' ? 'Ganjil' : 'Genap';

        $kelasList = DB::table('jadwal')
            ->join('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
            ->where('jadwal.guru_id', $guruId)
            ->where('jadwal.tahun_ajaran', $activeTahunName)
            ->distinct()
            ->orderBy('kelas.nama_kelas')
            ->pluck('kelas.nama_kelas', 'kelas.id');

        return view('guru.nilai', compact('user', 'mapel', 'kelasList', 'activeSemester'));
    }

    public function nilaiAction(Request $request)
    {
        // handled via AJAX — see api routes
        return response()->json(['error' => 'Use AJAX endpoint'], 405);
    }

    // ── API: jadwal by date ────────────────────────────────
    public function apiJadwal(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->id;
        $date = $request->get('date', '');

        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([]);
        }

        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => null,
        ];
        $dayEn = date('l', strtotime($date));
        $hariId = $dayMap[$dayEn] ?? null;

        if (!$hariId)
            return response()->json([]);

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        $rows = DB::table('jadwal')
            ->join('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
            ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->where('jadwal.guru_id', $guruId)
            ->where('jadwal.hari', $hariId)
            ->where('jadwal.tahun_ajaran', $activeTahunName)
            ->orderBy('jadwal.jam_mulai')
            ->select('jadwal.hari', 'jadwal.jam_mulai', 'jadwal.jam_selesai', 'kelas.nama_kelas as kelas', 'mata_pelajaran.nama_mapel as mata_pelajaran')
            ->get()
            ->map(function ($r) {
                $r->jam_mulai = substr($r->jam_mulai, 0, 5);
                $r->jam_selesai = substr($r->jam_selesai, 0, 5);
                return $r;
            });

        return response()->json($rows);
    }

    public function apiKelas()
    {
        $user = auth()->user();
        $guruId = $user->id;

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        // Get classes from this guru's jadwal only (filtered by active tahun ajaran)
        $rows = DB::table('jadwal as j')
            ->join('kelas as k', 'j.kelas_id', '=', 'k.id')
            ->join('mata_pelajaran as mp', 'j.mata_pelajaran_id', '=', 'mp.id')
            ->where('j.guru_id', $guruId)
            ->where('j.tahun_ajaran', $activeTahunName)
            ->where('mp.nama_mapel', 'not like', 'EKSKUL%')
            ->select('k.id as kelas_id', 'k.nama_kelas', 'mp.nama_mapel')
            ->orderBy('k.nama_kelas')
            ->get();

        // Group by kelas, collect unique mapel per kelas
        $kelasMap = [];
        foreach ($rows as $r) {
            if (!isset($kelasMap[$r->kelas_id])) {
                $kelasMap[$r->kelas_id] = [
                    'kelas_id' => $r->kelas_id,
                    'nama_kelas' => $r->nama_kelas,
                    'mapel' => [],
                ];
            }
            if (!in_array($r->nama_mapel, $kelasMap[$r->kelas_id]['mapel'])) {
                $kelasMap[$r->kelas_id]['mapel'][] = $r->nama_mapel;
            }
        }

        return response()->json(array_values($kelasMap));
    }

    // ── API: jadwal in kelas ───────────────────────────────
    public function apiJadwalKelas(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->id;
        $kelasId = $request->get('kelas', '');

        if (!$kelasId)
            return response()->json([]);

        $LES_MAP = [
            '07:30' => 1,
            '08:10' => 2,
            '08:50' => 3,
            '09:50' => 4,
            '10:30' => 5,
            '11:25' => 6,
            '12:05' => 7,
            '13:15' => 8,
            '13:55' => 9,
        ];

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        $rows = DB::table('jadwal')
            ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->where('jadwal.guru_id', $guruId)
            ->where('jadwal.kelas_id', $kelasId)
            ->where('jadwal.tahun_ajaran', $activeTahunName)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat')")
            ->orderBy('jam_mulai')
            ->select('hari', 'jam_mulai', 'jam_selesai', 'mata_pelajaran.nama_mapel as mata_pelajaran')
            ->get()
            ->map(function ($r) use ($LES_MAP) {
                $jm = substr($r->jam_mulai, 0, 5);
                $js = substr($r->jam_selesai, 0, 5);
                return [
                    'jam_mulai' => $jm,
                    'jam_selesai' => $js,
                    'les' => $LES_MAP[$jm] ?? '?',
                    'hari' => $r->hari,
                    'mata_pelajaran' => $r->mata_pelajaran,
                ];
            });

        return response()->json($rows);
    }

    // ── API: nilai siswa GET ───────────────────────────────
    public function apiNilaiGet(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->id;
        $kelasId = $request->get('kelas', '');

        if (!$kelasId)
            return response()->json(['error' => 'No kelas']);

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        
        $reqSemester = $request->get('semester');
        if ($reqSemester === '1' || $reqSemester === '2') {
            $semesterNum = $reqSemester;
            $activeSemester = $reqSemester === '1' ? 'Ganjil' : 'Genap';
        } else {
            $detected = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
            $semesterNum = $detected;
            $activeSemester = $detected === '1' ? 'Ganjil' : 'Genap';
        }

        $mapelListObj = DB::table('jadwal')
            ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->where('jadwal.guru_id', $guruId)
            ->where('jadwal.kelas_id', $kelasId)
            ->where('jadwal.tahun_ajaran', $activeTahunName)
            ->where('mata_pelajaran.nama_mapel', 'not like', 'EKSKUL%')
            ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
            ->distinct()
            ->get();

        if ($mapelListObj->isEmpty())
            return response()->json(['error' => 'Anda tidak mengajar di kelas ini.']);

        $mapelIds = $mapelListObj->pluck('id')->toArray();
        $mapelList = $mapelListObj->pluck('nama_mapel')->toArray();

        $allowedTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];

        $siswaList = DB::table('siswa')
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $activeTahunId)
            ->orderBy('nama')
            ->select('id', 'nis', 'nama', 'agama')
            ->get();

        $siswaIds = $siswaList->pluck('id')->toArray();
        $grades = [];

        if ($siswaIds) {
            // Query from unified penilaian table
            $nilaiRows = DB::table('penilaian')
                ->where('guru_id', $guruId)
                ->where('tahun_ajaran_id', $activeTahunId)
                ->where('semester', $semesterNum)
                ->whereIn('mata_pelajaran_id', $mapelIds)
                ->whereIn('siswa_id', $siswaIds)
                ->whereIn('assessment_type', $allowedTypes)
                ->select('siswa_id', 'mata_pelajaran_id', 'assessment_type', 'nilai', 'nilai_deskriptif')
                ->get();

            // Need mapel names for grading array
            $mapelIdToName = $mapelListObj->pluck('nama_mapel', 'id')->toArray();

            foreach ($nilaiRows as $n) {
                $mapelName = $mapelIdToName[$n->mata_pelajaran_id] ?? '';
                if (!$mapelName)
                    continue;

                if (!isset($grades[$n->siswa_id][$mapelName])) {
                    $grades[$n->siswa_id][$mapelName] = (object) [];
                }
                $grades[$n->siswa_id][$mapelName]->{$n->assessment_type} = $n->nilai;
                if (!empty($n->nilai_deskriptif)) {
                    $grades[$n->siswa_id][$mapelName]->nilai_deskriptif = $n->nilai_deskriptif;
                }
            }
        }

        $bobot = DB::table('bobot_nilai')->where('tahun_ajaran_id', $activeTahunId)->first();
        if (!$bobot) {
            // fallback defaults
            $bobot = (object)['tugas1' => 7.5, 'uh1' => 7.5, 'tugas2' => 7.5, 'uh2' => 7.5, 'uts' => 30, 'uas' => 40];
        }

        $result = [];
        foreach ($siswaList as $siswa) {
            $sid = $siswa->id;
            $row = ['siswa_id' => $sid, 'nama' => $siswa->nama, 'agama' => $siswa->agama, 'mapel' => []];
            foreach ($mapelList as $mp) {
                $g = $grades[$sid][$mp] ?? null;
                $mapelData = [
                    'nama' => $mp,
                    'nilai_deskriptif' => $g->nilai_deskriptif ?? '',
                ];
                foreach ($allowedTypes as $type) {
                    $mapelData[$type] = $g ? ((isset($g->$type) && $g->$type !== null) ? (int) $g->$type : null) : null;
                }
                
                // Calculate nilai_akhir based on allowed types and weights
                $filled = array_filter(array_map(fn($t) => $mapelData[$t] ?? null, $allowedTypes), fn($v) => $v !== null);
                if (count($filled) === count($allowedTypes)) {
                    $na_raw = 0;
                    foreach ($allowedTypes as $type) {
                        $na_raw += $mapelData[$type] * ($bobot->{$type} / 100);
                    }
                    // Normalize if total weights don't equal 100%
                    $totalWeight = array_sum(array_map(fn($t) => $bobot->{$t}, $allowedTypes));
                    if ($totalWeight > 0 && $totalWeight < 100) {
                        $na_raw = $na_raw / ($totalWeight / 100);
                    }
                    $mapelData['nilai_akhir'] = ceil($na_raw); // Dibulatkan ke atas
                } else {
                    $mapelData['nilai_akhir'] = null;
                }
                
                $row['mapel'][] = $mapelData;
            }
            $result[] = $row;
        }

        // Fetch Lab grades (uas_lab for FISIKA, KIMIA, BIOLOGI) — read-only for Guru
        $labSubjects = ['FISIKA', 'KIMIA', 'BIOLOGI'];
        $labGrades = [];
        if ($siswaIds) {
            $labRows = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('penilaian.siswa_id', $siswaIds)
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $semesterNum)
                ->where('penilaian.assessment_type', 'uas_lab')
                ->whereIn('mata_pelajaran.nama_mapel', $labSubjects)
                ->select('penilaian.siswa_id', 'mata_pelajaran.nama_mapel', 'penilaian.nilai')
                ->get();
            foreach ($labRows as $l) {
                $labGrades[$l->siswa_id][$l->nama_mapel] = (int) $l->nilai;
            }
        }

        // Attach lab data to result
        foreach ($result as &$row) {
            $row['lab'] = [
                'FISIKA' => $labGrades[$row['siswa_id']]['FISIKA'] ?? null,
                'KIMIA' => $labGrades[$row['siswa_id']]['KIMIA'] ?? null,
                'BIOLOGI' => $labGrades[$row['siswa_id']]['BIOLOGI'] ?? null,
            ];
        }
        unset($row);

        $typeStatus = [];
        foreach ($allowedTypes as $type) {
            $typeStatus[$type] = \App\Models\PeriodeNilai::isOpenForSemester($activeTahunId, $semesterNum, $type);
        }

        $myLabs = DB::table('mata_pelajaran')
            ->whereIn('nama_mapel', ['FISIKA', 'KIMIA', 'BIOLOGI'])
            ->where(function($q) use ($user) {
                $q->where('guru_pendamping_lab', $user->nama)
                  ->orWhere('guru_pendamping', $user->nama);
            })
            ->pluck('nama_mapel')
            ->toArray();

        return response()->json([
            'siswa' => $result,
            'mapel_list' => $mapelList,
            'guru_mapel' => implode(', ', $mapelList),
            'active_semester' => $activeSemester,
            'allowed_types' => $allowedTypes,
            'type_status' => $typeStatus,
            'my_labs' => $myLabs,
            'bobot' => $bobot
        ]);
    }

    // ── API: nilai siswa POST ──────────────────────────────
    public function apiNilaiPost(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->id;

        $body = $request->json()->all();
        $siswaId = trim($body['siswa_id'] ?? '');
        $kelasId = trim($body['kelas'] ?? ''); // passed from the view's mapel, kelas
        $mapelName = trim($body['mata_pelajaran'] ?? '');
        $desk = trim($body['nilai_deskriptif'] ?? '');

        $mapelId = DB::table('mata_pelajaran')->where('nama_mapel', $mapelName)->value('id');

        if (!$siswaId || !$mapelId || !$kelasId)
            return response()->json(['error' => 'Missing fields']);

        $teachClass = DB::table('jadwal')->where('guru_id', $guruId)->where('kelas_id', $kelasId)->exists();
        if (!$teachClass)
            return response()->json(['error' => 'Anda tidak mengajar di kelas ini.']);

        // Determine active semester
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $tahunAjaranId = $activeTahun ? $activeTahun->id : 1;
        
        $reqSemester = trim($body['semester'] ?? '');
        if ($reqSemester === '1' || $reqSemester === '2') {
            $semesterNum = (int) $reqSemester;
        } else {
            $semesterNum = (int) \App\Models\PeriodeNilai::detectActiveSemester($tahunAjaranId);
        }

        $allowedTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];

        // Build assessments from only allowed types
        $assessments = [];
        foreach ($allowedTypes as $type) {
            $assessments[$type] = isset($body[$type]) && $body[$type] !== '' ? intval($body[$type]) : null;
        }

        foreach ($assessments as $assessment_type => $nilai) {
            // Check if the period is open before saving
            if (!\App\Models\PeriodeNilai::isOpenForSemester($tahunAjaranId, (string)$semesterNum, $assessment_type)) {
                continue; // Skip closed periods
            }

            if ($nilai !== null || $desk) {
                DB::table('penilaian')->upsert([
                    'siswa_id' => $siswaId,
                    'guru_id' => $guruId,
                    'mata_pelajaran_id' => $mapelId,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'semester' => $semesterNum,
                    'assessment_type' => $assessment_type,
                    'nilai' => $nilai,
                    'nilai_deskriptif' => $desk,
                ], ['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'semester', 'assessment_type'], ['nilai', 'nilai_deskriptif']);
            }
        }

        $filled = array_filter($assessments, fn($v) => $v !== null);
        $na = null;
        if (count($filled) === count($allowedTypes)) {
            $bobot = DB::table('bobot_nilai')->where('tahun_ajaran_id', $tahunAjaranId)->first();
            if (!$bobot) {
                $bobot = (object)['tugas1' => 7.5, 'uh1' => 7.5, 'tugas2' => 7.5, 'uh2' => 7.5, 'uts' => 30, 'uas' => 40];
            }
            $na_raw = 0;
            foreach ($allowedTypes as $type) {
                $na_raw += $assessments[$type] * ($bobot->{$type} / 100);
            }
            $totalWeight = array_sum(array_map(fn($t) => $bobot->{$t}, $allowedTypes));
            if ($totalWeight > 0 && $totalWeight < 100) {
                $na_raw = $na_raw / ($totalWeight / 100);
            }
            $na = ceil($na_raw); // Dibulatkan ke atas
        }

        return response()->json(['success' => true, 'nilai_akhir' => $na]);
    }

    public function apiNilaiLabPost(Request $request)
    {
        $user = auth()->user();
        $guruId = $user->id;

        $body = $request->json()->all();
        $siswaId = trim($body['siswa_id'] ?? '');
        $mapel   = trim($body['mata_pelajaran'] ?? '');
        $value   = isset($body['value']) && $body['value'] !== '' && $body['value'] !== null ? intval($body['value']) : null;

        // Determine active semester / tahun
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $tahunAjaranId = $activeTahun ? $activeTahun->id : 1;
        $semesterNum = (int) \App\Models\PeriodeNilai::detectActiveSemester($tahunAjaranId);

        $labSubjects = ['FISIKA', 'KIMIA', 'BIOLOGI'];
        if (!in_array($mapel, $labSubjects)) {
            return response()->json(['error' => 'Subject is not a lab subject'], 400);
        }

        // Validate teacher is the guru_pendamping_lab or guru_pendamping of this lab
        $isPendamping = DB::table('mata_pelajaran')
            ->where('nama_mapel', $mapel)
            ->where(function($q) use ($user) {
                $q->where('guru_pendamping_lab', $user->nama)
                  ->orWhere('guru_pendamping', $user->nama);
            })
            ->exists();

        if (!$isPendamping) {
            return response()->json(['error' => 'Anda bukan guru pendamping lab untuk ' . $mapel], 403);
        }

        $mapelId = DB::table('mata_pelajaran')->where('nama_mapel', $mapel)->value('id');
        if (!$mapelId) {
            return response()->json(['error' => 'Mapel not found'], 404);
        }

        if ($value !== null) {
            DB::table('penilaian')->upsert([
                'siswa_id'          => $siswaId,
                'mata_pelajaran_id' => $mapelId,
                'guru_id'           => $guruId,
                'tahun_ajaran_id'   => $tahunAjaranId,
                'semester'          => $semesterNum,
                'assessment_type'   => 'uas_lab',
                'nilai'             => $value,
            ], ['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'semester', 'assessment_type'], ['nilai']);
        } else {
            DB::table('penilaian')
                ->where('siswa_id', $siswaId)
                ->where('mata_pelajaran_id', $mapelId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where('semester', $semesterNum)
                ->where('assessment_type', 'uas_lab')
                ->delete();
        }

        return response()->json(['success' => true]);
    }
}
