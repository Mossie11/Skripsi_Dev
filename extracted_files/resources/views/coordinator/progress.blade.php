@extends('layouts.app')

@section('page_title', 'Progress Monitoring')

@section('styles')
<style>
    /* ── Header ── */
    .progress-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        margin-bottom: 2rem; flex-wrap: wrap; gap: 16px;
    }
    .progress-header h2 {
        font-size: 1.8rem; font-weight: 700; color: #0C447C; margin: 0;
    }
    .progress-header .meta {
        color: #6a9bc0; font-size: .9rem; margin-top: 4px;
    }

    /* ── Tabs ── */
    .tab-container {
        display: flex; gap: 0; margin-bottom: 0;
        border-bottom: 2px solid #dce8f5;
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
    .tab-btn .badge {
        display: inline-flex; align-items: center; justify-content: center;
        background: #EF9F27; color: #fff; border-radius: 10px;
        padding: 2px 8px; font-size: .75rem; margin-left: 8px;
        font-weight: 700;
    }
    .tab-panel { display: none; background: #fff; border: 2px solid #dce8f5; border-top: none; border-radius: 0 0 12px 12px; padding: 24px; }
    .tab-panel.active { display: block; }

    /* ── Month Selector (Walikelas tab) ── */
    .month-filter {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 20px; padding: 12px 18px;
        background: #f5f9ff; border-radius: 8px; border: 1px solid #dce8f5;
    }
    .month-filter label { font-weight: 600; color: #0C447C; font-size: .9rem; }
    .month-filter input[type="month"] {
        border: 1px solid #c5d8ec; border-radius: 6px; padding: 6px 12px;
        font-size: .9rem; color: #0C447C;
    }
    .month-filter .btn-filter {
        background: #378ADD; color: #fff; border: none; padding: 7px 18px;
        border-radius: 6px; font-size: .9rem; cursor: pointer; font-weight: 600;
        transition: background .2s;
    }
    .month-filter .btn-filter:hover { background: #0C447C; }
    .school-days-info {
        font-size: .85rem; color: #6a9bc0; margin-left: auto;
    }

    /* ── Summary Cards ── */
    .summary-cards {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px; margin-bottom: 24px;
    }
    .summary-card {
        background: linear-gradient(135deg, #f8fbff 0%, #edf4fc 100%);
        border: 1px solid #dce8f5; border-radius: 10px; padding: 18px;
        text-align: center; cursor: pointer; transition: all .2s ease;
        user-select: none;
    }
    .summary-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(12,68,124,.12); }
    .summary-card.active-filter { border: 2px solid #0C447C; box-shadow: 0 4px 16px rgba(12,68,124,.18); transform: translateY(-2px); }
    .summary-card .sc-value {
        font-size: 1.8rem; font-weight: 800; color: #0C447C;
    }
    .summary-card .sc-label {
        font-size: .8rem; color: #6a9bc0; margin-top: 4px; text-transform: uppercase; letter-spacing: .5px;
    }
    .summary-card.green .sc-value { color: #1e8a5a; }
    .summary-card.green.active-filter { border-color: #1e8a5a; }
    .summary-card.yellow .sc-value { color: #c79100; }
    .summary-card.yellow.active-filter { border-color: #c79100; }
    .summary-card.red .sc-value { color: #c0392b; }
    .summary-card.red.active-filter { border-color: #c0392b; }

    /* ── Progress Table ── */
    .progress-table { width: 100%; border-collapse: collapse; margin-top: 0; }
    .progress-table thead th {
        background: #0C447C; color: #fff; padding: 12px 16px;
        text-align: left; font-size: .85rem; text-transform: uppercase;
        letter-spacing: .5px; white-space: nowrap;
    }
    .progress-table thead th:first-child { border-radius: 8px 0 0 0; }
    .progress-table thead th:last-child { border-radius: 0 8px 0 0; }
    .progress-table tbody tr { border-bottom: 1px solid #eef3fa; transition: background .15s; }
    .progress-table tbody tr:hover { background: #f5f9ff; }
    .progress-table td {
        padding: 12px 16px; font-size: .9rem; color: #333;
        vertical-align: middle;
    }

    /* ── Progress Bar ── */
    .pbar-container {
        display: flex; align-items: center; gap: 10px; min-width: 200px;
    }
    .pbar-track {
        flex: 1; height: 10px; background: #e8eef5; border-radius: 5px;
        overflow: hidden; position: relative;
    }
    .pbar-fill {
        height: 100%; border-radius: 5px; transition: width .5s ease;
        background: linear-gradient(90deg, #4ecf9a, #27ae60);
    }
    .pbar-fill.yellow { background: linear-gradient(90deg, #f6d365, #f5a623); }
    .pbar-fill.red { background: linear-gradient(90deg, #ff7675, #d63031); }
    .pbar-label {
        font-weight: 700; font-size: .85rem; min-width: 48px; text-align: right;
    }
    .pbar-label.green { color: #1e8a5a; }
    .pbar-label.yellow { color: #c79100; }
    .pbar-label.red { color: #c0392b; }

    /* ── Status Badge ── */
    .status-badge {
        display: inline-block; padding: 3px 10px; border-radius: 12px;
        font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
    }
    .status-badge.complete { background: #d4edda; color: #155724; }
    .status-badge.partial { background: #fff3cd; color: #856404; }
    .status-badge.empty { background: #f8d7da; color: #721c24; }

    /* ── Expandable Row ── */
    .expand-toggle {
        background: none; border: 1px solid #c5d8ec; border-radius: 6px;
        padding: 4px 10px; cursor: pointer; color: #378ADD;
        font-size: .8rem; font-weight: 600; transition: all .2s;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .expand-toggle:hover { background: #378ADD; color: #fff; }
    .expand-toggle i { font-size: 1rem; transition: transform .2s; }
    .expand-toggle.expanded i { transform: rotate(180deg); }

    .detail-row { display: none; }
    .detail-row.show { display: table-row; }
    .detail-cell {
        padding: 0 16px 16px 16px;
        background: #f8fbff;
    }

    /* ── Kelas Sub-Cards ── */
    .kelas-sub-cards { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 12px; }
    .kelas-sub-card {
        background: #fff; border: 1px solid #dce8f5; border-radius: 8px;
        padding: 14px 18px; min-width: 260px; flex: 1;
        transition: border-color .2s, box-shadow .2s;
    }
    .kelas-sub-card:hover { border-color: #378ADD; box-shadow: 0 2px 12px rgba(55, 138, 221, .1); }
    .kelas-sub-card h4 {
        font-size: .95rem; font-weight: 700; color: #0C447C; margin: 0 0 8px 0;
        display: flex; justify-content: space-between; align-items: center;
    }
    .kelas-sub-card .ksc-meta {
        font-size: .8rem; color: #6a9bc0; margin-bottom: 8px;
    }

    /* ── Missing Siswa List ── */
    .missing-title {
        font-size: .85rem; font-weight: 600; color: #c0392b; margin: 10px 0 6px 0;
        display: flex; align-items: center; gap: 6px;
    }
    .missing-list {
        list-style: none; padding: 0; margin: 0;
        max-height: 200px; overflow-y: auto;
    }
    .missing-list li {
        display: flex; justify-content: space-between; align-items: center;
        padding: 6px 10px; font-size: .82rem; color: #333;
        border-bottom: 1px solid #f0f0f0;
        height: auto; margin-top: 0;
    }
    .missing-list li:last-child { border-bottom: none; }
    .missing-list .ml-name { font-weight: 600; }
    .missing-list .ml-nis { color: #999; font-size: .78rem; }
    .missing-types-tags {
        display: flex; gap: 4px; flex-wrap: wrap;
    }
    .missing-type-tag {
        background: #fee2e2; color: #c0392b; font-size: .7rem;
        padding: 2px 6px; border-radius: 4px; font-weight: 600;
        text-transform: uppercase;
    }

    /* No data placeholder */
    .no-data {
        text-align: center; padding: 40px; color: #999; font-size: 1rem;
    }
    .no-data i { font-size: 3rem; display: block; margin-bottom: 12px; color: #ccc; }
</style>
@endsection

@section('content')
<div class="progress-header">
    <div>
        <h2>📊 Progress Monitoring</h2>
        <div class="meta">
            <i class='bx bx-calendar'></i> {{ $tahunAjaranLabel }} &bull; Semester {{ $semesterLabel }}
            @if($filterType !== 'all')
                &bull; Periode <strong>{{ $filterType === 'uas' ? 'Rapor Akhir' : strtoupper($filterType) }}</strong>
            @endif
        </div>
    </div>
</div>

@if($periodeNotSet)
<div style="display:flex; align-items:center; gap:12px; background:linear-gradient(135deg,#fff8f0,#fef3e6); border:1px solid #f5c77e; border-left:4px solid #e67e22; border-radius:8px; padding:14px 20px; margin-bottom:20px; font-size:.9rem; color:#7a5a2f;">
    <i class='bx bx-error-circle' style="font-size:1.4rem; color:#e67e22;"></i>
    <span><strong>Periode belum diatur!</strong> Silakan atur tanggal periode di <a href="{{ route('coordinator.manage-periode') }}" style="color:#378ADD; font-weight:600;">Manage Periode Nilai</a> untuk menghitung progress absensi secara akurat.</span>
</div>
@endif

<form method="GET" action="{{ route('coordinator.progress') }}" class="month-filter" style="margin-bottom:20px;">
    <label><i class='bx bx-filter-alt'></i> Periode:</label>
    <select name="filter_type" onchange="this.form.submit()" style="border:1px solid #c5d8ec; border-radius:6px; padding:6px 12px; font-size:.9rem; color:#0C447C; min-width:160px;">
        <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>Nilai Akhir (Semua)</option>
        <option value="uh1" {{ $filterType === 'uh1' ? 'selected' : '' }}>UH1</option>
        <option value="uts" {{ $filterType === 'uts' ? 'selected' : '' }}>UTS</option>
        <option value="uh2" {{ $filterType === 'uh2' ? 'selected' : '' }}>UH2</option>
        <option value="uas" {{ $filterType === 'uas' ? 'selected' : '' }}>Rapor Akhir</option>
    </select>
    <label style="margin-left:15px;"><i class='bx bx-book-reader'></i> Semester:</label>
    <select name="semester" onchange="this.form.submit()" style="border:1px solid #c5d8ec; border-radius:6px; padding:6px 12px; font-size:.9rem; color:#0C447C; min-width:120px;">
        <option value="1" {{ $activeSemester === '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
        <option value="2" {{ $activeSemester === '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
    </select>
    <input type="hidden" name="tab" value="{{ request('tab', 'guru') }}">
    @if($filterType !== 'all' && !$periodeNotSet)
    <span class="school-days-info" style="margin-left:auto;">
        📅 {{ $monthLabel }} &bull; <strong>{{ $schoolDays }}</strong> hari sekolah
    </span>
    @endif
</form>

{{-- Tabs --}}
@php
    $guruComplete = collect($guruProgress)->where('percentage', 100)->count();
    $guruPartial = collect($guruProgress)->where('percentage', '>', 0)->where('percentage', '<', 100)->count();
    $guruEmpty = collect($guruProgress)->where('percentage', 0)->count();

    $wkComplete = collect($walikelasProgress)->where('percentage', 100)->count();
    $wkPartial = collect($walikelasProgress)->where('percentage', '>', 0)->where('percentage', '<', 100)->count();
    $wkEmpty = collect($walikelasProgress)->where('percentage', 0)->count();
@endphp

<div class="tab-container">
    <button class="tab-btn active" onclick="switchTab('guru', this)" id="tab-btn-guru">
        <i class='bx bx-user-voice'></i> Guru Progress
        <span class="badge">{{ $guruEmpty }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('walikelas', this)" id="tab-btn-walikelas">
        <i class='bx bx-user-check'></i> Wali Kelas Progress
        <span class="badge">{{ $wkEmpty }}</span>
    </button>
    <a href="{{ route('coordinator.cetak', ['tipe' => strtoupper($filterType)]) }}" class="tab-btn" style="text-decoration:none;">
        <i class='bx bx-printer'></i> Cetak Rapor
    </a>
</div>

{{-- ═══ GURU TAB ═══ --}}
<div class="tab-panel active" id="panel-guru">


    <div class="summary-cards" id="guru-summary-cards">
        <div class="summary-card" data-filter="all" data-table="guru" onclick="filterByStatus(this)">
            <div class="sc-value">{{ count($guruProgress) }}</div>
            <div class="sc-label">Total Guru</div>
        </div>
        <div class="summary-card green" data-filter="complete" data-table="guru" onclick="filterByStatus(this)">
            <div class="sc-value">{{ $guruComplete }}</div>
            <div class="sc-label">Selesai</div>
        </div>
        <div class="summary-card yellow" data-filter="partial" data-table="guru" onclick="filterByStatus(this)">
            <div class="sc-value">{{ $guruPartial }}</div>
            <div class="sc-label">Dalam Proses</div>
        </div>
        <div class="summary-card red" data-filter="empty" data-table="guru" onclick="filterByStatus(this)">
            <div class="sc-value">{{ $guruEmpty }}</div>
            <div class="sc-label">Belum Input</div>
        </div>
    </div>

    @if(count($guruProgress) === 0)
        <div class="no-data"><i class='bx bx-info-circle'></i>Belum ada data guru.</div>
    @else
        <table class="progress-table" id="guru-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Nama Guru</th>
                    <th>Mata Pelajaran</th>
                    <th>Progress Keseluruhan</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th style="width:60px;">Detail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guruProgress as $idx => $guru)
                    @php
                        $colorClass = $guru->percentage >= 100 ? 'green' : ($guru->percentage >= 50 ? 'yellow' : 'red');
                        $statusClass = $guru->percentage >= 100 ? 'complete' : ($guru->percentage > 0 ? 'partial' : 'empty');
                        $statusLabel = $guru->percentage >= 100 ? 'Selesai' : ($guru->percentage > 0 ? 'Proses' : 'Belum');
                    @endphp
                    <tr data-status="{{ $statusClass }}">
                        <td>{{ $idx + 1 }}</td>
                        <td style="font-weight:600;">{{ $guru->nama }}</td>
                        <td>{{ $guru->mapel }}</td>
                        <td>
                            <div class="pbar-container">
                                <div class="pbar-track">
                                    <div class="pbar-fill {{ $colorClass }}" style="width: {{ min($guru->percentage, 100) }}%;"></div>
                                </div>
                                <span class="pbar-label {{ $colorClass }}">{{ $guru->percentage }}%</span>
                            </div>
                        </td>
                        <td style="font-size:.82rem; color:#666;">
                            {{ $guru->total_completed }}/{{ $guru->total_expected }}
                        </td>
                        <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td>
                            @if(count($guru->kelas_details) > 0)
                                <button class="expand-toggle" onclick="toggleDetail('guru-{{ $guru->id }}', this)">
                                    <i class='bx bx-chevron-down'></i>
                                </button>
                            @else
                                <span style="color:#ccc;">-</span>
                            @endif
                        </td>
                    </tr>
                    @if(count($guru->kelas_details) > 0)
                    <tr class="detail-row" id="detail-guru-{{ $guru->id }}">
                        <td colspan="7" class="detail-cell">
                            <div class="kelas-sub-cards">
                                @foreach($guru->kelas_details as $kd)
                                    @php
                                        $kdColor = $kd->percentage >= 100 ? 'green' : ($kd->percentage >= 50 ? 'yellow' : 'red');
                                    @endphp
                                    <a href="{{ route('coordinator.progress.detail', ['guru_id' => $guru->id, 'kelas_id' => $kd->kelas_id, 'filter_type' => $filterType, 'semester' => $activeSemester]) }}" class="kelas-sub-card" style="text-decoration:none; color:inherit; cursor:pointer;">
                                        <h4>
                                            <span>📚 Kelas {{ $kd->kelas_name }}</span>
                                            <span class="pbar-label {{ $kdColor }}">{{ $kd->percentage }}%</span>
                                        </h4>
                                        <div class="ksc-meta">
                                            {{ $kd->siswa_count }} siswa &bull; {{ $kd->completed }}/{{ $kd->expected }} nilai diinput
                                        </div>
                                        <div class="pbar-container" style="margin-bottom:6px;">
                                            <div class="pbar-track">
                                                <div class="pbar-fill {{ $kdColor }}" style="width: {{ min($kd->percentage, 100) }}%;"></div>
                                            </div>
                                        </div>
                                        @if(count($kd->missing_siswa) > 0)
                                            <div class="missing-title">
                                                <i class='bx bx-error-circle'></i>
                                                {{ count($kd->missing_siswa) }} siswa belum lengkap
                                            </div>
                                            <div style="color:#378ADD; font-size:.78rem; margin-top:4px; font-weight:600;">
                                                <i class='bx bx-right-arrow-alt'></i> Klik untuk lihat detail nilai
                                            </div>
                                        @else
                                            <div style="color:#1e8a5a; font-size:.82rem; margin-top:6px;">
                                                <i class='bx bx-check-circle'></i> Semua nilai telah lengkap
                                            </div>
                                            <div style="color:#378ADD; font-size:.78rem; margin-top:4px; font-weight:600;">
                                                <i class='bx bx-right-arrow-alt'></i> Klik untuk lihat detail nilai
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ═══ WALIKELAS TAB ═══ --}}
<div class="tab-panel" id="panel-walikelas">
    @if(!$periodeNotSet)
    <div style="margin-bottom: 20px;">
        <span class="school-days-info">
            📅 Target Kehadiran: <strong>{{ $schoolDays }}</strong> hari sekolah ({{ $monthLabel }})
        </span>
    </div>
    @endif


    <div class="summary-cards" id="wk-summary-cards">
        <div class="summary-card" data-filter="all" data-table="walikelas" onclick="filterByStatus(this)">
            <div class="sc-value">{{ count($walikelasProgress) }}</div>
            <div class="sc-label">Total Wali Kelas</div>
        </div>
        <div class="summary-card green" data-filter="complete" data-table="walikelas" onclick="filterByStatus(this)">
            <div class="sc-value">{{ $wkComplete }}</div>
            <div class="sc-label">Selesai</div>
        </div>
        <div class="summary-card yellow" data-filter="partial" data-table="walikelas" onclick="filterByStatus(this)">
            <div class="sc-value">{{ $wkPartial }}</div>
            <div class="sc-label">Dalam Proses</div>
        </div>
        <div class="summary-card red" data-filter="empty" data-table="walikelas" onclick="filterByStatus(this)">
            <div class="sc-value">{{ $wkEmpty }}</div>
            <div class="sc-label">Belum Input</div>
        </div>
    </div>

    @if(count($walikelasProgress) === 0)
        <div class="no-data"><i class='bx bx-info-circle'></i>Belum ada data wali kelas.</div>
    @else
        <table class="progress-table" id="walikelas-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Nama Wali Kelas</th>
                    <th>Kelas</th>
                    <th>Jumlah Siswa</th>
                    <th>Progress Absensi</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th style="width:60px;">Detail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($walikelasProgress as $idx => $wk)
                    @php
                        $colorClass = $wk->percentage >= 100 ? 'green' : ($wk->percentage >= 50 ? 'yellow' : 'red');
                        $statusClass = $wk->percentage >= 100 ? 'complete' : ($wk->percentage > 0 ? 'partial' : 'empty');
                        $statusLabel = $wk->percentage >= 100 ? 'Selesai' : ($wk->percentage > 0 ? 'Proses' : 'Belum');
                    @endphp
                    <tr data-status="{{ $statusClass }}">
                        <td>{{ $idx + 1 }}</td>
                        <td style="font-weight:600;">{{ $wk->nama }}</td>
                        <td>{{ $wk->kelas }}</td>
                        <td>{{ $wk->siswa_count }}</td>
                        <td>
                            <div class="pbar-container">
                                <div class="pbar-track">
                                    <div class="pbar-fill {{ $colorClass }}" style="width: {{ min($wk->percentage, 100) }}%;"></div>
                                </div>
                                <span class="pbar-label {{ $colorClass }}">{{ $wk->percentage }}%</span>
                            </div>
                        </td>
                        <td style="font-size:.82rem; color:#666;">
                            {{ $wk->completed }}/{{ $wk->expected }}
                        </td>
                        <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td>
                            @if(count($wk->missing_siswa) > 0)
                                <button class="expand-toggle" onclick="toggleDetail('wk-{{ $wk->id }}', this)">
                                    <i class='bx bx-chevron-down'></i>
                                </button>
                            @else
                                @if($wk->kelas !== '-')
                                    <span style="color:#1e8a5a;"><i class='bx bx-check'></i></span>
                                @else
                                    <span style="color:#ccc;">-</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @if(count($wk->missing_siswa) > 0)
                    <tr class="detail-row" id="detail-wk-{{ $wk->id }}">
                        <td colspan="8" class="detail-cell">
                            <div class="kelas-sub-cards">
                                <a href="{{ route('coordinator.progress.absensi-detail', ['walikelas_id' => $wk->id, 'filter_type' => $filterType, 'semester' => $activeSemester]) }}" class="kelas-sub-card" style="text-decoration:none; color:inherit; cursor:pointer; max-width:400px;">
                                    <h4>
                                        <span>📚 Kelas {{ $wk->kelas }}</span>
                                        <span class="pbar-label {{ $colorClass }}">{{ $wk->percentage }}%</span>
                                    </h4>
                                    <div class="ksc-meta">
                                        {{ $wk->siswa_count }} siswa &bull; {{ $wk->completed }}/{{ $wk->expected }} absensi diinput
                                    </div>
                                    <div class="pbar-container" style="margin-bottom:6px;">
                                        <div class="pbar-track">
                                            <div class="pbar-fill {{ $colorClass }}" style="width: {{ min($wk->percentage, 100) }}%;"></div>
                                        </div>
                                    </div>
                                    @if(count($wk->missing_siswa) > 0)
                                        <div class="missing-title">
                                            <i class='bx bx-error-circle'></i>
                                            {{ count($wk->missing_siswa) }} siswa absensi belum lengkap
                                        </div>
                                        <div style="color:#378ADD; font-size:.78rem; margin-top:4px; font-weight:600;">
                                            <i class='bx bx-right-arrow-alt'></i> Klik untuk lihat detail absensi
                                        </div>
                                    @else
                                        <div style="color:#1e8a5a; font-size:.82rem; margin-top:6px;">
                                            <i class='bx bx-check-circle'></i> Semua absensi telah lengkap ({{ $monthLabel }})
                                        </div>
                                        <div style="color:#378ADD; font-size:.78rem; margin-top:4px; font-weight:600;">
                                            <i class='bx bx-right-arrow-alt'></i> Klik untuk lihat detail absensi
                                        </div>
                                    @endif
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('panel-' + tab).classList.add('active');

        // Remember tab state for form submission
        var ts = document.getElementById('tab-state');
        if (ts) ts.value = tab;
    }

    function toggleDetail(id, btn) {
        var row = document.getElementById('detail-' + id);
        if (!row) return;
        row.classList.toggle('show');
        btn.classList.toggle('expanded');
    }

    function filterByStatus(card) {
        var filter = card.getAttribute('data-filter');
        var tableType = card.getAttribute('data-table');
        var containerId = tableType === 'guru' ? 'guru-summary-cards' : 'wk-summary-cards';
        var tableId = tableType === 'guru' ? 'guru-table' : 'walikelas-table';

        // Toggle active state on cards
        var container = document.getElementById(containerId);
        var cards = container.querySelectorAll('.summary-card');
        var wasActive = card.classList.contains('active-filter');

        cards.forEach(function(c) { c.classList.remove('active-filter'); });

        if (wasActive) {
            filter = 'all';
        } else {
            card.classList.add('active-filter');
        }

        // Filter table rows
        var table = document.getElementById(tableId);
        if (!table) return;
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            // Skip detail rows — they are controlled by toggleDetail
            if (row.classList.contains('detail-row')) {
                if (filter !== 'all') {
                    row.classList.remove('show');
                }
                return;
            }

            var status = row.getAttribute('data-status');
            if (filter === 'all' || status === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                // Collapse its detail row
                var next = row.nextElementSibling;
                if (next && next.classList.contains('detail-row')) {
                    next.classList.remove('show');
                }
            }
        });
    }

    // Restore tab on page load if tab param is present
    document.addEventListener('DOMContentLoaded', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var tab = urlParams.get('tab');
        if (tab === 'walikelas') {
            var btn = document.getElementById('tab-btn-walikelas');
            if (btn) switchTab('walikelas', btn);
        }
    });
</script>
@endsection
