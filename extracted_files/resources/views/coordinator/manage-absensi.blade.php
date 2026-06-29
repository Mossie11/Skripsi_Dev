@extends('layouts.app')
@section('page_title', 'Absensi Siswa')
@section('styles')
<style>
    select option { background:#fff !important; color:#1a2a3a !important; }
    .absensi-container { width:100%; }
    .absensi-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; padding:1.5rem; background:linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); border-radius:8px; box-shadow:0 4px 15px rgba(12,68,124,.15); }
    .absensi-header h2 { font-size:1.4rem; color:#fff; margin:0; display:flex; align-items:center; gap:10px; }
    .controls-bar { display:flex; gap:15px; align-items:center; background:#fff; padding:1rem 1.5rem; border-radius:8px; border:1px solid #dce8f5; margin-bottom:1.5rem; flex-wrap:wrap; box-shadow:0 2px 8px rgba(0,0,0,.05); }
    .controls-bar label { font-weight:700; color:#0C447C; font-size:1rem; }
    .controls-bar select { background:#fff; color:#0C447C; border:2px solid #dce8f5; padding:10px 14px; border-radius:8px; font-size:1rem; outline:none; cursor:pointer; min-width:180px; transition:border-color .2s; }
    .controls-bar select:focus { border-color:#378ADD; }
    .month-picker { background:#fff; color:#0C447C; border:2px solid #dce8f5; padding:10px 14px; border-radius:8px; font-size:1rem; outline:none; cursor:pointer; transition:border-color .2s; }
    .month-picker:focus { border-color:#378ADD; }

    /* ── Modal Styles ── */
    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(12, 68, 124, 0.4); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000;
    }
    .modal-box {
        background: #fff; width: 100%; max-width: 480px; border-radius: 16px;
        padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); text-align: center;
    }
    .modal-icon { width: 64px; height: 64px; background: #E6F1FB; color: #378ADD; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px; }
    .modal-box h2 { color: #0C447C; margin: 0 0 8px; font-size: 1.5rem; }
    .modal-subtitle { color: #6a9bc0; margin-bottom: 24px; font-size: .95rem; }
    .modal-field { margin-bottom: 20px; text-align: left; }
    .modal-field label { display: block; font-weight: 600; color: #1a2a3a; margin-bottom: 8px; font-size: .9rem; display: flex; align-items: center; gap: 6px; }
    .modal-field select, .modal-field input { width: 100%; padding: 12px 14px; border: 2px solid #eef3fa; border-radius: 8px; font-size: .95rem; color: #333; outline: none; transition: all .2s; }
    .modal-field select:focus, .modal-field input:focus { border-color: #378ADD; background: #fbfdff; }
    .modal-btn {
        width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700;
        font-size: 1rem; cursor: pointer; transition: all .2s; margin-top: 8px;
    }
    .modal-btn.primary { background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); color: #fff; }
    .modal-btn.primary:hover { background: linear-gradient(135deg, #0d3e6e 0%, #155085 100%); transform: translateY(-1px); }
    .modal-btn.primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    /* ── Page Content ── */
    .page-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0 0 20px; flex-wrap: wrap; gap: 15px;
    }
    .page-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; color: #0C447C; }
    .page-header .header-meta {
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .meta-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 8px; font-size: .88rem; font-weight: 600;
    }
    .meta-badge.kelas { background: #EF9F27; color: #fff; }
    .meta-badge.bulan { background: #0C447C; color: #fff; }
    .btn-change {
        display: inline-flex; align-items: center; gap: 6px;
        background: #fff; border: 2px solid #dce8f5; color: #378ADD;
        padding: 8px 16px; border-radius: 8px; font-size: .85rem; font-weight: 600;
        cursor: pointer; transition: all .2s; text-decoration: none;
    }
    .btn-change:hover { border-color: #378ADD; background: #f0f7ff; }
    .header-actions { display: flex; gap: 10px; }
    .semester-badge { padding:6px 16px; border-radius:20px; font-size:.9rem; font-weight:600; background:#EF9F27; color:#fff; }
    .btn-edit { background:#378ADD; color:#fff; border:none; padding:.6rem 1.2rem; border-radius:6px; font-weight:bold; cursor:pointer; font-size:.95rem; transition:background .2s; }
    .btn-edit:hover { background:#0C447C; }
    .btn-success { background:#28a745; color:#fff; border:none; padding:.6rem 1.2rem; border-radius:6px; font-weight:bold; cursor:pointer; font-size:.95rem; display:none; transition:background .2s; }
    .btn-success:hover { background:#1e7e34; }
    .btn-danger { background:#dc3545; color:#fff; border:none; padding:.6rem 1.2rem; border-radius:6px; font-weight:bold; cursor:pointer; font-size:.95rem; display:none; transition:background .2s; }
    .btn-danger:hover { background:#bd2130; }
    .search-container { display:flex; margin-bottom:20px; background:#fff; border-radius:8px; overflow:hidden; border:2px solid #dce8f5; }
    .search-input { flex:1; background:#fff; border:none; padding:12px 15px; color:#0C447C; font-size:.95rem; outline:none; }
    .search-input::placeholder { color:#9fc8f0; }
    .search-btn { background:#378ADD; border:none; width:50px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background .2s; }
    .search-btn:hover { background:#0C447C; }
    .search-icon { width:18px; height:18px; fill:#fff; }
    .table-wrap { width:100%; overflow-x:auto; background:#fff; border-radius:10px; border:1px solid #dce8f5; padding:1rem; box-shadow:0 2px 12px rgba(0,0,0,.06); }
    .absensi-table { width:100%; border-collapse:collapse; min-width:1000px; font-size:.9rem; }
    .absensi-table th, .absensi-table td { border:1px solid #dce8f5; padding:8px; text-align:center; vertical-align:middle; color:#1a2a3a; }
    .absensi-table thead th { background:#0C447C; font-weight:700; color:#fff !important; text-transform:uppercase; letter-spacing:.04em; font-size:.85rem; }
    .absensi-table th.col-name { text-align:left; padding-left:12px; min-width:180px; }
    .absensi-table td.col-name { text-align:left; padding-left:12px; font-weight:600; color:#0C447C; }
    .absensi-table tbody tr:hover td { background:#f0f6ff; }
    .absensi-table tbody tr:hover td.col-name { background:#e0edf8; }
    .cell-span { font-weight:700; font-size:.85rem; }
    .cell-span.H { color:#28a745; }
    .cell-span.S { color:#EF9F27; }
    .cell-span.I { color:#378ADD; }
    .cell-span.A { color:#dc3545; }

    .empty-state { text-align:center; padding:3rem; color:#888; }
    .empty-state i { font-size:3rem; opacity:.4; margin-bottom:12px; }
    .empty-state p { font-size:1rem; }
    .legend { display:flex; gap:24px; margin-top:20px; padding:16px 24px; background:#fff; border-radius:12px; border:1px solid #dce8f5; box-shadow:0 4px 15px rgba(0,0,0,.03); flex-wrap:wrap; align-items:center; justify-content:center; }
    .legend-item { display:flex; align-items:center; gap:8px; font-size:.95rem; font-weight:700; color:#1a2a3a; }
    .legend-dot { width:18px; height:18px; border-radius:4px; box-shadow:0 2px 5px rgba(0,0,0,.1); }
    .legend-dot.H { background:#28a745; }
    .legend-dot.S { background:#EF9F27; }
    .legend-dot.I { background:#378ADD; }
    .legend-dot.A { background:#dc3545; }
    .legend-dot.M { background:#f8d7da; border:1px solid #f5c6cb; color:#c82333; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:900; box-shadow:none; }
    .stats-row { display:flex; gap:16px; margin-bottom:1.5rem; flex-wrap:wrap; }
    .stat-card { flex:1; min-width:140px; background:#fff; border-radius:10px; padding:16px 20px; border:1px solid #e8eff7; box-shadow:0 2px 8px rgba(0,0,0,.04); text-align:center; }
    .stat-card .stat-val { font-size:1.6rem; font-weight:700; color:#0C447C; }
    .stat-card .stat-label { font-size:.82rem; color:#888; margin-top:4px; }
    .stat-card.hadir .stat-val { color:#28a745; }
    .stat-card.sakit .stat-val { color:#EF9F27; }
    .stat-card.izin .stat-val { color:#378ADD; }
    .stat-card.alpha .stat-val { color:#dc3545; }
    .status-msg { margin-top:1rem; color:#28a745; font-weight:bold; display:none; padding:.6rem 1rem; background:#d4edda; border-radius:6px; }
</style>
@endsection

@section('content')
<div class="absensi-container">

    @if(!$selectedKelas)
    <div class="modal-overlay" id="selectModal">
        <div class="modal-box">
            <div class="modal-icon"><i class='bx bx-check-square'></i></div>
            <h2>Kelola Absensi</h2>
            <p class="modal-subtitle">Pilih kelas dan bulan untuk melihat/mengedit absensi siswa.</p>

            <form method="GET" id="modalFilterForm">
                <div class="modal-field">
                    <label for="modalKelas"><i class='bx bx-building'></i> Kelas</label>
                    <select name="kelas_id" id="modalKelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-field">
                    <label for="modalBulan"><i class='bx bx-calendar'></i> Bulan</label>
                    <input type="month" name="month" id="modalBulan" value="{{ $selectedMonth }}" required>
                </div>

                <button type="submit" class="modal-btn primary" id="modalSubmitBtn" disabled>
                    <i class='bx bx-right-arrow-alt'></i> Tampilkan Absensi
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($selectedKelas)
    <div class="page-header">
        <h1>📋 Kelola Absensi</h1>
        <div class="header-meta">
            <span class="meta-badge kelas"><i class='bx bx-building'></i> {{ $selectedKelas->nama_kelas }}</span>
            <span class="meta-badge bulan"><i class='bx bx-calendar'></i> {{ $monthName }}</span>
            <a href="{{ route('coordinator.manage-absensi') }}" class="btn-change">
                <i class='bx bx-refresh'></i> Ganti
            </a>
        </div>
    </div>

    @if($siswaList->count() > 0)
    @php
        $totalH = 0; $totalS = 0; $totalI = 0; $totalA = 0;
        foreach ($kehadiranMap as $sid => $days) {
            foreach ($days as $d => $v) {
                if ($v === 'H') $totalH++;
                elseif ($v === 'S') $totalS++;
                elseif ($v === 'I') $totalI++;
                elseif ($v === 'A') $totalA++;
            }
        }
    @endphp
    <div class="stats-row">
        <div class="stat-card hadir"><div class="stat-val">{{ $totalH }}</div><div class="stat-label">Hadir</div></div>
        <div class="stat-card sakit"><div class="stat-val">{{ $totalS }}</div><div class="stat-label">Sakit</div></div>
        <div class="stat-card izin"><div class="stat-val">{{ $totalI }}</div><div class="stat-label">Izin</div></div>
        <div class="stat-card alpha"><div class="stat-val">{{ $totalA }}</div><div class="stat-label">Alpha</div></div>
    </div>



    <div class="search-container">
        <input type="text" id="searchInput" class="search-input" placeholder="Search by Nama Siswa...">
        <button class="search-btn"><svg class="search-icon" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg></button>
    </div>

    <div class="table-wrap">
        <table class="absensi-table" id="absensiTable">
            <thead>
                <tr class="day-row">
                    <th rowspan="2" class="rowspan-header" width="40">No</th>
                    <th rowspan="2" class="rowspan-header" class="col-name">Nama Siswa</th>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                    @php 
                        $dateStr = $selectedMonth . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $dayOfWeek = date('w', strtotime($dateStr));
                        $isSunday = $dayOfWeek == 0;
                        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                        $dayName = $dayNames[$dayOfWeek];
                    @endphp
                    <th width="28" style="font-size: .75rem; font-weight: 600; padding: 4px; {{ $isSunday ? 'background-color: #dc3545; color: white; border-color: #dc3545;' : 'background-color: #0d4e8c; color: #e0edf8;' }}">{{ $dayName }}</th>
                    @endfor
                </tr>
                <tr>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                    @php 
                        $dateStr = $selectedMonth . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $isSunday = date('w', strtotime($dateStr)) == 0;
                    @endphp
                    <th style="{{ $isSunday ? 'background-color: #dc3545; color: white; border-color: #dc3545;' : '' }}" title="{{ $isSunday ? 'Hari Minggu' : '' }}">{{ $d }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($siswaList as $idx => $s)
                <tr data-siswa="{{ $s->id }}">
                    <td>{{ $idx + 1 }}</td>
                    <td class="col-name">{{ $s->nama }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                    @php 
                        $dateStr = $selectedMonth . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $isSunday = date('w', strtotime($dateStr)) == 0;
                        $val = $kehadiranMap[$s->id][$d] ?? ''; 
                    @endphp
                    <td style="padding:0; {{ $isSunday ? 'background-color: #f8d7da; border-color: #f5c6cb;' : '' }}">
                        @if($isSunday)
                            <div class="cell-container">
                                <span style="color: #c82333; font-size: .8rem; font-weight: 800; opacity: 0.7;">M</span>
                            </div>
                        @else
                            <div style="display:flex;align-items:center;justify-content:center;width:100%;height:30px;">
                                <span class="cell-span {{ $val }}" style="font-weight:700;font-size:.85rem;">{{ $val }}</span>
                            </div>
                        @endif
                    </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="legend">
        <div class="legend-item"><div class="legend-dot H"></div> H = Hadir</div>
        <div class="legend-item"><div class="legend-dot S"></div> S = Sakit</div>
        <div class="legend-item"><div class="legend-dot I"></div> I = Izin</div>
        <div class="legend-item"><div class="legend-dot A"></div> A = Alpha</div>
        <div class="legend-item" style="margin-left: 10px; border-left: 2px solid #eef3fa; padding-left: 30px;"><div class="legend-dot M">M</div> M = Minggu (Libur)</div>
    </div>

    @elseif($siswaList->count() === 0)
    <div class="table-wrap">
        <div class="empty-state">
            <i class='bx bx-user-x'></i>
            <p>Tidak ada siswa di kelas <strong>{{ $selectedKelas->nama_kelas }}</strong> untuk tahun ajaran aktif.</p>
        </div>
    </div>
    @endif
    
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Modal Validation
    const modalKelas = document.getElementById('modalKelas');
    const modalBulan = document.getElementById('modalBulan');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');

    function checkModalForm() {
        if (!modalSubmitBtn) return;
        modalSubmitBtn.disabled = !(modalKelas.value && modalBulan.value);
    }

    if (modalKelas) modalKelas.addEventListener('change', checkModalForm);
    if (modalBulan) modalBulan.addEventListener('input', checkModalForm);

    // Search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#absensiTable tbody tr').forEach(row => {
                const nama = row.querySelector('.col-name');
                if (nama) row.style.display = nama.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
</script>
@endsection
