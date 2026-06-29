@extends('layouts.app')
@section('page_title', 'Rekap Nilai')
@section('styles')
<style>
    select option { background:#fff !important; color:#1a2a3a !important; }
    .content-wrap { padding:2rem; width:100%; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; }
    .page-title { font-size:1.5rem; font-weight:bold; color:#0C447C; display:flex; align-items:center; gap:10px; }
    .controls { display:flex; gap:15px; align-items:center; background:#fff; padding:1rem 1.5rem; border-radius:8px; border:1px solid #dce8f5; margin-bottom:2rem; flex-wrap:wrap; }
    .controls label { font-weight:bold; color:#378ADD; font-size:.95rem; }
    .controls select { background:#E6F1FB; color:#0C447C; border:1px solid #dce8f5; padding:8px 12px; border-radius:4px; font-size:1rem; outline:none; cursor:pointer; }
    .semester-select { background:#E6F1FB; color:#0C447C; border:1px solid #dce8f5; padding:8px 12px; border-radius:4px; font-size:1rem; outline:none; cursor:pointer; font-weight:600; }
    .search-container { display:flex; margin-bottom:20px; background:#E6F1FB; border-radius:4px; overflow:hidden; border:1px solid #dce8f5; }
    .search-input { flex:1; background:#E6F1FB; border:none; padding:12px 15px; color:#378ADD; font-size:.9rem; outline:none; }
    .search-input::placeholder { color:#6a9bc0; }
    .search-btn { background:#378ADD; border:none; width:45px; display:flex; align-items:center; justify-content:center; cursor:pointer; }
    .search-btn:hover { background:#0056b3; }
    .search-icon { width:16px; height:16px; fill:#fff; }
    .table-container { background:#fff; border-radius:8px; overflow-x:auto; border:1px solid #dce8f5; }
    .data-table { width:100%; border-collapse:collapse; font-size:.9rem; }
    .data-table th, .data-table td { text-align:center; padding:12px 15px; border-bottom:1px solid #dce8f5; border-right:1px solid #dce8f5; color:#1a2a3a; }
    .data-table th:first-child, .data-table td:first-child { text-align:left; position:sticky; left:0; background:#0C447C; color:#fff !important; z-index:10; font-weight:600; }
    .data-table thead th:first-child { z-index:11; }
    .data-table th { font-weight:600; color:#fff; background:#0C447C; text-transform:uppercase; letter-spacing:.05em; }
    .data-table tbody tr:hover td { background:#e0edf8; color:#1a2a3a; }
    .data-table tbody tr:hover td:first-child { background:#0C447C; color:#fff !important; }
    .empty-dash { color:#378ADD; }
    .score-cell { color:#1a3a5a; font-size:.9rem; }
    .score-cell.empty { color:#378ADD; }
    .ekskul-cell { color:#1a3a5a; font-size:.85rem; }
    .ekskul-cell.empty { color:#378ADD; }

    /* ── Tabs ── */
    .tab-container {
        display: flex; gap: 0; margin-bottom: 0;
        border-bottom: 2px solid #dce8f5;
        margin-top: 1rem;
    }
    .tab-btn {
        background: none; border: none; padding: 14px 28px;
        font-size: 1rem; font-weight: 600; color: #6a9bc0;
        cursor: pointer; position: relative;
        transition: color .2s, background .2s;
        border-radius: 8px 8px 0 0;
    }
    .tab-btn:hover { background: #f0f7ff; color: #0C447C; }
    .tab-btn.active {
        color: #0C447C; background: #fff;
        border: 2px solid #dce8f5; border-bottom: 2px solid #fff;
        margin-bottom: -2px;
    }
    .tab-panel { display: none; background: #fff; border: 2px solid #dce8f5; border-top: none; border-radius: 0 0 12px 12px; padding: 24px; }
    .tab-panel.active { display: block; }
    .tab-panel .table-container { border: none; background: transparent; box-shadow: none; }

    /* ── Inline Controls for Ekskul ── */
    .ekskul-select-inline, .nilai-select-inline {
        background: #f4f8fc;
        color: #0C447C;
        border: 1px solid #cce0f5;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: .88rem;
        outline: none;
        cursor: pointer;
        width: 100%;
        max-width: 200px;
        font-weight: 500;
        transition: border-color .2s;
    }
    .ekskul-select-inline:focus, .nilai-select-inline:focus {
        border-color: #378ADD;
        background: #fff;
    }
    .keterangan-input-inline {
        background: #f4f8fc;
        color: #1a2a3a;
        border: 1px solid #cce0f5;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: .88rem;
        outline: none;
        width: 100%;
        font-weight: 500;
        transition: border-color .2s;
    }
    .keterangan-input-inline:focus {
        border-color: #378ADD;
        background: #fff;
    }
    .lab-input-inline {
        background: #f4f8fc;
        color: #0C447C;
        border: 1px solid #cce0f5;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: .88rem;
        outline: none;
        width: 100px;
        text-align: center;
        font-weight: 500;
        transition: border-color .2s;
    }
    .lab-input-inline:focus {
        border-color: #378ADD;
        background: #fff;
    }
    .ekskul-select-inline:disabled, .nilai-select-inline:disabled, .keterangan-input-inline:disabled, .lab-input-inline:disabled {
        background: #f1f1f1;
        color: #888;
        border-color: #ddd;
        cursor: not-allowed;
    }

    /* Toast alert for autosave status */
    .toast-alert {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 24px;
        background: #28a745;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        font-size: .9rem;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: none;
        animation: slideIn 0.3s ease-out;
    }
    .toast-alert.error {
        background: #dc3545;
    }
    @keyframes slideIn {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* ── CRUD Buttons ── */
    .crud-btn {
        border: none; border-radius: 6px; padding: 8px 16px;
        font-weight: 600; font-size: .88rem; cursor: pointer;
        transition: all .2s; display: inline-flex; align-items: center; gap: 5px;
    }
    .crud-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,.15); }
    .crud-btn-create { background: linear-gradient(135deg, #0C447C, #1a5a9a); color: #fff; }
    .crud-btn-save { background: #28a745; color: #fff; }
    .crud-btn-cancel { background: #6c757d; color: #fff; padding: 8px 12px; }
    .crud-btn-delete { background: none; border: none; color: #dc3545; font-size: 1.1rem; padding: 4px 8px; cursor: pointer; }
    .crud-btn-delete:hover { background: #fee; border-radius: 4px; }
</style>
@endsection

@php
    $typeLabels = [
        'tugas1' => 'Tugas 1',
        'uh1' => 'Ulangan Harian 1 (UH1)',
        'tugas2' => 'Tugas 2',
        'uh2' => 'Ulangan Harian 2 (UH2)',
        'uts' => 'Ujian Tengah Semester (UTS)',
        'uas' => 'Ujian Akhir Semester (UAS)',
    ];
    $labSubjects = ['FISIKA', 'KIMIA', 'BIOLOGI'];
    $totalCols = 1 + count($subjects); // Nama Siswa + subjects
@endphp

@section('content')
<div class="content-wrap">
    <div class="page-header">
        <div class="page-title">Rekapitulasi Nilai Kelas {{ $myKelas }}</div>
    </div>

    <div class="controls" id="filterControls">
        <div id="tipeNilaiContainer" style="display: inline-flex; align-items: center; gap: 15px;">
            <label for="jenisNilai">Pilih Tipe Nilai:</label>
            <form action="" method="GET" id="filterForm">
                <input type="hidden" name="semester" value="{{ $semesterNum }}">
                <select name="jenis" id="jenisNilai" onchange="document.getElementById('filterForm').submit()">
                    @foreach($allowedTypes as $type)
                    <option value="{{ $type }}" {{ $jenis===$type?'selected':'' }}>{{ $typeLabels[$type] ?? strtoupper($type) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <label for="semesterSelect">Semester:</label>
        <form action="" method="GET" id="semesterForm">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <select name="semester" id="semesterSelect" class="semester-select" onchange="document.getElementById('semesterForm').submit()">
                <option value="1" {{ $semesterNum === '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                <option value="2" {{ $semesterNum === '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
            </select>
        </form>
    </div>

    <div class="search-container">
        <input type="text" id="searchInput" class="search-input" placeholder="Search by Nama Siswa...">
        <button class="search-btn"><svg class="search-icon" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg></button>
    </div>

    <!-- Tab Buttons -->
    <div class="tab-container">
        <button class="tab-btn active" id="tabBtnMapel" onclick="switchTab('mapel')">📚 Rekap Nilai Mapel</button>
        <button class="tab-btn" id="tabBtnEkskul" onclick="switchTab('ekskul')">🏆 Nilai Ekstrakurikuler</button>
        <button class="tab-btn" id="tabBtnLab" onclick="switchTab('lab')">🔬 Nilai Laboratorium</button>
        <button class="tab-btn" id="tabBtnKepribadian" onclick="switchTab('kepribadian')">😊 Kepribadian</button>
    </div>

    <!-- Tab 1: Mata Pelajaran -->
    <div id="panelMapel" class="tab-panel active">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:200px;">Nama Siswa</th>
                        @foreach($subjects as $subj)
                        <th>{{ $subj }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaList as $s)
                    <tr>
                        <td>{{ $s->nama }}</td>
                        @foreach($subjects as $subj)
                        @php
                            $score = $gradesRaw[$s->id][$subj] ?? null;
                            $val = ($score !== null) ? (floor((float)$score) == (float)$score ? intval($score) : number_format((float)$score,1)) : '';
                        @endphp
                        <td>
                            <span class="score-cell {{ $val === '' ? 'empty' : '' }}">{{ $val !== '' ? $val : '-' }}</span>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr><td colspan="{{ count($subjects) + 1 }}" style="padding:2rem;">Tidak ada siswa di kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Ekstrakurikuler (Record-based CRUD) -->
    <div id="panelEkskul" class="tab-panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <span style="font-weight:700; color:#0C447C; font-size:1.05rem;">Data Nilai Ekstrakurikuler</span>
            <button type="button" class="crud-btn crud-btn-create" onclick="toggleEkskulForm()">➕ Tambah Ekskul</button>
        </div>

        <!-- Inline Add Form (hidden by default) -->
        <div id="ekskulAddForm" style="display:none; background:#f0f7ff; border:2px solid #dce8f5; border-radius:8px; padding:16px; margin-bottom:16px;">
            <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                <div style="flex:1; min-width:180px;">
                    <label style="font-size:.8rem; font-weight:700; color:#0C447C; display:block; margin-bottom:4px;">Siswa</label>
                    <select id="ekskulAddSiswa" class="ekskul-select-inline" style="max-width:100%;">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:160px;">
                    <label style="font-size:.8rem; font-weight:700; color:#0C447C; display:block; margin-bottom:4px;">Ekstrakurikuler</label>
                    <select id="ekskulAddMapel" class="ekskul-select-inline" style="max-width:100%;">
                        <option value="">-- Pilih Ekskul --</option>
                        @foreach($ekskulOptions as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:100px;">
                    <label style="font-size:.8rem; font-weight:700; color:#0C447C; display:block; margin-bottom:4px;">Predikat</label>
                    <select id="ekskulAddNilai" class="nilai-select-inline" style="max-width:100%;">
                        <option value="">-- Pilih --</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-size:.8rem; font-weight:700; color:#0C447C; display:block; margin-bottom:4px;">Keterangan</label>
                    <input type="text" id="ekskulAddKet" class="keterangan-input-inline" placeholder="Keterangan..." style="width:100%;">
                </div>
                <div style="display:flex; gap:6px;">
                    <button type="button" class="crud-btn crud-btn-save" onclick="saveNewEkskul()">💾 Simpan</button>
                    <button type="button" class="crud-btn crud-btn-cancel" onclick="toggleEkskulForm()">✕</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="ekskulTable">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th style="min-width:200px; text-align:left;">Nama Siswa</th>
                        <th style="width:200px;">Ekstrakurikuler</th>
                        <th style="width:120px;">Predikat</th>
                        <th style="min-width:250px; text-align:left;">Keterangan</th>
                        <th style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ekskulRecords as $idx => $rec)
                    <tr data-record-id="{{ $rec->record_id }}">
                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align:left; font-weight:600; color:#0C447C;">{{ $rec->siswa_nama }}</td>
                        <td>
                            <select class="ekskul-select-inline ekskul-edit-field" data-record-id="{{ $rec->record_id }}" data-field="ekskul" style="max-width:100%;">
                                @foreach($ekskulOptions as $opt)
                                <option value="{{ $opt }}" {{ $rec->ekskul_name === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="nilai-select-inline ekskul-edit-field" data-record-id="{{ $rec->record_id }}" data-field="nilai_ekskul" style="max-width:100%;">
                                <option value="">--</option>
                                <option value="A" {{ $rec->nilai_deskriptif === 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ $rec->nilai_deskriptif === 'B' ? 'selected' : '' }}>B</option>
                                <option value="C" {{ $rec->nilai_deskriptif === 'C' ? 'selected' : '' }}>C</option>
                            </select>
                        </td>
                        <td style="text-align:left;">
                            <input type="text" class="keterangan-input-inline ekskul-edit-field" data-record-id="{{ $rec->record_id }}" data-field="ekskul_keterangan" value="{{ $rec->ekskul_keterangan ?? '' }}" placeholder="Keterangan...">
                        </td>
                        <td>
                            <button type="button" class="crud-btn crud-btn-delete" onclick="deleteEkskul({{ $rec->record_id }}, this)" title="Hapus">🗑️</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Nilai Laboratorium (Read-Only for Wali Kelas) -->
    <div id="panelLab" class="tab-panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <span style="font-weight:700; color:#0C447C; font-size:1.05rem;">Data Nilai Laboratorium</span>
        </div>

        <div class="table-container">
            <table class="data-table" id="labTable">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th style="min-width:200px; text-align:left;">Nama Siswa</th>
                        <th style="width:150px;">Mata Pelajaran</th>
                        <th style="width:120px; text-align:center;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @php $labIdx = 0; @endphp
                    @foreach($labRecordsBySiswa as $siswaId => $records)
                        @foreach($records as $rec)
                        <tr data-record-id="{{ $rec->record_id }}">
                            <td>{{ ++$labIdx }}</td>
                            <td style="text-align:left; font-weight:600; color:#0C447C;">{{ $rec->siswa_nama }}</td>
                            <td>
                                @if($rec->nama_mapel === 'FISIKA')
                                    Lab Fisika
                                @elseif($rec->nama_mapel === 'KIMIA')
                                    Lab Kimia
                                @elseif($rec->nama_mapel === 'BIOLOGI')
                                    Lab Biologi
                                @else
                                    {{ $rec->nama_mapel }}
                                @endif
                            </td>
                            <td style="text-align:center; font-weight:bold; color:#0C447C;">
                                {{ $rec->nilai !== null ? intval($rec->nilai) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 4: Kepribadian -->
    <div id="panelKepribadian" class="tab-panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <span style="font-weight:700; color:#0C447C; font-size:1.05rem;">Data Kepribadian Siswa</span>
        </div>

        <div class="table-container">
            <table class="data-table" id="kepribadianTable">
                <thead>
                    <tr>
                        <th style="width:50px;" rowspan="2">No</th>
                        <th style="min-width:200px; text-align:left;" rowspan="2">Nama Siswa</th>
                        @foreach($kepribadianPeriods as $pKey => $pLabel)
                        <th colspan="4">{{ $pLabel }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($kepribadianPeriods as $pKey => $pLabel)
                            @foreach($kepribadianAspects as $aspect)
                            <th style="font-size: 0.75rem; min-width: 80px;">{{ $aspect }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaList as $idx => $s)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align:left; font-weight:600; color:#0C447C;">{{ $s->nama }}</td>
                        @foreach($kepribadianPeriods as $pKey => $pLabel)
                            @foreach($kepribadianAspects as $aspect)
                            @php
                                $val = $kepribadianRecords[$s->id][$pKey][$aspect] ?? '';
                            @endphp
                            <td>
                                <select class="nilai-select-inline kepribadian-edit-field" 
                                        data-siswa-id="{{ $s->id }}" 
                                        data-aspect="{{ $aspect }}" 
                                        data-periode="{{ $pKey }}"
                                        style="max-width:100%; font-size: 0.8rem; padding: 4px;"
                                        {{ !$isOpen ? 'disabled' : '' }}>
                                    <option value="">--</option>
                                    <option value="A" {{ $val === 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ $val === 'B' ? 'selected' : '' }}>B</option>
                                    <option value="C" {{ $val === 'C' ? 'selected' : '' }}>C</option>
                                </select>
                            </td>
                            @endforeach
                        @endforeach
                    </tr>
                    @empty
                    <tr><td colspan="{{ 2 + count($kepribadianPeriods) * count($kepribadianAspects) }}" style="padding:2rem;">Tidak ada siswa di kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="toast-alert" id="toastAlert"></div>
@endsection

@section('scripts')
<script>
    // Tab switching
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));

        const tipeNilaiContainer = document.getElementById('tipeNilaiContainer');

        if (tabId === 'mapel') {
            document.getElementById('tabBtnMapel').classList.add('active');
            document.getElementById('panelMapel').classList.add('active');
            tipeNilaiContainer.style.display = 'inline-flex';
        } else if (tabId === 'ekskul') {
            document.getElementById('tabBtnEkskul').classList.add('active');
            document.getElementById('panelEkskul').classList.add('active');
            tipeNilaiContainer.style.display = 'none';
        } else if (tabId === 'lab') {
            document.getElementById('tabBtnLab').classList.add('active');
            document.getElementById('panelLab').classList.add('active');
            tipeNilaiContainer.style.display = 'none';
        } else if (tabId === 'kepribadian') {
            document.getElementById('tabBtnKepribadian').classList.add('active');
            document.getElementById('panelKepribadian').classList.add('active');
            tipeNilaiContainer.style.display = 'none';
        }
        localStorage.setItem('walikelas_nilai_active_tab', tabId);
    }

    // Toast notification
    const toast = document.getElementById('toastAlert');
    function showToast(message, isError = false) {
        toast.textContent = message;
        toast.className = 'toast-alert' + (isError ? ' error' : '');
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3000);
    }

    // ── Ekskul CRUD ──────────────────────────────────────────
    function toggleEkskulForm() {
        const form = document.getElementById('ekskulAddForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    async function saveNewEkskul() {
        const siswaId = document.getElementById('ekskulAddSiswa').value;
        const ekskul = document.getElementById('ekskulAddMapel').value;
        const nilai = document.getElementById('ekskulAddNilai').value;
        const ket = document.getElementById('ekskulAddKet').value;

        if (!siswaId || !ekskul) {
            showToast('Pilih siswa dan ekstrakurikuler terlebih dahulu.', true);
            return;
        }

        try {
            const response = await fetch('{{ route("walikelas.api.ekskul") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ siswa_id: siswaId, field: 'ekskul', value: ekskul })
            });
            const result = await response.json();
            if (!result.success) {
                showToast(result.error || 'Gagal menyimpan ekskul.', true);
                return;
            }

            // Save nilai and keterangan if provided
            if (nilai) {
                await fetch('{{ route("walikelas.api.ekskul") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ siswa_id: siswaId, field: 'nilai_ekskul', value: nilai })
                });
            }
            if (ket) {
                await fetch('{{ route("walikelas.api.ekskul") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ siswa_id: siswaId, field: 'ekskul_keterangan', value: ket })
                });
            }

            showToast('Ekstrakurikuler berhasil ditambahkan.');
            setTimeout(() => location.reload(), 500);
        } catch (error) {
            showToast('Gagal terhubung ke server.', true);
        }
    }

    async function deleteEkskul(recordId, btn) {
        if (!confirm('Hapus data ekstrakurikuler ini?')) return;

        try {
            const response = await fetch('{{ route("walikelas.api.ekskul.delete") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ record_id: recordId })
            });
            const result = await response.json();
            if (result.success) {
                btn.closest('tr').remove();
                showToast('Data ekstrakurikuler berhasil dihapus.');
                renumberTable('ekskulTable');
            } else {
                showToast(result.error || 'Gagal menghapus.', true);
            }
        } catch (error) {
            showToast('Gagal terhubung ke server.', true);
        }
    }

    // ── Lab CRUD ─────────────────────────────────────────────
    function toggleLabForm() {
        const form = document.getElementById('labAddForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    async function saveNewLab() {
        const siswaId = document.getElementById('labAddSiswa').value;
        const mapel = document.getElementById('labAddMapel').value;
        const nilai = document.getElementById('labAddNilai').value;

        if (!siswaId || !mapel) {
            showToast('Pilih siswa dan mata pelajaran lab terlebih dahulu.', true);
            return;
        }
        if (nilai === '' || isNaN(parseInt(nilai)) || parseInt(nilai) < 0 || parseInt(nilai) > 100) {
            showToast('Nilai harus berupa angka 0 - 100.', true);
            return;
        }

        try {
            const response = await fetch('{{ route("walikelas.api.nilai") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ siswa_id: siswaId, mata_pelajaran: mapel, jenis: 'uas_lab', value: parseInt(nilai) })
            });
            const result = await response.json();
            if (result.success) {
                showToast('Nilai laboratorium berhasil ditambahkan.');
                setTimeout(() => location.reload(), 500);
            } else {
                showToast(result.error || 'Gagal menyimpan nilai lab.', true);
            }
        } catch (error) {
            showToast('Gagal terhubung ke server.', true);
        }
    }

    async function deleteLab(recordId, btn) {
        if (!confirm('Hapus data nilai laboratorium ini?')) return;

        try {
            const response = await fetch('{{ route("walikelas.api.lab.delete") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ record_id: recordId })
            });
            const result = await response.json();
            if (result.success) {
                btn.closest('tr').remove();
                showToast('Nilai laboratorium berhasil dihapus.');
                renumberTable('labTable');
            } else {
                showToast(result.error || 'Gagal menghapus.', true);
            }
        } catch (error) {
            showToast('Gagal terhubung ke server.', true);
        }
    }

    // Renumber table rows after deletion
    function renumberTable(tableId) {
        const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        let num = 1;
        rows.forEach(row => {
            if (row.id && (row.id === 'ekskulEmptyRow' || row.id === 'labEmptyRow')) return;
            if (row.cells.length > 1) {
                row.cells[0].textContent = num++;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const activeTab = localStorage.getItem('walikelas_nilai_active_tab') || 'mapel';
        switchTab(activeTab);

        // ── Inline Edit for Ekskul (auto-save on change) ──
        document.querySelectorAll('.ekskul-edit-field').forEach(el => {
            el.addEventListener('change', async function() {
                const recordId = this.getAttribute('data-record-id');
                const field = this.getAttribute('data-field');
                const value = this.value;

                this.style.borderColor = '#EF9F27';
                try {
                    const response = await fetch('{{ route("walikelas.api.ekskul") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ record_id: recordId, field: field, value: value })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.style.borderColor = '#28a745';
                        showToast('Perubahan berhasil disimpan.');
                        setTimeout(() => { this.style.borderColor = ''; }, 1000);
                    } else {
                        this.style.borderColor = '#dc3545';
                        showToast(result.error || 'Gagal menyimpan.', true);
                    }
                } catch (error) {
                    this.style.borderColor = '#dc3545';
                    showToast('Gagal terhubung ke server.', true);
                }
            });
        });

        // ── Inline Edit for Lab (auto-save on change) ──
        document.querySelectorAll('.lab-edit-field').forEach(input => {
            input.addEventListener('change', async function() {
                const siswaId = this.getAttribute('data-siswa-id');
                const mapel = this.getAttribute('data-mapel');
                const val = this.value.trim();

                if (val !== '' && (isNaN(parseInt(val)) || parseInt(val) < 0 || parseInt(val) > 100)) {
                    this.style.borderColor = '#dc3545';
                    showToast('Nilai harus berupa angka 0 - 100.', true);
                    return;
                }

                this.style.borderColor = '#EF9F27';
                try {
                    const response = await fetch('{{ route("walikelas.api.nilai") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ siswa_id: siswaId, mata_pelajaran: mapel, jenis: 'uas_lab', value: val !== '' ? parseInt(val) : '' })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.style.borderColor = '#28a745';
                        showToast('Nilai Lab berhasil diperbarui.');
                        setTimeout(() => { this.style.borderColor = ''; }, 1000);
                    } else {
                        this.style.borderColor = '#dc3545';
                        showToast(result.error || 'Gagal menyimpan.', true);
                    }
                } catch (error) {
                    this.style.borderColor = '#dc3545';
                    showToast('Gagal terhubung ke server.', true);
                }
            });
        });

        // ── Inline Edit for Kepribadian (auto-save on change) ──
        document.querySelectorAll('.kepribadian-edit-field').forEach(el => {
            el.addEventListener('change', async function() {
                const siswaId = this.getAttribute('data-siswa-id');
                const aspect = this.getAttribute('data-aspect');
                const periode = this.getAttribute('data-periode');
                const value = this.value;

                this.style.borderColor = '#EF9F27';
                try {
                    const response = await fetch('{{ route("walikelas.api.kepribadian") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ siswa_id: siswaId, aspect: aspect, periode: periode, value: value })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.style.borderColor = '#28a745';
                        showToast('Data Kepribadian berhasil diperbarui.');
                        setTimeout(() => { this.style.borderColor = ''; }, 1000);
                    } else {
                        this.style.borderColor = '#dc3545';
                        showToast(result.error || 'Gagal menyimpan.', true);
                    }
                } catch (error) {
                    this.style.borderColor = '#dc3545';
                    showToast('Gagal terhubung ke server.', true);
                }
            });
        });

        // Search bar functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                const tdMapel = row.cells[0];
                const tdEkskul = row.cells[1];
                let nameText = '';
                if (row.closest('#panelMapel')) {
                    nameText = tdMapel ? tdMapel.textContent : '';
                } else if (row.closest('#panelEkskul') || row.closest('#panelLab') || row.closest('#panelKepribadian')) {
                    nameText = tdEkskul ? tdEkskul.textContent : '';
                }
                if (nameText) {
                    row.style.display = nameText.toLowerCase().includes(q) ? '' : 'none';
                }
            });
        });
    });
</script>
@endsection
