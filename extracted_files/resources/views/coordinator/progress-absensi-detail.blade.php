@extends('layouts.app')

@section('page_title', 'Detail Absensi – Kelas ' . $walikelas->kelas)

@section('styles')
<style>
    /* ── Breadcrumb ── */
    .breadcrumb {
        display: flex; align-items: center; gap: 8px;
        font-size: .85rem; color: #6a9bc0; margin-bottom: 20px;
    }
    .breadcrumb a {
        color: #378ADD; text-decoration: none; font-weight: 500;
        transition: color .2s;
    }
    .breadcrumb a:hover { color: #0C447C; text-decoration: underline; }
    .breadcrumb .sep { color: #c5d8ec; }

    /* ── Page Header ── */
    .detail-header {
        background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 50%, #378ADD 100%);
        border-radius: 12px; padding: 28px 32px; margin-bottom: 24px;
        color: #fff; display: flex; justify-content: space-between;
        align-items: flex-start; flex-wrap: wrap; gap: 16px;
    }
    .detail-header h2 {
        font-size: 1.6rem; font-weight: 700; margin: 0 0 6px 0;
    }
    .detail-header .dh-sub {
        font-size: .9rem; opacity: .85;
        display: flex; flex-wrap: wrap; gap: 16px;
    }
    .detail-header .dh-sub span {
        display: inline-flex; align-items: center; gap: 5px;
    }

    /* ── Summary Strip ── */
    .summary-strip {
        display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;
    }
    .ss-card {
        flex: 1; min-width: 130px; background: #fff; border: 1px solid #dce8f5;
        border-radius: 10px; padding: 16px 20px; text-align: center;
        transition: transform .2s, box-shadow .2s; cursor: pointer; user-select: none;
    }
    .ss-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(12,68,124,.08); }
    .ss-card.active-filter { border: 2px solid #0C447C; box-shadow: 0 4px 16px rgba(12,68,124,.18); transform: translateY(-2px); }
    .ss-card .ss-val { font-size: 1.6rem; font-weight: 800; color: #0C447C; }
    .ss-card .ss-lbl { font-size: .75rem; color: #6a9bc0; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
    .ss-card.green .ss-val { color: #1e8a5a; }
    .ss-card.green.active-filter { border-color: #1e8a5a; }
    .ss-card.red .ss-val { color: #c0392b; }
    .ss-card.red.active-filter { border-color: #c0392b; }
    .ss-card.orange .ss-val { color: #e67e22; }
    .ss-card.orange.active-filter { border-color: #e67e22; }

    /* ── Attendance Banner ── */
    .attendance-banner {
        display: flex; align-items: center; gap: 12px;
        background: linear-gradient(135deg, #fff8f0 0%, #fef3e6 100%);
        border: 1px solid #f5c77e; border-left: 4px solid #e67e22;
        border-radius: 8px; padding: 12px 20px; margin-bottom: 20px;
        font-size: .88rem; color: #7a5a2f;
    }
    .attendance-banner i { font-size: 1.4rem; color: #e67e22; }
    .attendance-banner strong { color: #c0392b; }

    /* ── Table ── */
    .nilai-table {
        width: 100%; border-collapse: collapse; background: #fff;
        border-radius: 10px; overflow: hidden;
        box-shadow: 0 2px 12px rgba(12,68,124,.06);
    }
    .nilai-table thead th {
        background: #0C447C; color: #fff; padding: 12px 14px;
        font-size: .78rem; text-transform: uppercase; letter-spacing: .5px;
        text-align: left; white-space: nowrap;
        position: sticky; top: 0; z-index: 10;
    }
    .nilai-table tbody tr { border-bottom: 1px solid #eef3fa; transition: background .15s; }
    .nilai-table tbody tr:hover { background: #f5f9ff; }
    .nilai-table tbody tr.incomplete { background: #fff8f0; }
    .nilai-table tbody tr.incomplete:hover { background: #fff0e0; }
    .nilai-table tbody tr.below-threshold { background: #fff5f5; }
    .nilai-table tbody tr.below-threshold:hover { background: #ffe8e8; }
    .nilai-table td {
        padding: 10px 14px; font-size: .88rem; color: #333;
        text-align: left; vertical-align: middle;
    }

    /* ── Status Progress ── */
    .status-mini {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: .85rem; font-weight: 600; min-width: 180px;
    }
    .status-mini .mini-bar {
        width: 100px; height: 8px; background: #e8eef5; border-radius: 4px; overflow: hidden;
    }
    .status-mini .mini-fill {
        height: 100%; border-radius: 4px;
        background: linear-gradient(90deg, #4ecf9a, #27ae60);
    }
    .status-mini .mini-fill.yellow { background: linear-gradient(90deg, #f6d365, #f5a623); }
    .status-mini .mini-fill.red { background: linear-gradient(90deg, #ff7675, #d63031); }
    .status-mini.complete { color: #1e8a5a; }
    .status-mini.partial { color: #c79100; }
    .status-mini.empty { color: #c0392b; }

    /* ── Status Badge ── */
    .status-badge {
        display: inline-block; padding: 4px 12px; border-radius: 12px;
        font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
        text-align: center;
    }
    .status-badge.complete { background: #d4edda; color: #155724; }
    .status-badge.partial { background: #fff3cd; color: #856404; }
    .status-badge.empty { background: #f8d7da; color: #721c24; }

    /* ── Attendance Alert ── */
    .attendance-alert {
        display: inline-flex; align-items: center; gap: 6px;
        position: relative;
    }
    .att-alert-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 18px; height: 18px; border-radius: 50%;
        background: #c0392b; color: #fff; font-size: .65rem;
        font-weight: 800; cursor: help;
        animation: att-pulse 2s infinite;
    }
    @keyframes att-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(192, 57, 43, 0.4); }
        50% { box-shadow: 0 0 0 6px rgba(192, 57, 43, 0); }
    }
    .att-alert-icon .att-tooltip {
        display: none; position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%); background: #2d2d2d; color: #fff;
        padding: 6px 12px; border-radius: 6px; font-size: .72rem;
        font-weight: 500; white-space: nowrap; z-index: 100;
        pointer-events: none;
        animation: none;
    }
    .att-alert-icon .att-tooltip::after {
        content: ''; position: absolute; top: 100%; left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #2d2d2d;
    }
    .att-alert-icon:hover .att-tooltip { display: block; }

    /* ── Percentage badge ── */
    .pct-badge {
        display: inline-block; padding: 3px 10px; border-radius: 10px;
        font-size: .75rem; font-weight: 700; margin-left: 8px;
    }
    .pct-badge.good { background: #d4edda; color: #155724; }
    .pct-badge.warning { background: #f8d7da; color: #721c24; }

    /* ── Back button ── */
    .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.3);
        padding: 8px 18px; border-radius: 8px; font-size: .85rem; font-weight: 600;
        text-decoration: none; transition: all .2s;
    }
    .btn-back:hover { background: rgba(255,255,255,.25); }
</style>
@endsection

@section('content')
{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="{{ route('coordinator.progress', ['tab' => 'walikelas', 'filter_type' => $filterType]) }}"><i class='bx bx-line-chart'></i> Progress</a>
    <span class="sep">›</span>
    <span>{{ $walikelas->nama }}</span>
    <span class="sep">›</span>
    <span><strong>Kelas {{ $walikelas->kelas }}</strong></span>
</div>

{{-- Header --}}
<div class="detail-header">
    <div>
        <h2>📋 Absensi Kelas {{ $walikelas->kelas }}</h2>
        <div class="dh-sub">
            <span><i class='bx bx-user-check'></i> Wali Kelas: {{ $walikelas->nama }}</span>
            <span><i class='bx bx-calendar'></i> Periode: {{ $monthLabel }}</span>
            <span><i class='bx bx-group'></i> {{ $totalSiswa }} Siswa</span>
        </div>
    </div>
    <a href="{{ route('coordinator.progress', ['tab' => 'walikelas', 'filter_type' => $filterType]) }}" class="btn-back">
        <i class='bx bx-arrow-back'></i> Kembali
    </a>
</div>

@if($periodeNotSet)
<div style="display:flex; align-items:center; gap:12px; background:linear-gradient(135deg,#fff8f0,#fef3e6); border:1px solid #f5c77e; border-left:4px solid #e67e22; border-radius:8px; padding:14px 20px; margin-bottom:20px; font-size:.9rem; color:#7a5a2f;">
    <i class='bx bx-error-circle' style="font-size:1.4rem; color:#e67e22;"></i>
    <span><strong>Periode belum diatur!</strong> Tanggal periode untuk {{ strtoupper($filterType) }} belum dikonfigurasi. Data absensi tidak dapat dihitung.</span>
</div>
@endif

{{-- Summary --}}
<div class="summary-strip" id="absensi-summary-cards">
    <div class="ss-card" data-filter="all" onclick="filterAbsensiByStatus(this)">
        <div class="ss-val">{{ $totalSiswa }}</div>
        <div class="ss-lbl">Total Siswa</div>
    </div>
    <div class="ss-card green" data-filter="complete" onclick="filterAbsensiByStatus(this)">
        <div class="ss-val">{{ $completeSiswa }}</div>
        <div class="ss-lbl">Absensi Lengkap</div>
    </div>
    <div class="ss-card red" data-filter="incomplete" onclick="filterAbsensiByStatus(this)">
        <div class="ss-val">{{ $incompleteSiswa }}</div>
        <div class="ss-lbl">Belum Lengkap</div>
    </div>
    <div class="ss-card green" data-filter="above-threshold" onclick="filterAbsensiByStatus(this)">
        <div class="ss-val">{{ $aboveAttendanceCount }}</div>
        <div class="ss-lbl">Kehadiran &ge; {{ $attendanceThreshold }}%</div>
    </div>
    <div class="ss-card orange" data-filter="below-threshold" onclick="filterAbsensiByStatus(this)">
        <div class="ss-val">{{ $belowAttendanceCount }}</div>
        <div class="ss-lbl">Kehadiran &lt; {{ $attendanceThreshold }}%</div>
    </div>
</div>

{{-- Attendance Threshold Banner --}}
<div class="attendance-banner">
    <i class='bx bx-info-circle'></i>
    <span>
        Batas Minimum Kehadiran: <strong>{{ $attendanceThreshold }}%</strong> — 
        Siswa dengan persentase kehadiran di bawah {{ $attendanceThreshold }}% akan ditandai dengan ikon peringatan <span class="att-alert-icon" style="animation:none; cursor:default; display:inline-flex; width:16px; height:16px; font-size:.6rem;">!</span>
    </span>
</div>

{{-- Table --}}
@if(count($tableData) === 0)
    <div style="text-align:center; padding:40px; color:#999;">
        <i class='bx bx-info-circle' style="font-size:3rem; display:block; margin-bottom:12px; color:#ccc;"></i>
        Tidak ada siswa di kelas ini.
    </div>
@else
<table class="nilai-table" id="absensi-detail-table">
    <thead>
        <tr>
            <th style="width:40px; text-align:center;">#</th>
            <th>Nama Siswa</th>
            <th>NIS</th>
            <th>Progress Kehadiran (Periode {{ $monthLabel }})</th>
            <th style="text-align:center;">Persentase</th>
            <th style="text-align:center;">Status Lengkap</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tableData as $idx => $row)
            @php
                $pct = $row->total > 0 ? round(($row->completed / $row->total) * 100) : 0;
                $statusCls = $pct >= 100 ? 'complete' : ($pct > 0 ? 'partial' : 'empty');
                $barCls = $pct >= 100 ? '' : ($pct >= 50 ? 'yellow' : 'red');
                $isBelowThreshold = $row->completed > 0 && $row->attendance_pct < $attendanceThreshold;
                $rowClass = $isBelowThreshold ? 'below-threshold' : ($row->is_complete ? '' : 'incomplete');
            @endphp
            @php
                $attStatus = ($row->completed > 0 && $row->attendance_pct < $attendanceThreshold) ? 'below-threshold' : (($row->completed > 0) ? 'above-threshold' : '');
                $completionStatus = $row->is_complete ? 'complete' : 'incomplete';
            @endphp
            <tr class="{{ $rowClass }}" data-completion="{{ $completionStatus }}" data-attendance="{{ $attStatus }}">
                <td style="text-align:center;">{{ $idx + 1 }}</td>
                <td style="font-weight:600;">{{ $row->nama }}</td>
                <td style="color:#666; font-size:.82rem;">{{ $row->nis ?? '-' }}</td>
                <td>
                    <div class="status-mini {{ $statusCls }}">
                        <div class="mini-bar">
                            <div class="mini-fill {{ $barCls }}" style="width: {{ $pct }}%;"></div>
                        </div>
                        <span style="min-width:60px;">{{ $row->completed }} / {{ $row->total }} hari</span>
                    </div>
                </td>
                <td style="text-align:center;">
                    @if($row->completed > 0)
                        <div class="attendance-alert">
                            <span class="pct-badge {{ $row->attendance_pct >= $attendanceThreshold ? 'good' : 'warning' }}">
                                {{ $row->attendance_pct }}%
                            </span>
                            @if($isBelowThreshold)
                                <span class="att-alert-icon">!
                                    <span class="att-tooltip">Kehadiran di bawah {{ $attendanceThreshold }}%</span>
                                </span>
                            @endif
                        </div>
                    @else
                        <span style="color:#ccc;">-</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    @if($row->is_complete)
                        <span class="status-badge complete">Lengkap</span>
                    @else
                        @if($row->completed > 0)
                            <span class="status-badge partial">Proses</span>
                        @else
                            <span class="status-badge empty">Belum</span>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection

@section('scripts')
<script>
    function filterAbsensiByStatus(card) {
        var filter = card.getAttribute('data-filter');
        var container = document.getElementById('absensi-summary-cards');
        var cards = container.querySelectorAll('.ss-card');
        var wasActive = card.classList.contains('active-filter');

        cards.forEach(function(c) { c.classList.remove('active-filter'); });

        if (wasActive) {
            filter = 'all';
        } else {
            card.classList.add('active-filter');
        }

        var table = document.getElementById('absensi-detail-table');
        if (!table) return;
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var completion = row.getAttribute('data-completion');
            var attendance = row.getAttribute('data-attendance');

            if (filter === 'all') {
                row.style.display = '';
            } else if (filter === 'complete' || filter === 'incomplete') {
                row.style.display = (completion === filter) ? '' : 'none';
            } else if (filter === 'above-threshold' || filter === 'below-threshold') {
                row.style.display = (attendance === filter) ? '' : 'none';
            } else {
                row.style.display = '';
            }
        });
    }
</script>
@endsection
