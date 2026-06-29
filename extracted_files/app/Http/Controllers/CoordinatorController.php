<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CoordinatorController extends Controller
{
    // A. Kelompok Mata Pelajaran Umum
    // B. Kelompok Mata Pelajaran Pilihan


    // ── Dashboard ──────────────────────────────────────────
    public function dashboard()
    {
        $juara_kelas = [];
        $juara_umum = [];

        // Pre-fill juara_kelas with all classes to show them even if empty
        $allKelas = DB::table('kelas')->orderBy('nama_kelas')->pluck('nama_kelas');
        foreach ($allKelas as $k) {
            $juara_kelas[$k] = null;
        }

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        // Fetch all students for the active tahun ajaran
        $students = DB::table('siswa')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->where('siswa.tahun_ajaran_id', $activeTahunId)
            ->select('siswa.id', 'siswa.nama', 'kelas.nama_kelas as kelas', 'siswa.nis')
            ->get();
        $studentData = [];
        foreach ($students as $student) {
            if (!$student->kelas)
                continue;

            $studentData[$student->id] = (object) [
                'siswa_id' => $student->id,
                'nama' => $student->nama,
                'kelas' => $student->kelas,
                'nis' => $student->nis,
                'tingkatan' => explode('-', $student->kelas)[0],
                'scores' => [] // will hold [mata_pelajaran => [scores]]
            ];
        }

        // Get all penilaian records where nilai is not null and > 0 for active tahun_ajaran
        $nilaiRecords = DB::table('penilaian')
            ->whereNotNull('nilai')
            ->where('nilai', '>', 0)
            ->where('tahun_ajaran_id', $activeTahunId)
            ->get();

        foreach ($nilaiRecords as $r) {
            if (!isset($studentData[$r->siswa_id]))
                continue;

            if (!isset($studentData[$r->siswa_id]->scores[$r->mata_pelajaran_id])) {
                $studentData[$r->siswa_id]->scores[$r->mata_pelajaran_id] = [];
            }
            $studentData[$r->siswa_id]->scores[$r->mata_pelajaran_id][] = $r->nilai;
        }

        $results = [];
        foreach ($studentData as $data) {
            if (empty($data->scores))
                continue;

            $subjectAverages = [];
            foreach ($data->scores as $mapel => $scores) {
                $subjectAverages[] = array_sum($scores) / count($scores);
            }
            $data->avg_score = array_sum($subjectAverages) / count($subjectAverages);
            $results[] = $data;
        }

        foreach ($results as $row) {
            $kls = $row->kelas;
            $tingkat = $row->tingkatan;
            $score = round($row->avg_score, 1);
            $row->avg_score_rounded = $score;

            if (!isset($juara_kelas[$kls]) || $juara_kelas[$kls] === null || $score > $juara_kelas[$kls]->avg_score_rounded)
                $juara_kelas[$kls] = $row;
            if (!isset($juara_umum[$tingkat]) || $score > $juara_umum[$tingkat]->avg_score_rounded)
                $juara_umum[$tingkat] = $row;
        }

        uksort($juara_kelas, 'strnatcmp');

        return view('coordinator.dashboard', compact('juara_kelas', 'juara_umum'));
    }

    // ── Profile ────────────────────────────────────────────
    public function profile()
    {
        $user = auth('coordinator')->user();
        return view('coordinator.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'nama' => $request->nama,
            'nip' => $request->nip,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
        ];

        if ($request->input('delete_foto') === '1') {
            $oldFoto = DB::table('users')->where('id', auth('coordinator')->id())->value('foto');
            if ($oldFoto && \Storage::disk('public')->exists($oldFoto)) {
                \Storage::disk('public')->delete($oldFoto);
            }
            $data['foto'] = null;
        } elseif ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'profile_' . auth('coordinator')->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_photos', $filename, 'public');
            $data['foto'] = 'profile_photos/' . $filename;

            // Delete old photo
            $oldFoto = DB::table('users')->where('id', auth('coordinator')->id())->value('foto');
            if ($oldFoto && \Storage::disk('public')->exists($oldFoto)) {
                \Storage::disk('public')->delete($oldFoto);
            }
        }

        DB::table('users')->where('id', auth('coordinator')->id())->update($data);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    // ── Manage Guru ────────────────────────────────────────
    public function manageGuru(Request $request)
    {
        $search = $request->get('search', '');
        $filterField = $request->get('filter_field', 'semua');
        $perPage = 10;
        
        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        $query = DB::table('users')
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->select('users.id', 'users.username', 'users.nama', 'users.nip', 'users.no_hp', 'users.email', 'users.foto', 'users.nuptk', 'users.jenis_kelamin', 'users.tempat_lahir', 'users.alamat', 'users.tanggal_lahir', 'users.role');

        if ($search) {
            if ($filterField === 'semua') {
                $query->where(function ($q) use ($search) {
                    $q->where('users.nama', 'like', "%$search%")
                      ->orWhere('users.nip', 'like', "%$search%")
                      ->orWhere('users.username', 'like', "%$search%")
                      ->orWhereExists(function ($q2) use ($search) {
                          $q2->select(DB::raw(1))
                             ->from('guru_mapel')
                             ->join('mata_pelajaran', 'guru_mapel.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                             ->whereColumn('guru_mapel.guru_id', 'users.id')
                             ->where('mata_pelajaran.nama_mapel', 'like', "%$search%");
                      });
                });
            } elseif (in_array($filterField, ['nama', 'nip', 'username'])) {
                $query->where("users.{$filterField}", 'like', "%$search%");
            } elseif ($filterField === 'mapel') {
                $query->whereExists(function ($q) use ($search) {
                    $q->select(DB::raw(1))
                      ->from('guru_mapel')
                      ->join('mata_pelajaran', 'guru_mapel.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                      ->whereColumn('guru_mapel.guru_id', 'users.id')
                      ->where('mata_pelajaran.nama_mapel', 'like', "%$search%");
                });
            }
        }

        $total = $query->count();
        $page = max(1, (int) $request->get('page', 1));
        $offset = ($page - 1) * $perPage;
        $pageData = $query->orderBy('users.nama')->offset($offset)->limit($perPage)->get();
        $totalPages = max(1, ceil($total / $perPage));
        $mapelList = DB::table('mata_pelajaran')->where('nama_mapel', 'NOT LIKE', 'KEPRIBADIAN%')->orderBy('nama_mapel')->pluck('nama_mapel', 'id')->toArray();

        // Fetch kelas assignments for each guru from jadwal table
        $guruIds = $pageData->pluck('id')->toArray();
        $guruKelasMap = [];
        $guruMapelMap = [];
        $guruMapelIdsMap = [];
        
        if ($guruIds) {
            $kelasData = DB::table('jadwal')
                ->join('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
                ->select('jadwal.guru_id', DB::raw('GROUP_CONCAT(DISTINCT kelas.nama_kelas ORDER BY kelas.nama_kelas) as kelas_list'))
                ->whereIn('jadwal.guru_id', $guruIds)
                ->where('jadwal.tahun_ajaran', $activeTahunName)
                ->groupBy('jadwal.guru_id')
                ->pluck('kelas_list', 'jadwal.guru_id')
                ->toArray();
            $guruKelasMap = $kelasData;

            // Also need raw kelas IDs for editing the select multiple
            $guruKelasIdsMap = [];
            $kelasIdsRaw = DB::table('jadwal')
                ->whereIn('guru_id', $guruIds)
                ->where('tahun_ajaran', $activeTahunName)
                ->select('guru_id', 'kelas_id')
                ->distinct()
                ->get();
            foreach ($kelasIdsRaw as $k) {
                if (!isset($guruKelasIdsMap[$k->guru_id])) $guruKelasIdsMap[$k->guru_id] = [];
                $guruKelasIdsMap[$k->guru_id][] = $k->kelas_id;
            }

            // Fetch mapel from both guru_mapel pivot table and jadwal table (union)
            $guruMapelRaw = DB::table('guru_mapel')
                ->join('mata_pelajaran', 'guru_mapel.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('guru_mapel.guru_id', $guruIds)
                ->select('guru_mapel.guru_id', 'mata_pelajaran.id as mapel_id', 'mata_pelajaran.nama_mapel')
                ->get();

            $guruJadwalMapelRaw = DB::table('jadwal')
                ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('jadwal.guru_id', $guruIds)
                ->where('jadwal.tahun_ajaran', $activeTahunName)
                ->select('jadwal.guru_id', 'mata_pelajaran.id as mapel_id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->get();

            $guruSubjects = [];
            $guruSubjectIds = [];
            foreach ($guruMapelRaw as $row) {
                $guruSubjects[$row->guru_id][$row->mapel_id] = $row->nama_mapel;
                $guruSubjectIds[$row->guru_id][$row->mapel_id] = $row->mapel_id;
            }
            foreach ($guruJadwalMapelRaw as $row) {
                $guruSubjects[$row->guru_id][$row->mapel_id] = $row->nama_mapel;
                $guruSubjectIds[$row->guru_id][$row->mapel_id] = $row->mapel_id;
            }

            foreach ($guruIds as $gid) {
                if (isset($guruSubjects[$gid])) {
                    $guruMapelMap[$gid] = implode(', ', array_unique($guruSubjects[$gid]));
                    $guruMapelIdsMap[$gid] = array_values(array_unique($guruSubjectIds[$gid]));
                } else {
                    $guruMapelMap[$gid] = '-';
                    $guruMapelIdsMap[$gid] = [];
                }
            }
        }
        
        foreach ($pageData as $g) {
            $g->kelas_diajar = $guruKelasMap[$g->id] ?? '-';
            
            // Mapel string for display
            if (isset($guruMapelMap[$g->id])) {
                $g->mapel = $guruMapelMap[$g->id];
            } else {
                $g->mapel = '-';
            }
            
            // Mapel array for select multiple
            $g->mata_pelajaran_ids = $guruMapelIdsMap[$g->id] ?? [];
            // Kelas array for select multiple
            $g->kelas_ids = $guruKelasIdsMap[$g->id] ?? [];
        }

        // Fetch wali kelas assignments
        $waliKelasMap = DB::table('kelas')
            ->whereNotNull('user_walikelas_id')
            ->pluck('id', 'user_walikelas_id')
            ->toArray();
        $waliKelasNameMap = DB::table('kelas')
            ->whereNotNull('user_walikelas_id')
            ->pluck('nama_kelas', 'user_walikelas_id')
            ->toArray();
        foreach ($pageData as $g) {
            $g->is_walikelas = ($g->role === 'wali_kelas') ? true : false;
            $g->kelas_wali_id = $waliKelasMap[$g->id] ?? null;
            $g->kelas_wali_nama = $waliKelasNameMap[$g->id] ?? null;
        }

        $kelasList = DB::table('kelas')->orderBy('nama_kelas')->pluck('nama_kelas', 'id')->toArray();

        return view('coordinator.manage-guru', compact('pageData', 'total', 'page', 'totalPages', 'perPage', 'offset', 'search', 'mapelList', 'kelasList', 'activeTahunName'));
    }

    public function manageGuruAction(Request $request)
    {
        $action = $request->input('action');

        if ($action === 'delete') {
            $ids = array_filter(array_map('trim', $request->input('ids', [])));
            if ($ids)
                DB::table('users')->whereIn('id', $ids)->whereIn('role', ['guru', 'wali_kelas'])->delete();
        }
        if ($action === 'create') {
            $guruId = DB::table('users')->insertGetId([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'role' => 'guru',
                'nip' => $request->nip,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'nuptk' => $request->nuptk,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'alamat' => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
            ]);
        }
        if ($action === 'update') {
            $editId = $request->edit_id;
            DB::table('users')->where('id', $editId)->whereIn('role', ['guru', 'wali_kelas'])->update([
                'username' => $request->username,
                'nama' => $request->nama,
                'nip' => $request->nip,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'nuptk' => $request->nuptk,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'alamat' => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
            ]);
        }
        if ($action === 'reset_password') {
            DB::table('users')->where('id', $request->edit_id)->whereIn('role', ['guru', 'wali_kelas'])
                ->update(['password' => Hash::make($request->new_password)]);
        }
        if ($action === 'force_reset_password_flag') {
            DB::table('users')->where('id', $request->edit_id)->whereIn('role', ['guru', 'wali_kelas'])
                ->update([
                    'needs_password_reset' => 1
                ]);
            return back()->with('success', 'User berhasil ditandai. Guru akan diminta memasukkan password baru saat login berikutnya.');
        }
        return redirect()->route('coordinator.manage-guru');
    }

    // ── Manage Wali Kelas ──────────────────────────────────
    public function manageWalikelas(Request $request)
    {
        $search = $request->get('search', '');
        $filterField = $request->get('filter_field', 'semua');
        $perPage = 10;
        
        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        // Get kelas IDs that have siswa in the active tahun ajaran
        $kelasIdsWithSiswa = DB::table('siswa')
            ->where('tahun_ajaran_id', $activeTahunId)
            ->whereNotNull('kelas_id')
            ->distinct()
            ->pluck('kelas_id');

        $query = DB::table('users')
            ->where('role', 'wali_kelas')
            ->leftJoin('kelas', 'users.id', '=', 'kelas.user_walikelas_id')
            ->whereIn('kelas.id', $kelasIdsWithSiswa)
            ->select('users.id', 'users.username', 'users.nama', 'users.nip', 'kelas.nama_kelas as kelas', 'kelas.id as kelas_id', 'users.no_hp', 'users.foto');

        if ($search) {
            if ($filterField === 'semua') {
                $query->where(function ($q) use ($search) {
                    $q->where('users.nama', 'like', "%$search%")->orWhere('users.nip', 'like', "%$search%")->orWhere('users.username', 'like', "%$search%");
                });
            } elseif (in_array($filterField, ['nama', 'nip', 'username'])) {
                $query->where("users.{$filterField}", 'like', "%$search%");
            } elseif ($filterField === 'kelas') {
                $query->where('kelas.nama_kelas', 'like', "%$search%");
            }
        }

        $total = $query->count();
        $page = max(1, (int) $request->get('page', 1));
        $offset = ($page - 1) * $perPage;
        $pageData = $query->orderBy('users.nama')->offset($offset)->limit($perPage)->get();
        $totalPages = max(1, ceil($total / $perPage));
        
        $kelasList = DB::table('kelas')->orderBy('nama_kelas')->get();

        return view('coordinator.manage-walikelas', compact('pageData', 'total', 'page', 'totalPages', 'perPage', 'offset', 'search', 'kelasList'));
    }

    public function manageWalikelasAction(Request $request)
    {
        $action = $request->input('action');

        if ($action === 'delete') {
            $ids = array_filter(array_map('trim', $request->input('ids', [])));
            if ($ids)
                DB::table('users')->whereIn('id', $ids)->where('role', 'wali_kelas')->delete();
        }
        if ($action === 'create') {
            $id = DB::table('users')->insertGetId([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'role' => 'wali_kelas',
                'nip' => $request->nip,
                'no_hp' => $request->no_hp,
            ]);
            if ($request->kelas) {
                DB::table('kelas')->where('id', $request->kelas)->update(['user_walikelas_id' => $id]);
            }
        }
        if ($action === 'update') {
            DB::table('users')->where('id', $request->edit_id)->where('role', 'wali_kelas')->update([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'no_hp' => $request->no_hp,
            ]);
            
            DB::table('kelas')->where('user_walikelas_id', $request->edit_id)->update(['user_walikelas_id' => null]);
            if ($request->kelas) {
                DB::table('kelas')->where('id', $request->kelas)->update(['user_walikelas_id' => $request->edit_id]);
            }
        }
        if ($action === 'reset_password') {
            DB::table('users')->where('id', $request->edit_id)->where('role', 'wali_kelas')
                ->update(['password' => Hash::make($request->new_password)]);
        }
        if ($action === 'force_reset_password_flag') {
            DB::table('users')->where('id', $request->edit_id)->where('role', 'wali_kelas')
                ->update([
                    'needs_password_reset' => 1
                ]);
            return back()->with('success', 'User berhasil ditandai. Wali Kelas akan diminta memasukkan password baru saat login berikutnya.');
        }
        return redirect()->route('coordinator.manage-walikelas');
    }

    // ── Manage Siswa ───────────────────────────────────────
    public function manageSiswa(Request $request)
    {
        $search = $request->get('search', '');
        $filterField = $request->get('filter_field', 'semua');
        $filterKelas = $request->get('filter_kelas', '');
        $perPage = 10;
        
        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        $query = DB::table('siswa')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->where('siswa.tahun_ajaran_id', $activeTahunId)
            ->select('siswa.id', 'siswa.nama', 'siswa.nis', 'siswa.nisn', 'kelas.nama_kelas as kelas', 'siswa.jenis_kelamin', 'siswa.agama', 'siswa.no_hp', 'siswa.no_hp_orangtua', 'siswa.tempat_lahir', 'siswa.tanggal_lahir', 'siswa.kelas_id');

        if ($search) {
            if ($filterField === 'semua') {
                $query->where(function ($q) use ($search) {
                    $q->where('siswa.nama', 'like', "%$search%")->orWhere('siswa.nis', 'like', "%$search%")->orWhere('siswa.nisn', 'like', "%$search%");
                });
            } elseif (in_array($filterField, ['nama', 'nis', 'nisn'])) {
                $query->where("siswa.{$filterField}", 'like', "%$search%");
            }
        }
        if ($filterKelas)
            $query->where('kelas.id', $filterKelas);

        $kelasList = DB::table('kelas')->orderBy('nama_kelas')->pluck('nama_kelas', 'id');

        $total = $query->count();
        $page = max(1, (int) $request->get('page', 1));
        $offset = ($page - 1) * $perPage;
        $pageData = $query->orderBy('siswa.nama')->offset($offset)->limit($perPage)->get();
        $totalPages = max(1, ceil($total / $perPage));

        return view('coordinator.manage-siswa', compact('pageData', 'total', 'page', 'totalPages', 'perPage', 'offset', 'search', 'filterKelas', 'kelasList'));
    }

    public function manageSiswaAction(Request $request)
    {
        $action = $request->input('action');

        if ($action === 'delete') {
            $ids = array_filter(array_map('trim', $request->input('ids', [])));
            if ($ids)
                DB::table('siswa')->whereIn('id', $ids)->delete();
        }
        if ($action === 'create') {
            $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
            $activeTahunId = $activeTahun ? $activeTahun->id : 1;

            DB::table('siswa')->insert([
                'nama' => $request->nama,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'kelas_id' => $request->kelas ?: null,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'no_hp' => $request->no_hp,
                'no_hp_orangtua' => $request->no_hp_orangtua,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tahun_ajaran_id' => $activeTahunId,
            ]);
        }
        if ($action === 'update') {
            DB::table('siswa')->where('id', $request->edit_id)->update([
                'nama' => $request->nama,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'kelas_id' => $request->kelas ?: null,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'no_hp' => $request->no_hp,
                'no_hp_orangtua' => $request->no_hp_orangtua,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
            ]);
        }
        return redirect()->route('coordinator.manage-siswa');
    }

    // ── Manage Tahun Ajaran ────────────────────────────────
    public function manageTahun()
    {
        $list = DB::table('tahun_ajaran')->orderByDesc('id')->get();
        return view('coordinator.manage-tahun', compact('list'));
    }

    public function manageTahunAction(Request $request)
    {
        $action = $request->input('action');
        if ($action === 'create') {
            DB::table('tahun_ajaran')->update(['is_active' => 0]);
            DB::table('tahun_ajaran')->insert([
                'nama' => $request->nama,
                'is_active' => 1,
            ]);
        }
        if ($action === 'set_active') {
            DB::table('tahun_ajaran')->update(['is_active' => 0]);
            DB::table('tahun_ajaran')->where('id', $request->id)->update(['is_active' => 1]);
        }
        if ($action === 'delete') {
            DB::table('tahun_ajaran')->where('id', $request->id)->delete();
        }
        return redirect()->route('coordinator.manage-tahun');
    }

    // ── Manage Bobot Nilai ────────────────────────────────
    public function manageBobot()
    {
        $tahunList = DB::table('tahun_ajaran')->orderByDesc('id')->get();
        $bobotList = DB::table('bobot_nilai')->get()->keyBy('tahun_ajaran_id');
        return view('coordinator.manage-bobot', compact('tahunList', 'bobotList'));
    }

    public function manageBobotAction(Request $request)
    {
        $action = $request->input('action');
        if ($action === 'save') {
            $tahun_ajaran_id = $request->input('tahun_ajaran_id');
            DB::table('bobot_nilai')->updateOrInsert(
                ['tahun_ajaran_id' => $tahun_ajaran_id],
                [
                    'tugas1' => $request->input('tugas1', 10),
                    'uh1' => $request->input('uh1', 15),
                    'tugas2' => $request->input('tugas2', 10),
                    'uh2' => $request->input('uh2', 15),
                    'uts' => $request->input('uts', 20),
                    'uas' => $request->input('uas', 30),
                    'updated_at' => now()
                ]
            );
            return redirect()->route('coordinator.manage-bobot')->with('success', 'Bobot Nilai berhasil diperbarui.');
        }
        return redirect()->route('coordinator.manage-bobot');
    }

    // ── Manage Kelas ───────────────────────────────────────
    public function manageKelas()
    {
        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        // Show ALL kelas (not filtered by siswa) so admin can setup classes before adding students
        $list = DB::table('kelas')
            ->leftJoin('users', 'kelas.user_walikelas_id', '=', 'users.id')
            ->select('kelas.id', 'kelas.nama_kelas', 'users.nama as wali_kelas', 'kelas.user_walikelas_id')
            ->orderBy('kelas.nama_kelas')
            ->get();

        // Attach student count for each kelas (only for active tahun ajaran, shows 0 if none)
        foreach ($list as $k) {
            $k->jumlah_siswa = DB::table('siswa')->where('kelas_id', $k->id)->where('tahun_ajaran_id', $activeTahunId)->count();
        }

        $waliKelasList = DB::table('users')->whereIn('role', ['guru', 'wali_kelas'])->orderBy('nama')->select('id', 'nama')->get();
        $kelasList = ['X-1', 'X-2', 'X-3', 'XI-1', 'XI-2', 'XI-3', 'XI-4', 'XII-1', 'XII-2', 'XII-3', 'XII-4'];

        return view('coordinator.manage-kelas', compact('list', 'waliKelasList', 'kelasList'));
    }

    public function manageKelasAction(Request $request)
    {
        $action = $request->input('action');

        if ($action === 'create') {
            DB::table('kelas')->insert([
                'nama_kelas' => $request->nama_kelas,
                'user_walikelas_id' => $request->wali_kelas ?: null,
            ]);
        }
        if ($action === 'delete') {
            DB::table('kelas')->where('id', $request->id)->delete();
        }
        if ($action === 'rename') {
            DB::table('kelas')->where('id', $request->id)->update([
                'nama_kelas' => $request->new_nama_kelas,
                'user_walikelas_id' => $request->new_wali_kelas ?: null,
            ]);
            // Syncing nama_kelas cascades automatically to other tables via foreign keys or isn't needed
            // since they reference kelas_id, not nama_kelas string anymore!
        }

        // Sync role wali_kelas vs guru based on current assignments in kelas table:
        $assignedWaliIds = DB::table('kelas')->whereNotNull('user_walikelas_id')->pluck('user_walikelas_id')->toArray();
        if (!empty($assignedWaliIds)) {
            DB::table('users')->whereIn('id', $assignedWaliIds)->whereIn('role', ['guru', 'wali_kelas'])->update(['role' => 'wali_kelas']);
            DB::table('users')->whereNotIn('id', $assignedWaliIds)->whereIn('role', ['guru', 'wali_kelas'])->update(['role' => 'guru']);
        } else {
            DB::table('users')->whereIn('role', ['guru', 'wali_kelas'])->update(['role' => 'guru']);
        }

        return redirect()->route('coordinator.manage-kelas');
    }

    // ── Manage Jadwal ──────────────────────────────────────
    public function manageJadwal(Request $request)
    {
        $filterKelas = $request->get('filter_kelas', '');
        
        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        $kelasList = DB::table('kelas')->orderBy('nama_kelas')->pluck('nama_kelas', 'id');
        $guruListRaw = DB::table('users')
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->orderBy('nama')
            ->get();
            
        $guruIds = $guruListRaw->pluck('id')->toArray();
        $guruMapelMap = [];
        if ($guruIds) {
            // Fetch mapel from both guru_mapel pivot table and jadwal table (union)
            $guruMapelRaw = DB::table('guru_mapel')
                ->join('mata_pelajaran', 'guru_mapel.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('guru_mapel.guru_id', $guruIds)
                ->select('guru_mapel.guru_id', 'mata_pelajaran.nama_mapel')
                ->get();

            $guruJadwalMapelRaw = DB::table('jadwal')
                ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->whereIn('jadwal.guru_id', $guruIds)
                ->where('jadwal.tahun_ajaran', $activeTahunName)
                ->select('jadwal.guru_id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->get();

            $guruSubjects = [];
            foreach ($guruMapelRaw as $row) {
                $guruSubjects[$row->guru_id][] = $row->nama_mapel;
            }
            foreach ($guruJadwalMapelRaw as $row) {
                $guruSubjects[$row->guru_id][] = $row->nama_mapel;
            }

            foreach ($guruIds as $gid) {
                if (isset($guruSubjects[$gid])) {
                    $guruMapelMap[$gid] = implode(', ', array_unique($guruSubjects[$gid]));
                } else {
                    $guruMapelMap[$gid] = '-';
                }
            }
        }
        
        foreach ($guruListRaw as $g) {
            $g->mapel = $guruMapelMap[$g->id] ?? '-';
        }
        $guruList = $guruListRaw;

        // Build jadwal data based on filter
        $jadwalQuery = DB::table('jadwal')
            ->leftJoin('users', 'jadwal.guru_id', '=', 'users.id')
            ->leftJoin('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
            ->leftJoin('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->select('jadwal.*', 'users.nama as guru_nama', 'kelas.nama_kelas as kelas', 'mata_pelajaran.nama_mapel as mata_pelajaran')
            ->where('jadwal.tahun_ajaran', $activeTahunName);

        if ($filterKelas) {
            $jadwalQuery->where('jadwal.kelas_id', $filterKelas);
        }

        $jadwal = $jadwalQuery
            ->orderByRaw("FIELD(jadwal.hari,'Senin','Selasa','Rabu','Kamis','Jumat')")
            ->orderBy('jadwal.jam_mulai')
            ->get();

        // Build timetable map: [jam_mulai][hari] = { mata_pelajaran, guru_nama }
        $jadwalMap = [];
        $displayedJadwalCount = 0;
        foreach ($jadwal as $j) {
            $key = substr($j->jam_mulai, 0, 5);
            if (!isset($jadwalMap[$key][$j->hari])) {
                $jadwalMap[$key][$j->hari] = $j;
                $displayedJadwalCount++;
            }
        }

        $timeSlots = [
            ['jam' => '07:30', 'end' => '08:10', 'les' => 1],
            ['jam' => '08:10', 'end' => '08:50', 'les' => 2],
            ['jam' => '08:50', 'end' => '09:30', 'les' => 3],
            ['break' => 'ISTIRAHAT I'],
            ['jam' => '09:50', 'end' => '10:30', 'les' => 4],
            ['jam' => '10:30', 'end' => '11:10', 'les' => 5],
            ['break' => 'ISTIRAHAT II'],
            ['jam' => '11:25', 'end' => '12:05', 'les' => 6],
            ['jam' => '12:05', 'end' => '12:45', 'les' => 7],
            ['break' => 'ISTIRAHAT III'],
            ['jam' => '13:15', 'end' => '13:55', 'les' => 8],
            ['jam' => '13:55', 'end' => '14:35', 'les' => 9],
        ];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $mapelList = DB::table('mata_pelajaran')->where('nama_mapel', 'NOT LIKE', 'KEPRIBADIAN%')->orderBy('nama_mapel')->pluck('nama_mapel', 'id')->toArray();

        return view('coordinator.manage-jadwal', compact(
            'jadwal',
            'jadwalMap',
            'guruList',
            'kelasList',
            'filterKelas',
            'timeSlots',
            'days',
            'mapelList',
            'displayedJadwalCount'
        ));
    }

    public function manageJadwalAction(Request $request)
    {
        $action = $request->input('action');
        if ($action === 'create') {
            DB::table('jadwal')->insert([
                'guru_id' => $request->guru_id,
                'kelas_id' => $request->kelas,
                'mata_pelajaran_id' => $request->mapel,
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
            ]);
        }
        if ($action === 'update') {
            DB::table('jadwal')->where('id', $request->id)->update([
                'guru_id' => $request->guru_id,
                'kelas_id' => $request->kelas,
                'mata_pelajaran_id' => $request->mapel,
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
            ]);
        }
        if ($action === 'batch_update') {
            $updates = json_decode($request->updates, true);
            if (is_array($updates)) {
                
                // Get active tahun ajaran for new records
                $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
                $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

                foreach ($updates as $u) {
                    if (isset($u['is_new']) && $u['is_new']) {
                        DB::table('jadwal')->insert([
                            'guru_id' => $u['guru_id'],
                            'kelas_id' => $u['kelas_id'],
                            'mata_pelajaran_id' => $u['mata_pelajaran_id'],
                            'hari' => $u['hari'],
                            'jam_mulai' => $u['jam_mulai'],
                            'jam_selesai' => $u['jam_selesai'],
                            'tahun_ajaran' => $activeTahunName,
                        ]);
                    } else {
                        if (isset($u['is_delete']) && $u['is_delete']) {
                            DB::table('jadwal')->where('id', $u['id'])->delete();
                        } else {
                            DB::table('jadwal')->where('id', $u['id'])->update([
                                'guru_id' => $u['guru_id'],
                                'kelas_id' => $u['kelas_id'],
                                'mata_pelajaran_id' => $u['mata_pelajaran_id'],
                                'hari' => $u['hari'],
                                'jam_mulai' => $u['jam_mulai'],
                                'jam_selesai' => $u['jam_selesai'],
                            ]);
                        }
                    }
                }
            }
        }
        if ($action === 'delete') {
            DB::table('jadwal')->where('id', $request->id)->delete();
        }
        return redirect()->route('coordinator.manage-jadwal');
    }

    // ── Manage Subjects ────────────────────────────────────
    public function manageSubjects()
    {
        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';
        
        $allMapel = DB::table('mata_pelajaran')->orderBy('nama_mapel')->get();
        $mapelList = [];
        $ekskulList = [];
        $ekskulPendamping = [];
        $labList = [];
        $labPendamping = [];
        foreach ($allMapel as $m) {
            $nameUpper = strtoupper($m->nama_mapel);
            if (str_starts_with($nameUpper, 'KEPRIBADIAN')) {
                continue;
            }
            if (str_starts_with($nameUpper, 'EKSKUL')) {
                $ekskulList[$m->id] = str_replace('EKSKUL ', '', $m->nama_mapel);
                $ekskulPendamping[$m->id] = $m->guru_pendamping;
            } else {
                $mapelList[$m->id] = $m->nama_mapel;
                if (str_starts_with($nameUpper, 'LAB') || in_array($nameUpper, ['FISIKA', 'KIMIA', 'BIOLOGI'])) {
                    $cleanName = str_replace('LAB ', '', $m->nama_mapel);
                    $cleanName = ucwords(strtolower($cleanName));
                    $labList[$m->id] = 'Lab ' . $cleanName;
                    $labPendamping[$m->id] = $m->guru_pendamping_lab ?? ($m->guru_pendamping ?? '');
                }
            }
        }
        
        // Fetch all mapel assignments from jadwal AND users table to ensure they appear under all subjects they teach
        $guruByMapelRaw = DB::table('jadwal')
            ->where('jadwal.tahun_ajaran', $activeTahunName)
            ->join('users', 'jadwal.guru_id', '=', 'users.id')
            ->select('users.id', 'jadwal.mata_pelajaran_id', 'users.nama')
            ->distinct()
            ->union(
                DB::table('guru_mapel')
                    ->join('users', 'guru_mapel.guru_id', '=', 'users.id')
                    ->whereIn('users.role', ['guru', 'wali_kelas'])
                    ->select('users.id', 'guru_mapel.mata_pelajaran_id', 'users.nama')
            )
            ->get();
            
        // Sort the collection by nama before grouping
        $guruByMapelRaw = $guruByMapelRaw->sortBy('nama');
            
        $guruByMapel = $guruByMapelRaw->groupBy('mata_pelajaran_id');

        $allGuru = DB::table('users')
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->select('id', 'nama')
            ->orderBy('nama')
            ->get();

        return view('coordinator.manage-subjects', compact('mapelList', 'ekskulList', 'guruByMapel', 'allGuru', 'ekskulPendamping', 'labList', 'labPendamping'));
    }

    public function manageSubjectsAction(Request $request)
    {
        if ($request->action === 'create_mapel') {
            $namaMapel = trim($request->nama_mapel);
            $exists = DB::table('mata_pelajaran')->where('nama_mapel', $namaMapel)->exists();
            if ($exists) {
                return back()->with('error', 'Mata pelajaran "' . $namaMapel . '" sudah ada.');
            }
            DB::table('mata_pelajaran')->insert([
                'nama_mapel' => $namaMapel,
            ]);
            return back()->with('success', 'Mata pelajaran "' . $namaMapel . '" berhasil ditambahkan.');
        }
        if ($request->action === 'delete_mapel') {
            $mapelName = DB::table('mata_pelajaran')->where('id', $request->mata_pelajaran_id)->value('nama_mapel');
            DB::table('mata_pelajaran')->where('id', $request->mata_pelajaran_id)->delete();
            return back()->with('success', 'Mata pelajaran "' . $mapelName . '" berhasil dihapus.');
        }
        if ($request->action === 'assign') {
            DB::table('guru_mapel')->insertOrIgnore([
                'guru_id' => $request->guru_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return back()->with('success', 'Guru berhasil ditugaskan.');
        }
        if ($request->action === 'remove') {
            DB::table('guru_mapel')
                ->where('guru_id', $request->guru_id)
                ->where('mata_pelajaran_id', $request->mata_pelajaran_id)
                ->delete();
            return back()->with('success', 'Guru berhasil dihapus dari mata pelajaran.');
        }
        if ($request->action === 'update_ekskul_pendamping') {
            DB::table('mata_pelajaran')
                ->where('id', $request->mata_pelajaran_id)
                ->update([
                    'guru_pendamping' => $request->guru_pendamping,
                ]);
            return back()->with('success', 'Guru pendamping ekstrakurikuler berhasil diperbarui.');
        }
        if ($request->action === 'update_lab_pendamping') {
            DB::table('mata_pelajaran')
                ->where('id', $request->mata_pelajaran_id)
                ->update([
                    'guru_pendamping_lab' => $request->guru_pendamping_lab,
                ]);
            return back()->with('success', 'Guru pendamping lab berhasil diperbarui.');
        }
        return redirect()->route('coordinator.manage-subjects');
    }

    // ── Manage Nilai ───────────────────────────────────────
    public function manageNilai(Request $request)
    {
        $search = $request->get('search', '');
        $filterKelas = $request->get('filter_kelas', '');
        $filterMapel = $request->get('filter_mapel', '');
        $perPage = 15;

        // Determine active semester
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';
        
        $filterSemester = $request->get('filter_semester');
        if ($filterSemester === '1' || $filterSemester === '2') {
            $activeSemester = $filterSemester;
        } else {
            $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }
        // Assessment types are now identical for both semesters
        $assessmentTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];

        // Build a cartesian product of siswa and mata_pelajaran combinations
        // Filter siswa to only those in the active tahun_ajaran
        $baseQuery = DB::table('siswa')
            ->where('siswa.tahun_ajaran_id', $activeTahunId)
            ->select('siswa.id as siswa_id')
            ->crossJoin('mata_pelajaran')
            ->select('siswa.id as siswa_id', 'mata_pelajaran.id as mata_pelajaran_id');

        // Get filtered mata_pelajaran IDs if filtering by name
        if ($filterMapel) {
            $mapelIds = DB::table('mata_pelajaran')->where('nama_mapel', $filterMapel)->pluck('id')->toArray();
            if (!empty($mapelIds)) {
                $baseQuery->whereIn('mata_pelajaran.id', $mapelIds);
            } else {
                // If mapel filter doesn't match anything, show empty
                $baseQuery->where(DB::raw('1'), '=', 0);
            }
        }

        $query = DB::query()
            ->fromSub($baseQuery, 'ns')
            ->join('siswa as s', 'ns.siswa_id', '=', 's.id')
            ->leftJoin('kelas as k', 's.kelas_id', '=', 'k.id')
            ->leftJoin('mata_pelajaran as mp', 'ns.mata_pelajaran_id', '=', 'mp.id')
            ->select('ns.siswa_id', 'ns.mata_pelajaran_id', 's.nama as siswa_nama', 'k.nama_kelas as kelas', 's.nis', 's.id as siswa_db_id', 'mp.nama_mapel')
            ->distinct();

        // Only show religion subjects matching the student's religion profile
        $query->where(function($q) {
            $q->where('mp.nama_mapel', 'not like', 'PEND. AGAMA%')
              ->orWhere(function($sub) {
                  $sub->whereRaw("
                      (s.agama = 'Islam' AND mp.nama_mapel = 'PEND. AGAMA ISLAM') OR
                      (s.agama = 'Kristen' AND mp.nama_mapel = 'PEND. AGAMA KRISTEN') OR
                      (s.agama = 'Budha' AND mp.nama_mapel = 'PEND. AGAMA BUDDHA') OR
                      (s.agama = 'Katolik' AND mp.nama_mapel = 'PEND. AGAMA KATOLIK') OR
                      (s.agama = 'Hindu' AND mp.nama_mapel = 'PEND. AGAMA HINDU') OR
                      (s.agama = 'Konghucu' AND mp.nama_mapel = 'PEND. AGAMA KONGHUCU')
                  ");
              });
        });

        if ($search)
            $query->where('s.nama', 'like', "%$search%");
        if ($filterKelas)
            $query->where('k.id', $filterKelas);

        $total = $query->count();
        $page = max(1, (int) $request->get('page', 1));
        $offset = ($page - 1) * $perPage;

        $pageDataRaw = $query->orderBy('k.nama_kelas')->orderBy('s.nama')->offset($offset)->limit($perPage)->get();

        // Hydrate the scores from penilaian table
        $pageData = collect();
        foreach ($pageDataRaw as $row) {
            $hydrated = (object) (array) $row;
            // Provide a composite key replacement for the frontend
            $hydrated->id = $row->siswa_id . '|' . $row->mata_pelajaran_id;

            // Default scores to null for allowed types only
            foreach ($assessmentTypes as $type)
                $hydrated->$type = null;

            // Query penilaian table for this student/subject combination
            $nilaiRows = DB::table('penilaian')
                ->where('siswa_id', $row->siswa_id)
                ->where('mata_pelajaran_id', $row->mata_pelajaran_id)
                ->where('tahun_ajaran_id', $activeTahunId)
                ->where('semester', $activeSemester)
                ->whereIn('assessment_type', $assessmentTypes)
                ->select('assessment_type', 'nilai')
                ->get();

            foreach ($nilaiRows as $n) {
                if (in_array($n->assessment_type, $assessmentTypes)) {
                    $hydrated->{$n->assessment_type} = $n->nilai;
                }
            }

            // Hydrate ekskul data from penilaian
            $ekskulRow = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->where('penilaian.siswa_id', $row->siswa_id)
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $activeSemester)
                ->where('penilaian.assessment_type', 'ekskul')
                ->select('mata_pelajaran.nama_mapel', 'penilaian.nilai_deskriptif', 'penilaian.ekskul_keterangan')
                ->first();
            $hydrated->ekskul = $ekskulRow ? str_replace('EKSKUL ', '', $ekskulRow->nama_mapel) : null;
            $hydrated->nilai_ekskul = $ekskulRow ? $ekskulRow->nilai_deskriptif : null;
            $hydrated->ekskul_keterangan = $ekskulRow ? $ekskulRow->ekskul_keterangan : null;

            // Calculate Nilai Akhir
            if (!isset($bobot)) {
                $bobot = DB::table('bobot_nilai')->where('tahun_ajaran_id', $activeTahunId)->first();
                if (!$bobot) {
                    $bobot = (object)['tugas1' => 7.5, 'uh1' => 7.5, 'tugas2' => 7.5, 'uh2' => 7.5, 'uts' => 30, 'uas' => 40];
                }
            }
            $filledCount = 0;
            $na_raw = 0;
            if ($bobot) {
                foreach ($assessmentTypes as $type) {
                    if (isset($hydrated->$type) && $hydrated->$type !== null) {
                        $filledCount++;
                        $na_raw += $hydrated->$type * ($bobot->{$type} / 100);
                    }
                }
                if ($filledCount === count($assessmentTypes)) {
                    $totalWeight = array_sum(array_map(fn($t) => $bobot->{$t}, $assessmentTypes));
                    if ($totalWeight > 0 && $totalWeight < 100) {
                        $na_raw = $na_raw / ($totalWeight / 100);
                    }
                    $hydrated->nilai_akhir = ceil($na_raw);
                } else {
                    $hydrated->nilai_akhir = null;
                }
            } else {
                $hydrated->nilai_akhir = null;
            }

            $pageData->push($hydrated);
        }

        $activeTab = $request->get('tab', 'mapel');
        if (!in_array($activeTab, ['mapel', 'ekskul', 'lab'])) {
            $activeTab = 'mapel';
        }

        // Query for Tab 2 (Students with Ekskul)
        $ekskulQuery = DB::table('penilaian as p')
            ->join('siswa as s', 'p.siswa_id', '=', 's.id')
            ->leftJoin('kelas as k', 's.kelas_id', '=', 'k.id')
            ->join('mata_pelajaran as mp', 'p.mata_pelajaran_id', '=', 'mp.id')
            ->where('p.tahun_ajaran_id', $activeTahunId)
            ->where('p.semester', $activeSemester)
            ->where('p.assessment_type', 'ekskul')
            ->select('s.id as siswa_id', 's.nama as siswa_nama', 'k.nama_kelas as kelas', 's.nis', 'mp.nama_mapel', 'p.nilai_deskriptif', 'p.ekskul_keterangan');

        if ($search) $ekskulQuery->where('s.nama', 'like', "%$search%");
        if ($filterKelas) $ekskulQuery->where('k.id', $filterKelas);

        $ekskulTotal = $ekskulQuery->count();
        $ekskulTotalPages = max(1, ceil($ekskulTotal / $perPage));
        $ekskulPageDataRaw = $ekskulQuery->orderBy('k.nama_kelas')->orderBy('s.nama')->offset($offset)->limit($perPage)->get();

        $ekskulPageData = collect();
        foreach ($ekskulPageDataRaw as $row) {
            $h = (object) (array) $row;
            $h->ekskul = str_replace('EKSKUL ', '', $row->nama_mapel);
            $h->nilai_ekskul = $row->nilai_deskriptif;
            $ekskulPageData->push($h);
        }

        // Query for Tab 3 (Students with Lab)
        $labQuery = DB::table('siswa as s')
            ->leftJoin('kelas as k', 's.kelas_id', '=', 'k.id')
            ->whereExists(function($q) use ($activeTahunId, $activeSemester) {
                $q->select(DB::raw(1))
                  ->from('penilaian as p')
                  ->join('mata_pelajaran as mp', 'p.mata_pelajaran_id', '=', 'mp.id')
                  ->whereColumn('p.siswa_id', 's.id')
                  ->where('p.tahun_ajaran_id', $activeTahunId)
                  ->where('p.semester', $activeSemester)
                  ->where('p.assessment_type', 'uas_lab')
                  ->whereIn('mp.nama_mapel', ['FISIKA', 'KIMIA', 'BIOLOGI']);
            })
            ->select('s.id as siswa_id', 's.nama as siswa_nama', 'k.nama_kelas as kelas', 's.nis');

        if ($search) $labQuery->where('s.nama', 'like', "%$search%");
        if ($filterKelas) $labQuery->where('k.id', $filterKelas);

        $labTotal = $labQuery->count();
        $labTotalPages = max(1, ceil($labTotal / $perPage));
        $labPageDataRaw = $labQuery->orderBy('k.nama_kelas')->orderBy('s.nama')->offset($offset)->limit($perPage)->get();

        $labPageData = collect();
        foreach ($labPageDataRaw as $row) {
            $h = (object) (array) $row;
            $h->fisika_lab = null;
            $h->kimia_lab = null;
            $h->biologi_lab = null;

            $labRows = DB::table('penilaian')
                ->join('mata_pelajaran', 'penilaian.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->where('penilaian.siswa_id', $row->siswa_id)
                ->where('penilaian.tahun_ajaran_id', $activeTahunId)
                ->where('penilaian.semester', $activeSemester)
                ->where('penilaian.assessment_type', 'uas_lab')
                ->whereIn('mata_pelajaran.nama_mapel', ['FISIKA', 'KIMIA', 'BIOLOGI'])
                ->select('mata_pelajaran.nama_mapel', 'penilaian.nilai')
                ->get();

            foreach ($labRows as $l) {
                $subjKey = strtolower($l->nama_mapel) . '_lab';
                $h->{$subjKey} = $l->nilai;
            }
            $labPageData->push($h);
        }

        $totalPages = max(1, ceil($total / $perPage));
        $kelasList = DB::table('siswa')
            ->join('kelas as k', 'siswa.kelas_id', '=', 'k.id')
            ->whereNotNull('siswa.kelas_id')
            ->distinct()
            ->orderBy('k.nama_kelas')
            ->pluck('k.nama_kelas', 'k.id');
        $mapelList = DB::table('mata_pelajaran')->where('nama_mapel', 'not like', 'EKSKUL%')->orderBy('nama_mapel')->pluck('nama_mapel', 'id')->toArray();

        return view('coordinator.manage-nilai', compact('pageData', 'total', 'page', 'totalPages', 'perPage', 'offset', 'search', 'filterKelas', 'filterMapel', 'kelasList', 'mapelList', 'activeSemester', 'assessmentTypes', 'ekskulPageData', 'ekskulTotal', 'ekskulTotalPages', 'activeTab', 'labPageData', 'labTotal', 'labTotalPages'));
    }


    // ── Cetak Rapor ─────────────────────────────────────────
    public function cetak(Request $request)
    {
        $user = auth()->user();
        $filterKelas = $request->get('kelas', '');
        
        // Get active tahun ajaran
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';

        $activeSemester = $request->get('semester');
        if (!$activeSemester || !in_array($activeSemester, ['1', '2'])) {
            $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }

        $validTypes = ['UH1', 'UH2', 'UTS', 'UAS'];
        $jenisRapor = in_array(strtoupper($request->get('tipe', 'UH1')), $validTypes)
            ? strtoupper($request->get('tipe', 'UH1'))
            : 'UH1';

        // Generate list of classes that have siswa in the active tahun ajaran
        $kelasIdsWithSiswa = DB::table('siswa')
            ->where('tahun_ajaran_id', $activeTahunId)
            ->whereNotNull('kelas_id')
            ->distinct()
            ->pluck('kelas_id');

        $allKelas = DB::table('kelas')
            ->whereIn('id', $kelasIdsWithSiswa)
            ->orderBy('nama_kelas')
            ->get();

        // Build per-class completion status
        $kelasList = [];
        $kelasProgress = [];
        $anyCompleteClass = false;
        foreach ($allKelas as $k) {
            $kelasList[$k->id] = $k->nama_kelas;
            $isCompleteClass = \App\Models\PeriodeNilai::isClassProgressComplete(
                $k->id, strtolower($jenisRapor), $activeTahunId, $activeSemester
            );
            $kelasProgress[$k->id] = $isCompleteClass;
            if ($isCompleteClass) {
                $anyCompleteClass = true;
            }
        }

        $periodLabel = $jenisRapor === 'UAS' ? 'Rapor Akhir' : $jenisRapor;

        $isGlobalComplete = true;

        // If no class is complete at all
        if (!$anyCompleteClass) {
            $isGlobalComplete = false;
        }

        // If selected class is not complete
        if ($filterKelas) {
            $isGlobalComplete = $kelasProgress[$filterKelas] ?? false;
        }

        $activeYear = $activeTahunName;

        $siswaList = collect();
        $isComplete = false;
        $completedCount = 0;
        $expectedCount = 0;

        if ($filterKelas) {
            $siswaList = DB::table('siswa')
                ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->where('siswa.tahun_ajaran_id', $activeTahunId)
                ->where('siswa.kelas_id', $filterKelas)
                ->orderBy('siswa.nama')
                ->select('siswa.id', 'siswa.nama', 'siswa.nis', 'kelas.nama_kelas')
                ->get();

            // Check completeness: all siswa must have nilai for the selected assessment type
            // for every mapel taught in this kelas (via jadwal)
            $mapelIdsInKelas = DB::table('jadwal')
                ->where('kelas_id', $filterKelas)
                ->where('tahun_ajaran', $activeTahunName)
                ->distinct()
                ->pluck('mata_pelajaran_id')
                ->toArray();

            $siswaIds = $siswaList->pluck('id')->toArray();
            $assessmentType = strtolower($jenisRapor);

            if (!empty($siswaIds) && !empty($mapelIdsInKelas)) {
                $expectedCount = count($siswaIds) * count($mapelIdsInKelas);

                $completedCount = DB::table('penilaian')
                    ->whereIn('siswa_id', $siswaIds)
                    ->whereIn('mata_pelajaran_id', $mapelIdsInKelas)
                    ->where('assessment_type', $assessmentType)
                    ->where('tahun_ajaran_id', $activeTahunId)
                    ->where('semester', $activeSemester)
                    ->whereNotNull('nilai')
                    ->count();

                $isComplete = ($completedCount >= $expectedCount && $expectedCount > 0);
            }
        }

        return view('coordinator.cetak', compact(
            'user', 'kelasList', 'filterKelas', 'siswaList', 'activeYear', 'jenisRapor',
            'isComplete', 'completedCount', 'expectedCount', 'isGlobalComplete', 'activeSemester',
            'kelasProgress'
        ));
    }
    // ── Manage Periode ───────────────────────────────────────────
    public function managePeriode()
    {
        $tahunList = DB::table('tahun_ajaran')->orderByDesc('nama')->get();
        
        // Key periode by "tahun_ajaran_id-semester"
        $periodeRaw = \App\Models\PeriodeNilai::all();
        $periodeList = [];
        foreach ($periodeRaw as $p) {
            $periodeList[$p->tahun_ajaran_id . '-' . $p->semester] = $p;
        }
        
        return view('coordinator.manage-periode', compact('tahunList', 'periodeList'));
    }

    public function managePeriodeAction(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester' => 'required|in:1,2',
        ]);

        \App\Models\PeriodeNilai::updateOrCreate(
            [
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'semester' => $request->semester,
            ],
            [
                'uh1_start' => $request->uh1_start,
                'uh1_end' => $request->uh1_end,
                'uh2_start' => $request->uh2_start,
                'uh2_end' => $request->uh2_end,
                'uts_start' => $request->uts_start,
                'uts_end' => $request->uts_end,
                'uas_start' => $request->uas_start,
                'uas_end' => $request->uas_end,
            ]
        );

        return redirect()->back()->with('success', 'Periode nilai berhasil diperbarui.');
    }

    public function printRapor(Request $request)
    {
        $user = auth()->user();

        // Get active tahun ajaran if not specified
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        $activeSemesterReq = $request->get('semester');
        if ($activeSemesterReq === '1' || $activeSemesterReq === '2') {
            $activeSemesterNum = $activeSemesterReq;
        } else {
            $activeSemesterNum = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }

        $jenis = strtolower($request->get('tipe', 'uh1'));
        $kelasId = $request->get('kelas', '');

        if ($kelasId && !\App\Models\PeriodeNilai::isClassProgressComplete($kelasId, $jenis, $activeTahunId, $activeSemesterNum)) {
            return redirect()->route('coordinator.cetak', ['tipe' => strtoupper($jenis), 'semester' => $activeSemesterNum])
                ->with('error', 'Cetak rapor ditangguhkan karena penginputan nilai/absensi kelas ini belum mencapai progress 100%.');
        }

        $ids = array_filter(array_map('trim', explode(',', $request->get('siswa_ids', ''))));
        $tahun = $request->get('tahun') ?: ($activeTahun ? $activeTahun->nama : '2025/2026');

        $subjects = ['PEND. AGAMA & BUDI PEKERTI', 'PEND. PANCASILA / PKN', 'BAHASA INDONESIA', 'BAHASA INGGRIS', 'MATEMATIKA / MATEMATIKA WAJIB', 'SENI MUSIK / SENI BUDAYA', 'PENJAS ORKES', 'SEJARAH / SEJARAH INDONESIA', 'PRAKARYA & KEWIRAUSAHAAN', 'GEOGRAFI', 'EKONOMI', 'SOSIOLOGI', 'SEJARAH (Tingkat Lanjut)', 'FISIKA', 'KIMIA', 'BIOLOGI', 'MATEMATIKA (Tingkat Lanjut)', 'INFORMATIKA / TIK', 'BAHASA MANDARIN', 'CONVERSATION'];
        $tahunRow = DB::table('tahun_ajaran')->where('nama', $tahun)->first();
        $tahunId = $tahunRow ? $tahunRow->id : 1;
        $activeSemester = $activeSemesterNum === '1' ? 'Ganjil' : 'Genap';

        $siswaList = DB::table('siswa')
            ->leftJoin('kelas as k', 'siswa.kelas_id', '=', 'k.id')
            ->leftJoin('users as wk', 'k.user_walikelas_id', '=', 'wk.id')
            ->whereIn('siswa.id', $ids)
            ->when($kelasId, function ($q) use ($kelasId) {
                return $q->where('k.id', $kelasId);
            })
            ->select('siswa.*', 'k.nama_kelas as kelas', 'wk.nama as wali_kelas_nama')
            ->orderBy('siswa.nama')
            ->get();

        $kelasName = '';
        if ($siswaList->isNotEmpty()) {
            $kelasName = $siswaList->first()->kelas ?? '';
        } elseif ($kelasId) {
            $kelasName = DB::table('kelas')->where('id', $kelasId)->value('nama_kelas') ?? '';
        }

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
            ->select('penilaian.siswa_id', 'mata_pelajaran.nama_mapel', 'penilaian.nilai_deskriptif')
            ->get();
        
        $ekskulData = [];
        foreach ($ekskulRecords as $rec) {
            $name = str_replace('EKSKUL ', '', $rec->nama_mapel);
            $ekskulData[$rec->siswa_id][] = (object)['nama' => $name, 'predikat' => $rec->nilai_deskriptif];
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

        $kepPeriodForJenis = $kepPeriodMap[$jenis] ?? 'kepribadian_uh1';

        $kelas = $kelasName;
        return view('walikelas.print_rapor', compact('siswaList', 'grades', 'subjects', 'jenis', 'tahun', 'kelas', 'user', 'ekskulData', 'absensiData', 'absensiDataByPeriod', 'labData', 'activeSemester', 'kepribadianData', 'kepPeriodForJenis'));
    }

    // ── Progress Monitoring ─────────────────────────────────────
    public function progress(Request $request)
    {
        // Active tahun ajaran & semester
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeSemester = $request->get('semester');
        if (!$activeSemester || !in_array($activeSemester, ['1', '2'])) {
            $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }
        $tahunAjaranLabel = $activeTahun ? $activeTahun->nama : '-';
        $semesterLabel = $activeSemester === '1' ? 'Ganjil' : 'Genap';

        // Auto-detect active period type
        $activePeriodType = \App\Models\PeriodeNilai::detectActivePeriodType($activeTahunId);

        // Filter: default to active period type (auto-detect)
        $filterType = $request->get('filter_type');
        $validFilters = ['uh1', 'uts', 'uh2', 'uas', 'all'];
        if (!$filterType || !in_array($filterType, $validFilters)) {
            $filterType = $activePeriodType ?: 'all';
        }

        // Map filter to assessment types using the model helper
        $assessmentTypes = \App\Models\PeriodeNilai::getAssessmentTypesForFilter($filterType);
        $allAssessmentTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];

        // ── Get actual date range from periode_nilai ──
        $periodeNotSet = false;
        $dateRanges = [];
        $schoolDays = 0;
        $monthLabel = '';
        $periodeStartDate = null;
        $periodeEndDate = null;

        if ($filterType !== 'all') {
            $range = \App\Models\PeriodeNilai::getDateRangeForType($activeTahunId, $activeSemester, $filterType);
            if ($range) {
                $dateRanges[] = $range;
                $schoolDays = \App\Models\PeriodeNilai::calculateSchoolDays($range[0], $range[1]);
                $periodeStartDate = $range[0];
                $periodeEndDate = $range[1];
                $startFormatted = \Carbon\Carbon::parse($range[0])->translatedFormat('d M Y');
                $endFormatted = \Carbon\Carbon::parse($range[1])->translatedFormat('d M Y');
                $periodLabel = \App\Models\PeriodeNilai::getPeriodLabel($filterType);
                $monthLabel = "{$periodLabel} ({$startFormatted} – {$endFormatted})";
            } else {
                $periodeNotSet = true;
                $monthLabel = strtoupper($filterType) . ' (Periode belum diatur)';
            }
        } else {
            // 'all' — combine all 4 period date ranges from active semester
            $types = ['uh1', 'uts', 'uh2', 'uas'];
            $allRangeLabels = [];
            foreach ($types as $t) {
                $range = \App\Models\PeriodeNilai::getDateRangeForType($activeTahunId, $activeSemester, $t);
                if ($range) {
                    $dateRanges[] = $range;
                    $schoolDays += \App\Models\PeriodeNilai::calculateSchoolDays($range[0], $range[1]);
                    $allRangeLabels[] = strtoupper($t);
                }
            }
            if (empty($dateRanges)) {
                $periodeNotSet = true;
                $monthLabel = 'Semua Periode (Belum diatur)';
            } else {
                $monthLabel = 'Semua Periode (' . implode(', ', $allRangeLabels) . ')';
            }
        }

        // ── GURU PROGRESS ──
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';
        
        $guruList = DB::table('users')
            ->whereIn('role', ['guru', 'wali_kelas'])
            ->whereExists(function ($q) use ($activeTahunName) {
                $q->select(DB::raw(1))
                  ->from('jadwal')
                  ->where('jadwal.tahun_ajaran', $activeTahunName)
                  ->whereColumn('jadwal.guru_id', 'users.id');
            })
            ->orderBy('users.nama')
            ->get();

        $guruProgress = [];
        foreach ($guruList as $guru) {
            $assignments = DB::table('jadwal')
                ->where('guru_id', $guru->id)
                ->where('jadwal.tahun_ajaran', $activeTahunName)
                ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->join('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
                ->select('jadwal.mata_pelajaran_id', 'mata_pelajaran.nama_mapel as mapel_name', 'jadwal.kelas_id', 'kelas.nama_kelas')
                ->distinct()
                ->get();

            if ($assignments->isEmpty()) {
                $guruProgress[] = (object)[
                    'id' => $guru->id,
                    'nama' => $guru->nama,
                    'mapel' => '-',
                    'kelas_details' => [],
                    'total_expected' => 0,
                    'total_completed' => 0,
                    'percentage' => 0,
                ];
                continue;
            }

            $mapelNames = $assignments->pluck('mapel_name')->unique()->implode(', ');

            $kelasDetails = [];
            $totalExpected = 0;
            $totalCompleted = 0;

            foreach ($assignments as $assignment) {
                $kelasId = $assignment->kelas_id;
                $mapelId = $assignment->mata_pelajaran_id;
                $kelasName = $assignment->nama_kelas;

                $siswaInKelas = DB::table('siswa')->where('kelas_id', $kelasId)->where('tahun_ajaran_id', $activeTahunId)->orderBy('nama')->get();
                
                // Filter matching students by religion
                $matchedSiswaIds = [];
                $matchedSiswaList = [];
                foreach ($siswaInKelas as $siswa) {
                    if (\App\Models\PeriodeNilai::matchesReligion($siswa->agama, $assignment->mapel_name)) {
                        $matchedSiswaIds[] = $siswa->id;
                        $matchedSiswaList[] = $siswa;
                    }
                }

                $siswaCount = count($matchedSiswaIds);
                if ($siswaCount === 0) continue;

                $expectedPerKelas = $siswaCount * count($assessmentTypes);
                $totalExpected += $expectedPerKelas;

                $siswaIdList = implode(',', $matchedSiswaIds);
                $typeList = implode("','", $assessmentTypes);
                
                $completedRecords = (int) DB::selectOne("
                    SELECT COUNT(*) as cnt FROM (
                        SELECT DISTINCT siswa_id, assessment_type 
                        FROM penilaian 
                        WHERE mata_pelajaran_id = ? 
                        AND tahun_ajaran_id = ?
                        AND semester = ?
                        AND siswa_id IN ({$siswaIdList})
                        AND assessment_type IN ('{$typeList}')
                        AND nilai IS NOT NULL
                    ) as sub
                ", [$mapelId, $activeTahunId, $activeSemester])->cnt;

                $totalCompleted += $completedRecords;

                $missingSiswa = [];
                foreach ($matchedSiswaList as $siswa) {
                    $siswaCompleted = DB::table('penilaian')
                        ->where('mata_pelajaran_id', $mapelId)
                        ->where('tahun_ajaran_id', $activeTahunId)
                        ->where('semester', $activeSemester)
                        ->where('siswa_id', $siswa->id)
                        ->whereIn('assessment_type', $assessmentTypes)
                        ->whereNotNull('nilai')
                        ->distinct()
                        ->pluck('assessment_type')
                        ->unique()
                        ->toArray();

                    $missingTypes = array_diff($assessmentTypes, $siswaCompleted);
                    if (!empty($missingTypes)) {
                        $missingSiswa[] = (object)[
                            'nama' => $siswa->nama,
                            'nis' => $siswa->nis,
                            'missing_types' => array_values($missingTypes),
                            'completed_count' => count($siswaCompleted),
                            'total_types' => count($assessmentTypes),
                        ];
                    }
                }

                $pct = $expectedPerKelas > 0 ? round(($completedRecords / $expectedPerKelas) * 100, 1) : 0;
                
                $displayKelasName = $assignments->pluck('mata_pelajaran_id')->unique()->count() > 1 
                    ? "{$kelasName} ({$assignment->mapel_name})" 
                    : $kelasName;

                $kelasDetails[] = (object)[
                    'kelas_id' => $kelasId,
                    'kelas_name' => $displayKelasName,
                    'siswa_count' => $siswaCount,
                    'expected' => $expectedPerKelas,
                    'completed' => $completedRecords,
                    'percentage' => $pct,
                    'missing_siswa' => $missingSiswa,
                ];
            }

            $overallPct = $totalExpected > 0 ? round(($totalCompleted / $totalExpected) * 100, 1) : 0;
            $guruProgress[] = (object)[
                'id' => $guru->id,
                'nama' => $guru->nama,
                'mapel' => $mapelNames ?: '-',
                'kelas_details' => $kelasDetails,
                'total_expected' => $totalExpected,
                'total_completed' => $totalCompleted,
                'percentage' => $overallPct,
            ];
        }

        // ── WALIKELAS PROGRESS ──
        $kelasIdsWithSiswaWk = DB::table('siswa')
            ->where('tahun_ajaran_id', $activeTahunId)
            ->whereNotNull('kelas_id')
            ->distinct()
            ->pluck('kelas_id');

        $walikelasList = DB::table('users')
            ->where('role', 'wali_kelas')
            ->leftJoin('kelas', 'users.id', '=', 'kelas.user_walikelas_id')
            ->whereIn('kelas.id', $kelasIdsWithSiswaWk)
            ->select('users.id', 'users.nama', 'kelas.nama_kelas as kelas', 'kelas.id as kelas_id')
            ->orderBy('users.nama')
            ->get();

        $walikelasProgress = [];
        foreach ($walikelasList as $wk) {
            if (!$wk->kelas_id) {
                $walikelasProgress[] = (object)[
                    'id' => $wk->id,
                    'nama' => $wk->nama,
                    'kelas' => '-',
                    'siswa_count' => 0,
                    'expected' => 0,
                    'completed' => 0,
                    'percentage' => 0,
                    'missing_siswa' => [],
                ];
                continue;
            }

            $siswaInKelas = DB::table('siswa')->where('kelas_id', $wk->kelas_id)->where('tahun_ajaran_id', $activeTahunId)->orderBy('nama')->get();
            $siswaCount = $siswaInKelas->count();
            $expectedTotal = $siswaCount * $schoolDays;

            $completedTotal = 0;
            $missingSiswa = [];

            if ($siswaCount > 0 && !empty($dateRanges)) {
                $siswaIds = $siswaInKelas->pluck('id')->toArray();
                
                $query = DB::table('kehadiran')
                    ->whereIn('siswa_id', $siswaIds)
                    ->where('kelas_id', $wk->kelas_id)
                    ->where(function($q) use ($dateRanges) {
                        foreach ($dateRanges as $range) {
                            $q->orWhereBetween('tanggal', [$range[0], $range[1]]);
                        }
                    });

                $completedTotal = $query->count();

                $absensiPerSiswa = DB::table('kehadiran')
                    ->select('siswa_id', DB::raw('count(*) as total'))
                    ->whereIn('siswa_id', $siswaIds)
                    ->where('kelas_id', $wk->kelas_id)
                    ->where(function($q) use ($dateRanges) {
                        foreach ($dateRanges as $range) {
                            $q->orWhereBetween('tanggal', [$range[0], $range[1]]);
                        }
                    })
                    ->groupBy('siswa_id')
                    ->pluck('total', 'siswa_id');

                foreach ($siswaInKelas as $siswa) {
                    $siswaAbsensi = $absensiPerSiswa->get($siswa->id, 0);

                    if ($siswaAbsensi < $schoolDays) {
                        $missingSiswa[] = (object)[
                            'nama' => $siswa->nama,
                            'nis' => $siswa->nis,
                            'completed_days' => $siswaAbsensi,
                            'total_days' => $schoolDays,
                        ];
                    }
                }
            }

            $pct = $expectedTotal > 0 ? round(($completedTotal / $expectedTotal) * 100, 1) : 0;
            $walikelasProgress[] = (object)[
                'id' => $wk->id,
                'nama' => $wk->nama,
                'kelas' => $wk->kelas ?? '-',
                'siswa_count' => $siswaCount,
                'expected' => $expectedTotal,
                'completed' => $completedTotal,
                'percentage' => $pct,
                'missing_siswa' => $missingSiswa,
            ];
        }

        return view('coordinator.progress', compact(
            'guruProgress', 'walikelasProgress',
            'activeSemester', 'assessmentTypes', 'allAssessmentTypes', 'filterType', 'tahunAjaranLabel',
            'monthLabel', 'schoolDays', 'activePeriodType', 'periodeNotSet', 'semesterLabel',
            'periodeStartDate', 'periodeEndDate'
        ));
    }


    // ── Progress Detail (Guru + Kelas Nilai View) ───────────────
    public function progressDetail(Request $request)
    {
        $guruId = $request->get('guru_id');
        $kelasId = $request->get('kelas_id');

        if (!$guruId || !$kelasId) {
            return redirect()->route('coordinator.progress')->with('error', 'Parameter tidak lengkap.');
        }

        // Get guru info
        $guru = DB::table('users')
            ->where('id', $guruId)
            ->select('id', 'nama')
            ->first();

        if (!$guru) {
            return redirect()->route('coordinator.progress')->with('error', 'Guru tidak ditemukan.');
        }

        // Get kelas info
        $kelas = DB::table('kelas')->where('id', $kelasId)->first();
        if (!$kelas) {
            return redirect()->route('coordinator.progress')->with('error', 'Kelas tidak ditemukan.');
        }

        // Active tahun ajaran & semester
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeSemester = $request->get('semester');
        if (!$activeSemester || !in_array($activeSemester, ['1', '2'])) {
            $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }
        $activeTahunName = $activeTahun ? $activeTahun->nama : '2025/2026';
        $semesterLabel = $activeSemester === '1' ? 'Ganjil' : 'Genap';

        // Get filter_type from request — determines which assessment columns to show
        $filterType = $request->get('filter_type', 'all');
        $validFilters = ['uh1', 'uts', 'uh2', 'uas', 'all'];
        if (!in_array($filterType, $validFilters)) {
            $filterType = 'all';
        }
        $assessmentTypes = \App\Models\PeriodeNilai::getAssessmentTypesForFilter($filterType);
        $periodLabel = $filterType !== 'all' ? \App\Models\PeriodeNilai::getPeriodLabel($filterType) : 'Nilai Akhir';

        // Get mata_pelajaran_id(s) this guru teaches in this kelas from jadwal
        $mapelIds = DB::table('jadwal')
            ->where('guru_id', $guruId)
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran', $activeTahunName)
            ->distinct()
            ->pluck('mata_pelajaran_id')
            ->toArray();

        // Get mapel name(s) for display
        $mapelNames = DB::table('mata_pelajaran')
            ->whereIn('id', $mapelIds)
            ->pluck('nama_mapel')
            ->implode(', ');
        $guru->mapel = $mapelNames ?: '-';

        // Get all siswa in kelas
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $siswaList = DB::table('siswa')
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $activeTahunId)
            ->orderBy('nama')
            ->get();

        // Get all penilaian records for these siswa + this guru's mapel(s) in this kelas
        $siswaIds = $siswaList->pluck('id')->toArray();
        $nilaiRecords = collect();
        if (!empty($siswaIds) && !empty($mapelIds)) {
            $nilaiRecords = DB::table('penilaian')
                ->whereIn('mata_pelajaran_id', $mapelIds)
                ->whereIn('siswa_id', $siswaIds)
                ->where('semester', $activeSemester)
                ->whereIn('assessment_type', $assessmentTypes)
                ->whereNotNull('nilai')
                ->select('siswa_id', 'assessment_type', 'nilai')
                ->get();
        }

        // Build a grades map: [siswa_id][assessment_type] = nilai
        $gradesMap = [];
        foreach ($nilaiRecords as $r) {
            // If duplicates, just take the first one
            if (!isset($gradesMap[$r->siswa_id][$r->assessment_type])) {
                $gradesMap[$r->siswa_id][$r->assessment_type] = $r->nilai;
            }
        }

        // Build display data
        $tableData = [];
        foreach ($siswaList as $siswa) {
            $row = (object)[
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'scores' => [],
                'completed' => 0,
                'total' => count($assessmentTypes),
                'avg' => null,
            ];

            $sum = 0;
            $count = 0;
            foreach ($assessmentTypes as $type) {
                $val = $gradesMap[$siswa->id][$type] ?? null;
                $row->scores[$type] = $val;
                if ($val !== null) {
                    $row->completed++;
                    $sum += $val;
                    $count++;
                }
            }
            $row->avg = $count > 0 ? round($sum / $count, 1) : null;
            $row->is_complete = ($row->completed === $row->total);
            $tableData[] = $row;
        }

        $totalSiswa = count($tableData);
        $completeSiswa = collect($tableData)->where('is_complete', true)->count();
        $incompleteSiswa = $totalSiswa - $completeSiswa;

        // KKM threshold
        $kkm = 65;
        $belowKkmCount = 0;
        $aboveKkmCount = 0;
        foreach ($tableData as $row) {
            $hasBelowKkm = false;
            foreach ($row->scores as $val) {
                if ($val !== null && $val < $kkm) {
                    $hasBelowKkm = true;
                    break;
                }
            }
            if ($hasBelowKkm) {
                $belowKkmCount++;
            } else {
                // Only count as "above KKM" if they have at least one score
                $hasAnyScore = false;
                foreach ($row->scores as $val) {
                    if ($val !== null) { $hasAnyScore = true; break; }
                }
                if ($hasAnyScore) $aboveKkmCount++;
            }
        }

        return view('coordinator.progress-detail', compact(
            'guru', 'kelas', 'activeSemester', 'assessmentTypes',
            'tableData', 'totalSiswa', 'completeSiswa', 'incompleteSiswa',
            'kkm', 'belowKkmCount', 'aboveKkmCount',
            'filterType', 'periodLabel', 'semesterLabel'
        ));
    }

    // ── Progress Detail (Walikelas + Absensi View) ───────────────
    public function progressAbsensiDetail(Request $request)
    {
        $walikelasId = $request->get('walikelas_id');
        $filterType = $request->get('filter_type', 'all');

        if (!$walikelasId) {
            return redirect()->route('coordinator.progress', ['tab' => 'walikelas'])->with('error', 'Parameter tidak lengkap.');
        }

        // Get walikelas info & linked kelas
        $walikelas = DB::table('users')
            ->where('role', 'wali_kelas')
            ->where('users.id', $walikelasId)
            ->leftJoin('kelas', 'users.id', '=', 'kelas.user_walikelas_id')
            ->select('users.id', 'users.nama', 'kelas.id as kelas_id', 'kelas.nama_kelas as kelas')
            ->first();

        if (!$walikelas || !$walikelas->kelas_id) {
            return redirect()->route('coordinator.progress', ['tab' => 'walikelas'])->with('error', 'Wali kelas atau kelas tidak ditemukan.');
        }

        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeSemester = $request->get('semester');
        if (!$activeSemester || !in_array($activeSemester, ['1', '2'])) {
            $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        }

        // Get actual date ranges from periode_nilai
        $dateRanges = [];
        $schoolDays = 0;
        $monthLabel = '';
        $periodeNotSet = false;

        $validFilters = ['uh1', 'uts', 'uh2', 'uas', 'all'];
        if (!in_array($filterType, $validFilters)) {
            $filterType = 'all';
        }

        if ($filterType !== 'all') {
            $range = \App\Models\PeriodeNilai::getDateRangeForType($activeTahunId, $activeSemester, $filterType);
            if ($range) {
                $dateRanges[] = $range;
                $schoolDays = \App\Models\PeriodeNilai::calculateSchoolDays($range[0], $range[1]);
                $startFormatted = \Carbon\Carbon::parse($range[0])->translatedFormat('d M Y');
                $endFormatted = \Carbon\Carbon::parse($range[1])->translatedFormat('d M Y');
                $periodLabel = \App\Models\PeriodeNilai::getPeriodLabel($filterType);
                $monthLabel = "{$periodLabel} ({$startFormatted} – {$endFormatted})";
            } else {
                $periodeNotSet = true;
                $monthLabel = strtoupper($filterType) . ' (Periode belum diatur)';
            }
        } else {
            $types = ['uh1', 'uts', 'uh2', 'uas'];
            $allRangeLabels = [];
            foreach ($types as $t) {
                $range = \App\Models\PeriodeNilai::getDateRangeForType($activeTahunId, $activeSemester, $t);
                if ($range) {
                    $dateRanges[] = $range;
                    $schoolDays += \App\Models\PeriodeNilai::calculateSchoolDays($range[0], $range[1]);
                    $allRangeLabels[] = strtoupper($t);
                }
            }
            if (empty($dateRanges)) {
                $periodeNotSet = true;
                $monthLabel = 'Semua Periode (Belum diatur)';
            } else {
                $monthLabel = 'Semua Periode (' . implode(', ', $allRangeLabels) . ')';
            }
        }

        $siswaList = DB::table('siswa')
            ->where('kelas_id', $walikelas->kelas_id)
            ->orderBy('nama')
            ->get();

        $siswaIds = $siswaList->pluck('id')->toArray();
        $kehadiranRecords = collect();
        if (!empty($siswaIds) && !empty($dateRanges)) {
            $query = DB::table('kehadiran')
                ->whereIn('siswa_id', $siswaIds)
                ->where('kelas_id', $walikelas->kelas_id)
                ->where(function($q) use ($dateRanges) {
                    foreach ($dateRanges as $range) {
                        $q->orWhereBetween('tanggal', [$range[0], $range[1]]);
                    }
                });
            $kehadiranRecords = $query->get();
        }

        // Build display data
        $tableData = [];
        foreach ($siswaList as $siswa) {
            $siswaKehadiranCount = $kehadiranRecords->where('siswa_id', $siswa->id)->count();
            $attendancePct = $schoolDays > 0 ? round(($siswaKehadiranCount / $schoolDays) * 100, 1) : 0;
            
            $row = (object)[
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'completed' => $siswaKehadiranCount,
                'total' => $schoolDays,
                'attendance_pct' => $attendancePct,
                'is_complete' => $siswaKehadiranCount >= $schoolDays
            ];
            $tableData[] = $row;
        }

        $totalSiswa = count($tableData);
        $completeSiswa = collect($tableData)->where('is_complete', true)->count();
        $incompleteSiswa = $totalSiswa - $completeSiswa;

        // Attendance threshold
        $attendanceThreshold = 75;
        $belowAttendanceCount = 0;
        $aboveAttendanceCount = 0;
        foreach ($tableData as $row) {
            if ($row->completed > 0 && $row->attendance_pct < $attendanceThreshold) {
                $belowAttendanceCount++;
            } elseif ($row->completed > 0 && $row->attendance_pct >= $attendanceThreshold) {
                $aboveAttendanceCount++;
            }
        }

        return view('coordinator.progress-absensi-detail', compact(
            'walikelas', 'monthLabel', 'filterType', 'periodeNotSet',
            'tableData', 'totalSiswa', 'completeSiswa', 'incompleteSiswa', 'schoolDays',
            'attendanceThreshold', 'belowAttendanceCount', 'aboveAttendanceCount'
        ));
    }

    // ── Kenaikan Kelas ────────────────────────────────────────
    public function kenaikanKelas(Request $request)
    {
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeTahunName = $activeTahun ? $activeTahun->nama : '-';
        $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);

        // All tahun_ajaran for target selection
        $tahunList = DB::table('tahun_ajaran')->orderByDesc('id')->get();

        // Get all kelas with student counts for the active tahun ajaran
        $kelasList = DB::table('kelas')
            ->orderBy('nama_kelas')
            ->get();

        foreach ($kelasList as $k) {
            $k->jumlah_siswa = DB::table('siswa')
                ->where('kelas_id', $k->id)
                ->where('tahun_ajaran_id', $activeTahunId)
                ->count();
        }

        // Determine the auto-upgrade target kelas for each kelas
        // X→XI, XI→XII, XII→Lulus (no target kelas)
        $upgradeTargetMap = [];
        foreach ($kelasList as $k) {
            $nama = $k->nama_kelas;
            if (preg_match('/^X-(\d+)$/', $nama, $m)) {
                $target = 'XI-' . $m[1];
                $targetKelas = $kelasList->firstWhere('nama_kelas', $target);
                $upgradeTargetMap[$k->id] = $targetKelas ? $targetKelas->id : null;
            } elseif (preg_match('/^XI-(\d+)$/', $nama, $m)) {
                $target = 'XII-' . $m[1];
                $targetKelas = $kelasList->firstWhere('nama_kelas', $target);
                $upgradeTargetMap[$k->id] = $targetKelas ? $targetKelas->id : null;
            } elseif (preg_match('/^XII-/', $nama)) {
                $upgradeTargetMap[$k->id] = 'lulus'; // XII graduates
            }
        }

        // If a kelas is selected, load students
        $selectedKelasId = $request->get('kelas_id', '');
        $selectedKelas = null;
        $siswaList = collect();
        $targetKelasName = null;

        if ($selectedKelasId) {
            $selectedKelas = $kelasList->firstWhere('id', (int) $selectedKelasId);
            if ($selectedKelas) {
                $siswaList = DB::table('siswa')
                    ->where('kelas_id', $selectedKelasId)
                    ->where('tahun_ajaran_id', $activeTahunId)
                    ->orderBy('nama')
                    ->get();

                // Determine target kelas name for display
                $targetId = $upgradeTargetMap[$selectedKelas->id] ?? null;
                if ($targetId === 'lulus') {
                    $targetKelasName = 'LULUS (Kelulusan)';
                } elseif ($targetId) {
                    $tk = $kelasList->firstWhere('id', $targetId);
                    $targetKelasName = $tk ? $tk->nama_kelas : null;
                }
            }
        }

        // Determine available target classes for manual assignment
        $availableTargetKelas = collect();
        if ($selectedKelas) {
            $nama = $selectedKelas->nama_kelas;
            if (preg_match('/^X-/', $nama)) {
                $availableTargetKelas = $kelasList->filter(function($k) {
                    return preg_match('/^XI-/', $k->nama_kelas);
                })->values();
            } elseif (preg_match('/^XI-/', $nama)) {
                $availableTargetKelas = $kelasList->filter(function($k) {
                    return preg_match('/^XII-/', $k->nama_kelas);
                })->values();
            }
        }

        // ═══════════════════════════════════════════════════════
        // ── READINESS CHECK: determine if kenaikan kelas is allowed
        // ═══════════════════════════════════════════════════════
        $isLocked = false;
        $lockReasons = [];
        $nilaiPercentage = 0;
        $absensiPercentage = 0;

        // 1) Must be Semester Genap (2)
        if ($activeSemester !== '2') {
            $isLocked = true;
            $lockReasons[] = 'Semester saat ini bukan Semester Genap. Kenaikan kelas hanya dapat dilakukan di akhir Semester Genap.';
        }

        // 2) Check nilai (grades) progress — all assessment types filled for all students
        $assessmentTypes = ['tugas1', 'uh1', 'tugas2', 'uh2', 'uts', 'uas'];
        $activeTahunNameForCheck = $activeTahun ? $activeTahun->nama : '2025/2026';

        // Get all mapel assignments from jadwal for this tahun ajaran
        $allAssignments = DB::table('jadwal')
            ->where('jadwal.tahun_ajaran', $activeTahunNameForCheck)
            ->join('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
            ->join('mata_pelajaran', 'jadwal.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->select('jadwal.mata_pelajaran_id', 'jadwal.kelas_id', 'mata_pelajaran.nama_mapel')
            ->distinct()
            ->get();

        $totalNilaiExpected = 0;
        $totalNilaiCompleted = 0;

        foreach ($allAssignments as $assignment) {
            $siswaInKelas = DB::table('siswa')
                ->where('kelas_id', $assignment->kelas_id)
                ->where('tahun_ajaran_id', $activeTahunId)
                ->select('id', 'agama')
                ->get();

            $matchedSiswaIds = [];
            foreach ($siswaInKelas as $siswa) {
                if (\App\Models\PeriodeNilai::matchesReligion($siswa->agama, $assignment->nama_mapel)) {
                    $matchedSiswaIds[] = $siswa->id;
                }
            }

            $siswaCount = count($matchedSiswaIds);
            if ($siswaCount === 0) continue;

            $totalNilaiExpected += $siswaCount * count($assessmentTypes);

            if (!empty($matchedSiswaIds)) {
                $siswaIdList = implode(',', $matchedSiswaIds);
                $typeList = implode("','", $assessmentTypes);

                $completed = (int) DB::selectOne("
                    SELECT COUNT(*) as cnt FROM (
                        SELECT DISTINCT siswa_id, assessment_type 
                        FROM penilaian 
                        WHERE mata_pelajaran_id = ? 
                        AND tahun_ajaran_id = ?
                        AND semester = ?
                        AND siswa_id IN ({$siswaIdList})
                        AND assessment_type IN ('{$typeList}')
                        AND nilai IS NOT NULL
                    ) as sub
                ", [$assignment->mata_pelajaran_id, $activeTahunId, $activeSemester])->cnt;

                $totalNilaiCompleted += $completed;
            }
        }

        $nilaiPercentage = $totalNilaiExpected > 0
            ? round(($totalNilaiCompleted / $totalNilaiExpected) * 100, 1)
            : 0;

        if ($nilaiPercentage < 100) {
            $isLocked = true;
            $lockReasons[] = "Progress Nilai belum lengkap ({$nilaiPercentage}%). Semua nilai siswa (Tugas, UH, UTS, Rapor Akhir) harus terisi 100%.";
        }

        // 3) Check absensi (attendance) progress — all school days filled for the entire active academic year (both Semester 1 & 2)
        $dateRanges = [];
        $schoolDays = 0;

        $semesters = ['1', '2'];
        $types = ['uh1', 'uts', 'uh2', 'uas'];

        // Determine if there are any custom period dates configured in the database
        $hasCustomPeriods = false;
        foreach ($semesters as $s) {
            $periodeRecord = DB::table('periode_nilai')
                ->where('tahun_ajaran_id', $activeTahunId)
                ->where('semester', $s)
                ->first();
            if ($periodeRecord) {
                foreach ($types as $t) {
                    if (!empty($periodeRecord->{$t . '_start'}) && !empty($periodeRecord->{$t . '_end'})) {
                        $hasCustomPeriods = true;
                        break 2;
                    }
                }
            }
        }

        foreach ($semesters as $s) {
            $periodeRecord = DB::table('periode_nilai')
                ->where('tahun_ajaran_id', $activeTahunId)
                ->where('semester', $s)
                ->first();

            foreach ($types as $t) {
                if ($hasCustomPeriods) {
                    // Only include periods that are explicitly configured in the database
                    if ($periodeRecord && !empty($periodeRecord->{$t . '_start'}) && !empty($periodeRecord->{$t . '_end'})) {
                        $range = [$periodeRecord->{$t . '_start'}, $periodeRecord->{$t . '_end'}];
                        $dateRanges[] = $range;
                        $schoolDays += \App\Models\PeriodeNilai::calculateSchoolDays($range[0], $range[1]);
                    }
                } else {
                    // Fall back to default ranges if no custom periods are set at all
                    $range = \App\Models\PeriodeNilai::getDateRangeForType($activeTahunId, $s, $t);
                    if ($range) {
                        $dateRanges[] = $range;
                        $schoolDays += \App\Models\PeriodeNilai::calculateSchoolDays($range[0], $range[1]);
                    }
                }
            }
        }

        // Get all siswa in the active tahun ajaran who are in a class
        $allSiswaIds = DB::table('siswa')
            ->where('tahun_ajaran_id', $activeTahunId)
            ->whereNotNull('kelas_id')
            ->pluck('id')
            ->toArray();

        $siswaCount = count($allSiswaIds);
        $totalAbsensiExpected = $siswaCount * $schoolDays;
        $totalAbsensiCompleted = 0;

        if ($siswaCount > 0 && $schoolDays > 0 && !empty($dateRanges)) {
            $totalAbsensiCompleted = DB::table('kehadiran')
                ->whereIn('siswa_id', $allSiswaIds)
                ->where(function($q) use ($dateRanges) {
                    foreach ($dateRanges as $range) {
                        $q->orWhereBetween('tanggal', [$range[0], $range[1]]);
                    }
                })
                ->count();
        }

        $absensiPercentage = $totalAbsensiExpected > 0
            ? round(($totalAbsensiCompleted / $totalAbsensiExpected) * 100, 1)
            : 0;

        if ($absensiPercentage < 100) {
            $isLocked = true;
            $lockReasons[] = "Progress Absensi belum lengkap ({$absensiPercentage}%). Semua data kehadiran siswa harus terisi 100%.";
        }

        // Kenaikan kelas is locked if any of the above checks fail

        return view('coordinator.kenaikan-kelas', compact(
            'kelasList', 'activeTahunId', 'activeTahunName', 'tahunList',
            'upgradeTargetMap', 'selectedKelasId', 'selectedKelas', 'siswaList', 'targetKelasName',
            'isLocked', 'lockReasons', 'nilaiPercentage', 'absensiPercentage', 'activeSemester',
            'availableTargetKelas'
        ));
    }

    public function kenaikanKelasAction(Request $request)
    {
        // Server-side guard: only allow during Semester Genap
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;
        $activeSemester = \App\Models\PeriodeNilai::detectActiveSemester($activeTahunId);
        if (!$activeTahun) {
            return back()->with('error', 'Tahun ajaran aktif tidak ditemukan.');
        }
        if ($activeSemester !== '2') {
            return back()->with('error', 'Kenaikan kelas hanya dapat dilakukan di Semester Genap.');
        }

        $statuses = $request->input('statuses', []);
        $targetTahunId = $request->input('target_tahun_id');
        $sourceKelasId = $request->input('source_kelas_id');

        if (!$targetTahunId || !$sourceKelasId || empty($statuses)) {
            return back()->with('error', 'Data tidak lengkap. Pastikan tahun ajaran tujuan, kelas, dan status siswa sudah dipilih.');
        }

        if ($targetTahunId == $activeTahunId) {
            return back()->with('error', 'Tahun ajaran tujuan tidak boleh sama dengan tahun ajaran aktif.');
        }

        // Get all kelas for upgrade mapping
        $kelasList = DB::table('kelas')->orderBy('nama_kelas')->get();
        $sourceKelas = $kelasList->firstWhere('id', (int) $sourceKelasId);

        if (!$sourceKelas) {
            return back()->with('error', 'Kelas asal tidak ditemukan.');
        }

        // Determine target kelas for 'naik' students
        $nama = $sourceKelas->nama_kelas;
        $targetKelasId = null;
        if (preg_match('/^X-(\d+)$/', $nama, $m)) {
            $target = 'XI-' . $m[1];
            $tk = $kelasList->firstWhere('nama_kelas', $target);
            $targetKelasId = $tk ? $tk->id : null;
        } elseif (preg_match('/^XI-(\d+)$/', $nama, $m)) {
            $target = 'XII-' . $m[1];
            $tk = $kelasList->firstWhere('nama_kelas', $target);
            $targetKelasId = $tk ? $tk->id : null;
        }
        // XII students can't be "naik", they can only be "lulus" or "tidak_aktif"

        $totalNaik = 0;
        $totalTinggal = 0;
        $totalLulus = 0;
        $totalTidakAktif = 0;
        $skipped = 0;

        $targetKelasIds = $request->input('target_kelas_ids', []);

        foreach ($statuses as $siswaId => $status) {
            $siswa = DB::table('siswa')
                ->where('id', $siswaId)
                ->where('kelas_id', $sourceKelasId)
                ->where('tahun_ajaran_id', $activeTahunId)
                ->first();

            if (!$siswa) continue;

            $individualTargetKelasId = $targetKelasIds[$siswaId] ?? $targetKelasId;

            if ($status === 'naik' && $individualTargetKelasId) {
                // Check duplicate
                $exists = DB::table('siswa')
                    ->where('nis', $siswa->nis)
                    ->where('tahun_ajaran_id', $targetTahunId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Copy student to new tahun ajaran with upgraded kelas
                DB::table('siswa')->insert([
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'kelas_id' => $individualTargetKelasId,
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'agama' => $siswa->agama,
                    'no_hp' => $siswa->no_hp,
                    'no_hp_orangtua' => $siswa->no_hp_orangtua,
                    'tempat_lahir' => $siswa->tempat_lahir,
                    'tanggal_lahir' => $siswa->tanggal_lahir,
                    'tahun_ajaran_id' => $targetTahunId,
                ]);
                $totalNaik++;
            } elseif ($status === 'tinggal') {
                // Check duplicate
                $exists = DB::table('siswa')
                    ->where('nis', $siswa->nis)
                    ->where('tahun_ajaran_id', $targetTahunId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Copy student to new tahun ajaran with same kelas
                DB::table('siswa')->insert([
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'kelas_id' => $sourceKelasId, // Repeat same class
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'agama' => $siswa->agama,
                    'no_hp' => $siswa->no_hp,
                    'no_hp_orangtua' => $siswa->no_hp_orangtua,
                    'tempat_lahir' => $siswa->tempat_lahir,
                    'tanggal_lahir' => $siswa->tanggal_lahir,
                    'tahun_ajaran_id' => $targetTahunId,
                ]);
                $totalTinggal++;
            } elseif ($status === 'lulus') {
                $totalLulus++;
            } elseif ($status === 'tidak_aktif') {
                $totalTidakAktif++;
            }
        }

        $msg = "Proses kenaikan kelas berhasil!";
        if ($totalNaik > 0) {
            $msg .= " {$totalNaik} siswa naik kelas.";
        }
        if ($totalTinggal > 0) {
            $msg .= " {$totalTinggal} siswa tinggal kelas.";
        }
        if ($totalLulus > 0) {
            $msg .= " {$totalLulus} siswa dinyatakan LULUS.";
        }
        if ($totalTidakAktif > 0) {
            $msg .= " {$totalTidakAktif} siswa dinyatakan TIDAK AKTIF.";
        }
        if ($skipped > 0) {
            $msg .= " ({$skipped} siswa dilewati karena sudah ada di tahun ajaran tujuan.)";
        }

        return redirect()->route('coordinator.kenaikan-kelas', ['kelas_id' => $sourceKelasId])
            ->with('success', $msg);
    }

    // ── Manage Absensi (read-only, data from walikelas) ───────
    public function manageAbsensi(Request $request)
    {
        $activeTahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $activeTahunId = $activeTahun ? $activeTahun->id : 1;

        // Get all kelas
        $kelasList = DB::table('kelas')->orderBy('nama_kelas')->get();

        // Selected kelas
        $selectedKelasId = $request->get('kelas_id', null);
        $selectedKelas = $selectedKelasId ? $kelasList->firstWhere('id', $selectedKelasId) : null;

        $siswaList = collect();
        $kehadiranMap = [];
        $daysInMonth = 0;
        $selectedMonth = $request->get('month', date('Y-m'));
        $monthName = '';

        if ($selectedKelas) {
            $year  = (int)substr($selectedMonth, 0, 4);
            $month = (int)substr($selectedMonth, 5, 2);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $siswaList = DB::table('siswa')
                ->where('kelas_id', $selectedKelasId)
                ->where('tahun_ajaran_id', $activeTahunId)
                ->orderBy('nama')
                ->get();

            if ($siswaList->isNotEmpty()) {
                $startDate = "$selectedMonth-01";
                $endDate   = "$selectedMonth-" . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT);
                $kRows = DB::table('kehadiran')
                    ->whereIn('siswa_id', $siswaList->pluck('id'))
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->selectRaw('siswa_id, DAY(tanggal) as day_num, status')
                    ->get();
                $statusMap = ['Hadir' => 'H', 'Sakit' => 'S', 'Izin' => 'I', 'Alpha' => 'A'];
                foreach ($kRows as $r) {
                    $kehadiranMap[$r->siswa_id][$r->day_num] = $statusMap[$r->status] ?? '';
                }
            }

            $monthsIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
            $monthName = $monthsIndo[$month] . ' ' . $year;
        }

        return view('coordinator.manage-absensi', compact(
            'kelasList', 'selectedKelasId', 'selectedKelas',
            'siswaList', 'kehadiranMap', 'daysInMonth', 'selectedMonth', 'monthName'
        ));
    }

    public function manageAbsensiSave(Request $request)
    {
        $data = $request->json()->all()['data'] ?? [];
        $kelasId = $request->json()->all()['kelas_id'] ?? null;

        foreach ($data as $row) {
            $siswaId = trim($row['siswa_id'] ?? '');
            $tanggal = $row['tanggal'] ?? '';
            $statusLetter = $row['status'] ?? '';
            $statusMap = ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpha'];
            $status = $statusMap[$statusLetter] ?? null;
            if (!$siswaId || !$tanggal || !$status) continue;

            DB::table('kehadiran')->upsert([
                'siswa_id' => $siswaId,
                'kelas_id' => $kelasId,
                'tanggal'  => $tanggal,
                'status'   => $status,
            ], ['siswa_id', 'tanggal'], ['status', 'kelas_id']);
        }
        return response()->json(['success' => true]);
    }
}
