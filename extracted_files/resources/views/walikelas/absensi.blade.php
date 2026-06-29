@extends('layouts.app')
@section('page_title', 'Absensi Kelas')
@section('styles')
<style>
    /* ── Updated Styles to match Coordinator View ── */
    select option { background:#fff !important; color:#1a2a3a !important; }
    .absensi-container { width:100%; max-width:1400px; margin:2rem auto; padding:0 1.5rem; }
    
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
    
    .header-actions { display: flex; gap: 10px; align-items: center; }
    .month-picker { background:#fff; color:#0C447C; border:2px solid #dce8f5; padding:10px 14px; border-radius:8px; font-size:1rem; outline:none; cursor:pointer; transition:border-color .2s; }
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
    
    .check-row { display:none; }
    .check-row th { background:#e0edf8; padding:4px; border-bottom:2px solid #555; color:#1a3a5a !important; }
    .check-row input { transform:scale(1.2); cursor:pointer; margin:0 auto; display:block; }
    .cell-container { display:flex; align-items:center; justify-content:center; width:100%; height:30px; }
    .cell-select { width:100%; height:100%; border:none; background:transparent; color:#1a2a3a; font-size:.9rem; text-align:center; font-weight:bold; cursor:pointer; appearance:none; -webkit-appearance:none; display:none; }
    .cell-select:focus { outline:none; background:#E6F1FB; }
    
    .cell-span { font-weight:700; font-size:.85rem; }
    .cell-span.H { color:#28a745; }
    .cell-span.S { color:#EF9F27; }
    .cell-span.I { color:#378ADD; }
    .cell-span.A { color:#dc3545; }
    .cell-span.L { color:#6c757d; }
    
    body.edit-mode .cell-span { display:none; }
    body.edit-mode .cell-select.editable { display:inline-block; }
    body.edit-mode .check-row { display:table-row; }
    body.edit-mode .btn-edit { display:none; }
    body.edit-mode .btn-success { display:inline-block; }
    body.edit-mode .btn-danger  { display:inline-block; }

    /* Locked cell styling for dates outside active period */
    body.edit-mode td.cell-locked { background-color: #f0f0f0 !important; }
    body.edit-mode td.cell-locked .cell-span { display:inline; color:#aaa !important; }
    body.edit-mode td.cell-locked .cell-select { display:none !important; }
    
    .legend { display:flex; gap:24px; margin-top:20px; padding:16px 24px; background:#fff; border-radius:12px; border:1px solid #dce8f5; box-shadow:0 4px 15px rgba(0,0,0,.03); flex-wrap:wrap; align-items:center; justify-content:center; }
    .legend-item { display:flex; align-items:center; gap:8px; font-size:.95rem; font-weight:700; color:#1a2a3a; }
    .legend-dot { width:18px; height:18px; border-radius:4px; box-shadow:0 2px 5px rgba(0,0,0,.1); }
    .legend-dot.H { background:#28a745; }
    .legend-dot.S { background:#EF9F27; }
    .legend-dot.I { background:#378ADD; }
    .legend-dot.A { background:#dc3545; }
    .legend-dot.L { background:#6c757d; }
    .legend-dot.M { background:#f8d7da; border:1px solid #f5c6cb; color:#c82333; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:900; box-shadow:none; }
    
    .stats-row { display:flex; gap:16px; margin-bottom:1.5rem; flex-wrap:wrap; }
    .stat-card { flex:1; min-width:140px; background:#fff; border-radius:10px; padding:16px 20px; border:1px solid #e8eff7; box-shadow:0 2px 8px rgba(0,0,0,.04); text-align:center; }
    .stat-card .stat-val { font-size:1.6rem; font-weight:700; color:#0C447C; }
    .stat-card .stat-label { font-size:.82rem; color:#888; margin-top:4px; }
    .stat-card.hadir .stat-val { color:#28a745; }
    .stat-card.sakit .stat-val { color:#EF9F27; }
    .stat-card.izin .stat-val { color:#378ADD; }
    .stat-card.alpha .stat-val { color:#dc3545; }
    .stat-card.libur .stat-val { color:#6c757d; }
    .status-msg { margin-top:1rem; color:#28a745; font-weight:bold; display:none; padding:.6rem 1rem; background:#d4edda; border-radius:6px; }

    /* ── Dropdown Select-All Button ── */
    .col-action-wrapper { position: relative; display: inline-flex; align-items: center; gap: 0; }
    .col-action-btn {
        background: #378ADD; color: #fff; border: none; border-radius: 4px;
        padding: 2px 6px; font-size: .72rem; font-weight: 700; cursor: pointer;
        display: inline-flex; align-items: center; gap: 2px; white-space: nowrap;
        transition: background .2s;
    }
    .col-action-btn:hover { background: #0C447C; }
    .col-action-btn i { font-size: .8rem; }
    .col-action-dropdown {
        display: none; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
        background: #fff; border: 1px solid #dce8f5; border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,.15); z-index: 100; min-width: 120px;
        overflow: hidden;
    }
    .col-action-dropdown.show { display: block; }
    .col-action-dropdown .dropdown-item {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 12px; font-size: .82rem; font-weight: 600; color: #1a2a3a;
        cursor: pointer; transition: background .15s; border: none; background: transparent;
        width: 100%; text-align: left;
    }
    .col-action-dropdown .dropdown-item:hover { background: #E6F1FB; }
    .col-action-dropdown .dropdown-item .dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }
    .col-action-dropdown .dropdown-item .dot.H { background: #28a745; }
    .col-action-dropdown .dropdown-item .dot.L { background: #6c757d; }
    .col-action-dropdown .dropdown-divider { height: 1px; background: #eef3fa; margin: 0; }
    .col-action-dropdown .dropdown-item.reset-item { color: #dc3545; }
</style>
@endsection

@section('content')
<div class="absensi-container">
    <div class="page-header">
        <h1>📋 Kelola Absensi</h1>
        <div class="header-meta">
            <span class="meta-badge kelas"><i class='bx bx-building'></i> {{ $myKelas }}</span>
            <span class="meta-badge bulan"><i class='bx bx-calendar'></i> {{ $monthName }}</span>
        </div>
        <div class="header-actions">
            <form method="GET" style="display:flex; align-items:center; margin-right:10px;">
                <input type="month" name="month" class="month-picker" value="{{ $selectedMonth }}" onchange="this.form.submit()">
            </form>
            @if($isOpen)
                <button class="btn-edit" id="btnEdit"><i class='bx bx-edit-alt'></i> Edit Absensi</button>
                <button class="btn-danger" id="btnCancel"><i class='bx bx-x'></i> Batal</button>
                <button class="btn-success" id="btnSave"><i class='bx bx-save'></i> Simpan</button>
            @else
                <span style="background: #f8d7da; color: #721c24; padding: 8px 15px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; border: 1px solid #f5c6cb;">
                    <i class='bx bx-lock-alt'></i> Periode Ditutup
                </span>
            @endif
        </div>
    </div>

    @if($siswaList->count() > 0)
    @php
        $totalH = 0; $totalS = 0; $totalI = 0; $totalA = 0; $totalL = 0;
        foreach ($kehadiranMap as $sid => $days) {
            foreach ($days as $d => $v) {
                if ($v === 'H') $totalH++;
                elseif ($v === 'S') $totalS++;
                elseif ($v === 'I') $totalI++;
                elseif ($v === 'A') $totalA++;
                elseif ($v === 'L') $totalL++;
            }
        }
    @endphp
    <div class="stats-row">
        <div class="stat-card hadir"><div class="stat-val">{{ $totalH }}</div><div class="stat-label">Hadir</div></div>
        <div class="stat-card sakit"><div class="stat-val">{{ $totalS }}</div><div class="stat-label">Sakit</div></div>
        <div class="stat-card izin"><div class="stat-val">{{ $totalI }}</div><div class="stat-label">Izin</div></div>
        <div class="stat-card alpha"><div class="stat-val">{{ $totalA }}</div><div class="stat-label">Alpha</div></div>
        <div class="stat-card libur"><div class="stat-val">{{ $totalL }}</div><div class="stat-label">Libur</div></div>
    </div>

    <div id="statusMsg" class="status-msg">✅ Absensi berhasil disimpan!</div>

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
                        $dayOfWeek = date('w', strtotime($dateStr));
                        $isSunday = $dayOfWeek == 0;
                        $isSaturday = $dayOfWeek == 6;
                    @endphp
                    <th style="{{ $isSunday ? 'background-color: #dc3545; color: white; border-color: #dc3545;' : '' }}" title="{{ $isSunday ? 'Hari Minggu' : ($isSaturday ? 'Tidak Ada Kelas' : '') }}">{{ $d }}</th>
                    @endfor
                </tr>
                <tr class="check-row">
                    @for($d = 1; $d <= $daysInMonth; $d++)
                    @php 
                        $dateStr = $selectedMonth . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $dayOfWeek = date('w', strtotime($dateStr));
                        $isSunday = $dayOfWeek == 0;
                        $isSaturday = $dayOfWeek == 6;
                        // Check if this date is within any active period range
                        $isEditable = false;
                        foreach ($editableRanges as $range) {
                            if ($dateStr >= $range['start'] && $dateStr <= $range['end']) {
                                $isEditable = true;
                                break;
                            }
                        }
                    @endphp
                    <th style="{{ $isSunday ? 'background-color: #f8d7da; border-color: #f5c6cb;' : '' }}">
                        @if(!$isSunday && !$isSaturday && $isEditable)
                        <div class="col-action-wrapper">
                            <button type="button" class="col-action-btn" data-col="{{ $d }}" onclick="toggleColDropdown(this)">
                                <i class='bx bx-chevron-down'></i>
                            </button>
                            <div class="col-action-dropdown" data-col="{{ $d }}">
                                <button class="dropdown-item" onclick="setColumnAll({{ $d }}, 'H', this)">
                                    <span class="dot H"></span> Hadir Semua
                                </button>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item" onclick="setColumnAll({{ $d }}, 'L', this)">
                                    <span class="dot L"></span> Libur Semua
                                </button>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item reset-item" onclick="setColumnAll({{ $d }}, '', this)">
                                    <i class='bx bx-reset' style="font-size:.9rem;"></i> Reset
                                </button>
                            </div>
                        </div>
                        @elseif(!$isSunday && !$isSaturday)
                        <i class='bx bx-lock-alt' style="color:#bbb; font-size:.8rem;" title="Di luar periode aktif"></i>
                        @endif
                    </th>
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
                        $dayOfWeek = date('w', strtotime($dateStr));
                        $isSunday = $dayOfWeek == 0;
                        $isSaturday = $dayOfWeek == 6;
                        $val = $kehadiranMap[$s->id][$d] ?? ''; 
                        // Check if this date is within any active period range
                        $isEditable = false;
                        foreach ($editableRanges as $range) {
                            if ($dateStr >= $range['start'] && $dateStr <= $range['end']) {
                                $isEditable = true;
                                break;
                            }
                        }
                    @endphp
                    <td style="padding:0; {{ $isSunday ? 'background-color: #f8d7da; border-color: #f5c6cb;' : '' }}" @if(!$isSunday && !$isSaturday && !$isEditable) class="cell-locked" @endif>
                        @if($isSunday)
                            <div class="cell-container">
                                <span style="color: #c82333; font-size: .8rem; font-weight: 800; opacity: 0.7;">M</span>
                            </div>
                        @elseif($isSaturday)
                            <div class="cell-container">
                            </div>
                        @else
                            <div class="cell-container">
                                <span class="cell-span {{ $val }}">{{ $val }}</span>
                                @if($isEditable)
                                <select class="cell-select editable" data-col="{{ $d }}" data-date="{{ $selectedMonth }}-{{ str_pad($d,2,'0',STR_PAD_LEFT) }}">
                                    <option value=""></option>
                                    <option value="H" {{ $val=='H'?'selected':'' }}>H</option>
                                    <option value="S" {{ $val=='S'?'selected':'' }}>S</option>
                                    <option value="I" {{ $val=='I'?'selected':'' }}>I</option>
                                    <option value="A" {{ $val=='A'?'selected':'' }}>A</option>
                                    <option value="L" {{ $val=='L'?'selected':'' }}>L</option>
                                </select>
                                @endif
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
        <div class="legend-item"><div class="legend-dot L"></div> L = Libur</div>
        <div class="legend-item" style="margin-left: 10px; border-left: 2px solid #eef3fa; padding-left: 30px;"><div class="legend-dot M">M</div> M = Minggu (Libur)</div>
    </div>
    @else
    <div class="table-wrap">
        <div style="text-align:center; padding:3rem; color:#888;">
            <i class='bx bx-user-x' style="font-size:3rem; opacity:.4; margin-bottom:12px;"></i>
            <p>Tidak ada siswa di kelas <strong>{{ $myKelas }}</strong> untuk tahun ajaran aktif.</p>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const ABSENSI_API = '{{ route("walikelas.api.absensi") }}';
    const CSRF = '{{ csrf_token() }}';

    const btnEdit = document.getElementById('btnEdit');
    const btnCancel = document.getElementById('btnCancel');
    const btnSave = document.getElementById('btnSave');

    if (btnEdit) {
        btnEdit.addEventListener('click', () => {
            document.body.classList.add('edit-mode');
            document.getElementById('statusMsg').style.display = 'none';
            document.querySelectorAll('.rowspan-header').forEach(th => th.setAttribute('rowspan', '3'));
        });
    }
    if (btnCancel) {
        btnCancel.addEventListener('click', () => {
            document.body.classList.remove('edit-mode');
            document.querySelectorAll('.cell-select.editable').forEach(sel => {
                sel.value = sel.previousElementSibling.textContent.trim();
            });
            closeAllDropdowns();
            document.querySelectorAll('.rowspan-header').forEach(th => th.setAttribute('rowspan', '2'));
        });
    }

    // Sync select -> span
    document.querySelectorAll('.cell-select.editable').forEach(sel => {
        sel.addEventListener('change', function() {
            this.previousElementSibling.textContent = this.value;
            this.previousElementSibling.className = 'cell-span ' + this.value;
        });
    });

    // ── Column Dropdown Functions ──
    function toggleColDropdown(btn) {
        const wrapper = btn.closest('.col-action-wrapper');
        const dropdown = wrapper.querySelector('.col-action-dropdown');
        const isOpen = dropdown.classList.contains('show');
        
        // Close all dropdowns first
        closeAllDropdowns();
        
        if (!isOpen) {
            dropdown.classList.add('show');
        }
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.col-action-dropdown.show').forEach(d => d.classList.remove('show'));
    }

    function setColumnAll(col, value, btnElement) {
        document.querySelectorAll(`.cell-select.editable[data-col="${col}"]`).forEach(sel => {
            sel.value = value;
            sel.previousElementSibling.textContent = sel.value;
            sel.previousElementSibling.className = 'cell-span ' + sel.value;
        });
        closeAllDropdowns();
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.col-action-wrapper')) {
            closeAllDropdowns();
        }
    });

    if (btnSave) {
        btnSave.addEventListener('click', () => {
            btnSave.textContent = 'Menyimpan...'; btnSave.disabled = true;
            const payload = [];
            document.querySelectorAll('tbody tr').forEach(tr => {
                const siswaId = tr.dataset.siswa;
                tr.querySelectorAll('.cell-select.editable').forEach(sel => {
                    if (sel.value) payload.push({ siswa_id: siswaId, tanggal: sel.dataset.date, status: sel.value });
                });
            });
            fetch(ABSENSI_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ data: payload })
            })
            .then(r => r.json())
            .then(res => {
                btnSave.textContent = '💾 Simpan Perubahan'; btnSave.disabled = false;
                if (res.success) {
                    document.body.classList.remove('edit-mode');
                    document.querySelectorAll('.rowspan-header').forEach(th => th.setAttribute('rowspan', '2'));
                    const msg = document.getElementById('statusMsg');
                    msg.style.display = 'block';
                    setTimeout(() => msg.style.display = 'none', 3000);
                } else alert('Gagal menyimpan.');
            })
            .catch(() => {
                btnSave.textContent = '💾 Simpan Perubahan'; btnSave.disabled = false;
                alert('Terjadi kesalahan jaringan.');
            });
        });
    }

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
