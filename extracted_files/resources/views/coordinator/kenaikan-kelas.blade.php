@extends('layouts.app')
@section('page_title','Kenaikan Kelas')

@section('styles')
<style>
    .kk-container {
        max-width: 1100px;
        margin: 0 auto;
    }
    .kk-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .kk-header h2 {
        color: #0C447C;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .kk-info-card {
        background: linear-gradient(135deg, #0C447C 0%, #378ADD 100%);
        color: #fff;
        border-radius: 12px;
        padding: 20px 28px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .kk-info-card i {
        font-size: 2.5rem;
        opacity: .8;
    }
    .kk-info-card .info-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .kk-info-card .info-text p {
        font-size: .85rem;
        opacity: .85;
    }

    /* ── Kelas Cards Grid ── */
    .kk-kelas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .kk-kelas-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        cursor: pointer;
        transition: all .25s ease;
        border: 2px solid transparent;
        text-decoration: none;
        display: block;
    }
    .kk-kelas-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(55,138,221,.15);
        border-color: #378ADD;
    }
    .kk-kelas-card.active {
        border-color: #0C447C;
        background: linear-gradient(135deg, #f0f6ff 0%, #e0edfb 100%);
    }
    .kk-kelas-card .kelas-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0C447C;
        margin-bottom: 4px;
    }
    .kk-kelas-card .kelas-count {
        font-size: .82rem;
        color: #666;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .kk-kelas-card .kelas-count i {
        font-size: 1rem;
        color: #378ADD;
    }
    .kk-kelas-card .kelas-target {
        font-size: .75rem;
        color: #888;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .kk-kelas-card .kelas-target i {
        font-size: .85rem;
    }
    .kk-kelas-card .kelas-target .lulus-badge {
        color: #e6a200;
        font-weight: 600;
    }

    /* ── Form Section ── */
    .kk-form-section {
        background: #fff;
        border-radius: 12px;
        padding: 28px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        margin-bottom: 24px;
    }
    .kk-form-section h3 {
        color: #0C447C;
        font-size: 1.05rem;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .kk-tahun-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .kk-tahun-row label {
        font-weight: 600;
        color: #0C447C;
        min-width: 180px;
    }
    .kk-tahun-row select {
        padding: 10px 14px;
        border: 2px solid #dce8f5;
        border-radius: 8px;
        font-size: .95rem;
        color: #0C447C;
        background: #fff;
        min-width: 220px;
        outline: none;
        transition: border-color .2s;
    }
    .kk-tahun-row select:focus {
        border-color: #378ADD;
    }
    .kk-tahun-row .info-val {
        font-weight: 600;
        color: #378ADD;
        font-size: 1rem;
    }

    /* ── Quick Actions ── */
    .kk-quick-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .kk-quick-btn {
        padding: 8px 18px;
        border: 2px solid #dce8f5;
        border-radius: 8px;
        background: #fff;
        color: #0C447C;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .kk-quick-btn:hover {
        background: #f0f6ff;
        border-color: #378ADD;
    }
    .kk-quick-btn.active-naik {
        background: #e8f5e9;
        border-color: #4CAF50;
        color: #2e7d32;
    }
    .kk-quick-btn.active-lulus {
        background: #fff8e1;
        border-color: #FFC107;
        color: #f57f17;
    }
    .kk-quick-btn.active-tidak {
        background: #fce4ec;
        border-color: #e57373;
        color: #c62828;
    }

    /* ── Student Table ── */
    .kk-siswa-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .kk-siswa-table thead th {
        background: #0C447C;
        color: #fff;
        padding: 12px 16px;
        font-size: .85rem;
        font-weight: 600;
        text-align: left;
        position: sticky;
        top: 0;
    }
    .kk-siswa-table thead th:first-child { border-radius: 8px 0 0 0; }
    .kk-siswa-table thead th:last-child { border-radius: 0 8px 0 0; }
    .kk-siswa-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #e8eff7;
        vertical-align: middle;
    }
    .kk-siswa-table tbody tr:hover {
        background: #f0f6ff;
    }
    .kk-siswa-table tbody tr.status-naik td:first-child {
        border-left: 3px solid #4CAF50;
    }
    .kk-siswa-table tbody tr.status-lulus td:first-child {
        border-left: 3px solid #FFC107;
    }
    .kk-siswa-table tbody tr.status-tidak_aktif td:first-child {
        border-left: 3px solid #e57373;
    }
    .kk-siswa-table tbody tr.status-tinggal td:first-child {
        border-left: 3px solid #ffc107;
    }

    /* Status Badge */
    .status-display-badge {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: .88rem;
        font-weight: 600;
        border: 2px solid #dce8f5;
        text-align: center;
        min-width: 140px;
        transition: all .2s;
    }
    .status-display-badge.badge-naik {
        background: #e8f5e9;
        color: #2e7d32;
        border-color: #81c784;
    }
    .status-display-badge.badge-lulus {
        background: #fff8e1;
        color: #f57f17;
        border-color: #FFD54F;
    }
    .status-display-badge.badge-tidak_aktif {
        background: #fce4ec;
        color: #c62828;
        border-color: #ef9a9a;
    }
    .status-display-badge.badge-tinggal {
        background: #fff3cd;
        color: #856404;
        border-color: #ffeeba;
    }

    /* Summary badges */
    .kk-summary {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .kk-summary-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .9rem;
    }
    .kk-summary-item.naik {
        background: #e8f5e9;
        color: #2e7d32;
    }
    .kk-summary-item.lulus {
        background: #fff8e1;
        color: #f57f17;
    }
    .kk-summary-item.tidak {
        background: #fce4ec;
        color: #c62828;
    }
    .kk-summary-item .count {
        font-size: 1.2rem;
        font-weight: 700;
    }

    /* Actions */
    .kk-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }
    .kk-btn {
        padding: 12px 32px;
        border: none;
        border-radius: 8px;
        font-size: .95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .kk-btn-primary {
        background: linear-gradient(135deg, #0C447C 0%, #378ADD 100%);
        color: #fff;
    }
    .kk-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(55,138,221,.4);
    }
    .kk-btn-primary:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .kk-btn-secondary {
        background: #e8eff7;
        color: #0C447C;
    }
    .kk-btn-secondary:hover {
        background: #dce8f5;
    }

    /* Empty state */
    .kk-empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #888;
    }
    .kk-empty-state i {
        font-size: 3rem;
        margin-bottom: 12px;
        opacity: .4;
    }
    .kk-empty-state p {
        font-size: 1rem;
    }

    /* Section label */
    .kk-section-label {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
        font-weight: 600;
        margin-bottom: 12px;
    }

    /* Target info banner */
    .kk-target-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        border-radius: 10px;
        background: #f0f6ff;
        margin-bottom: 20px;
        border: 1px solid #dce8f5;
    }
    .kk-target-banner i {
        font-size: 1.5rem;
        color: #378ADD;
    }
    .kk-target-banner .target-label {
        font-size: .85rem;
        color: #666;
    }
    .kk-target-banner .target-value {
        font-weight: 700;
        color: #0C447C;
        font-size: 1rem;
    }

    /* ── Lock Banner ── */
    .kk-lock-banner {
        background: linear-gradient(135deg, #ff6b35 0%, #e03535 100%);
        color: #fff;
        border-radius: 12px;
        padding: 24px 28px;
        margin-bottom: 28px;
    }
    .kk-lock-banner .lock-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }
    .kk-lock-banner .lock-header i {
        font-size: 2.2rem;
        opacity: .9;
    }
    .kk-lock-banner .lock-header h3 {
        font-size: 1.15rem;
        font-weight: 700;
    }
    .kk-lock-banner .lock-header p {
        font-size: .85rem;
        opacity: .85;
        margin-top: 2px;
    }
    .kk-lock-checklist {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .kk-lock-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255,255,255,.15);
        border-radius: 8px;
        padding: 12px 16px;
    }
    .kk-lock-item i {
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .kk-lock-item .item-text {
        font-size: .9rem;
        font-weight: 500;
    }
    .kk-lock-item .item-bar {
        flex: 1;
        max-width: 180px;
        height: 8px;
        background: rgba(255,255,255,.25);
        border-radius: 4px;
        overflow: hidden;
        margin-left: auto;
    }
    .kk-lock-item .item-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width .4s ease;
    }
    .kk-lock-item.ok .item-bar-fill { background: #4ecf9a; }
    .kk-lock-item.fail .item-bar-fill { background: #FFD54F; }
    .kk-lock-item .item-pct {
        font-size: .85rem;
        font-weight: 700;
        min-width: 50px;
        text-align: right;
    }

    /* Disabled overlay for locked form */
    .kk-locked-overlay {
        position: relative;
        pointer-events: none;
        opacity: .45;
        filter: grayscale(.4);
    }
</style>
@endsection

@section('content')
@php
    $isXII = ($selectedKelas && isset($siswaList) && $siswaList->count() > 0) ? preg_match('/^XII-/', $selectedKelas->nama_kelas) : false;
@endphp
<div class="kk-container">
    <div class="kk-header">
        <h2><i class='bx bx-transfer'></i> Kenaikan Kelas</h2>
    </div>

    <div class="kk-info-card">
        <i class='bx bx-info-circle'></i>
        <div class="info-text">
            <h3>Promosi Siswa ke Kelas Berikutnya</h3>
            <p>Tahun Ajaran Aktif: <strong>{{ $activeTahunName }}</strong> — Pilih kelas untuk melihat daftar siswa, atur status masing-masing siswa, lalu proses kenaikan.</p>
        </div>
    </div>

    @if($isLocked)
    <div class="kk-lock-banner">
        <div class="lock-header">
            <i class='bx bx-lock-alt'></i>
            <div>
                <h3>Data Kenaikan Kelas Belum Lengkap</h3>
                <p>Kenaikan kelas belum dapat diproses. Pastikan semua syarat berikut terpenuhi:</p>
            </div>
        </div>
        <div class="kk-lock-checklist">
            <div class="kk-lock-item {{ $activeSemester === '2' ? 'ok' : 'fail' }}">
                <i class='bx {{ $activeSemester === "2" ? "bx-check-circle" : "bx-x-circle" }}'></i>
                <span class="item-text">Semester Genap (Aktif: {{ $activeSemester === '2' ? 'Genap ✓' : 'Ganjil ✗' }})</span>
            </div>
            <div class="kk-lock-item {{ $nilaiPercentage >= 100 ? 'ok' : 'fail' }}">
                <i class='bx {{ $nilaiPercentage >= 100 ? "bx-check-circle" : "bx-x-circle" }}'></i>
                <span class="item-text">Progress Nilai</span>
                <div class="item-bar"><div class="item-bar-fill" style="width:{{ min($nilaiPercentage, 100) }}%"></div></div>
                <span class="item-pct">{{ $nilaiPercentage }}%</span>
            </div>
            <div class="kk-lock-item {{ $absensiPercentage >= 100 ? 'ok' : 'fail' }}">
                <i class='bx {{ $absensiPercentage >= 100 ? "bx-check-circle" : "bx-x-circle" }}'></i>
                <span class="item-text">Progress Absensi</span>
                <div class="item-bar"><div class="item-bar-fill" style="width:{{ min($absensiPercentage, 100) }}%"></div></div>
                <span class="item-pct">{{ $absensiPercentage }}%</span>
            </div>
        </div>
    </div>
    @endif

    <!-- ── Pilih Kelas ── -->
    <div class="kk-section-label">Pilih Kelas</div>
    <div class="kk-kelas-grid">
        @foreach($kelasList as $k)
            @if($k->jumlah_siswa > 0)
            <a href="{{ route('coordinator.kenaikan-kelas', ['kelas_id' => $k->id]) }}"
               class="kk-kelas-card {{ $selectedKelasId == $k->id ? 'active' : '' }}">
                <div class="kelas-name">{{ $k->nama_kelas }}</div>
                <div class="kelas-count">
                    <i class='bx bx-group'></i> {{ $k->jumlah_siswa }} siswa
                </div>
                <div class="kelas-target">
                    @php $tid = $upgradeTargetMap[$k->id] ?? null; @endphp
                    @if($tid === 'lulus')
                        <i class='bx bx-graduation-cap'></i> <span class="lulus-badge">→ Lulus</span>
                    @elseif($tid)
                        @php $tkn = $kelasList->firstWhere('id', $tid); @endphp
                        <i class='bx bx-right-arrow-alt'></i> → {{ $tkn ? $tkn->nama_kelas : '?' }}
                    @else
                        <i class='bx bx-minus'></i> Tidak ada target
                    @endif
                </div>
            </a>
            @endif
        @endforeach
    </div>

    @if($selectedKelas && $siswaList->count() > 0)
    <!-- ── Form Kenaikan ── -->
    <div class="{{ $isLocked ? 'kk-locked-overlay' : '' }}">
    <form method="POST" action="{{ route('coordinator.kenaikan-kelas.action') }}" id="kenaikanForm">
        @csrf
        <input type="hidden" name="source_kelas_id" value="{{ $selectedKelas->id }}">

        <div class="kk-form-section">
            <h3><i class='bx bx-calendar-star'></i> Pengaturan</h3>
            <div class="kk-tahun-row">
                <label>Kelas Asal:</label>
                <span class="info-val">{{ $selectedKelas->nama_kelas }} ({{ $siswaList->count() }} siswa)</span>
            </div>
            @if($targetKelasName)
            <div class="kk-tahun-row">
                <label>Kelas Tujuan (Naik):</label>
                <span class="info-val">{{ $targetKelasName }}</span>
            </div>
            @endif
            <div class="kk-tahun-row">
                <label>Tahun Ajaran Sumber:</label>
                <span class="info-val">{{ $activeTahunName }} (aktif)</span>
            </div>
            <div class="kk-tahun-row">
                <label>Tahun Ajaran Tujuan:</label>
                <select name="target_tahun_id" required id="targetTahun">
                    <option value="">-- Pilih Tahun Ajaran Tujuan --</option>
                    @foreach($tahunList as $t)
                        @if($t->id != $activeTahunId)
                            <option value="{{ $t->id }}">{{ $t->nama }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <div class="kk-form-section">
            <h3><i class='bx bx-user-check'></i> Daftar Siswa — {{ $selectedKelas->nama_kelas }}</h3>

            @if($targetKelasName)
            <div class="kk-target-banner">
                <i class='bx bx-right-arrow-circle'></i>
                <div>
                    <div class="target-label">Siswa dengan status "Naik Kelas" akan dipindahkan ke:</div>
                    <div class="target-value">{{ $targetKelasName }}</div>
                </div>
            </div>
            @endif

            <!-- Bulk Action Toolbar (appears when any checkbox is checked) -->
            <div id="bulkActionToolbar" style="display:none; background:#E6F1FB; border:1.5px solid #378ADD; padding:15px; border-radius:10px; margin-bottom:20px; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; animation: slideUp 0.3s ease;">
                <div style="font-weight:600; color:#0C447C; display:flex; align-items:center; gap:8px;">
                    <i class='bx bx-check-square' style="font-size:1.3rem;"></i>
                    <span id="checkedCountText">0 siswa terpilih</span>
                </div>
                <div style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:5px;">
                        <label style="font-size:.85rem; font-weight:600; color:#0C447C; margin:0;">Status:</label>
                        <select id="bulkStatus" style="padding:6px 12px; border-radius:6px; border:1.5px solid #dce8f5;">
                            @if(!$isXII)
                                <option value="naik">✅ Naik Kelas</option>
                                <option value="tinggal">⚠️ Tinggal Kelas</option>
                                <option value="tidak_aktif">❌ Tidak Aktif</option>
                            @else
                                <option value="lulus">🎓 Lulus</option>
                                <option value="tidak_aktif">❌ Tidak Aktif</option>
                            @endif
                        </select>
                    </div>

                    @if(!$isXII)
                    <div id="bulkTargetWrap" style="display:flex; align-items:center; gap:5px;">
                        <label style="font-size:.85rem; font-weight:600; color:#0C447C; margin:0;">Kelas Tujuan:</label>
                        <select id="bulkTargetKelas" style="padding:6px 12px; border-radius:6px; border:1.5px solid #dce8f5;">
                            @foreach($availableTargetKelas as $tk)
                                <option value="{{ $tk->id }}" {{ ($upgradeTargetMap[$selectedKelas->id] ?? null) == $tk->id ? 'selected' : '' }}>{{ $tk->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="button" class="kk-btn kk-btn-primary" onclick="applyBulkAction()" style="padding:6px 16px; font-size:.85rem; border-radius:6px; margin:0;">
                        Terapkan Ke Terpilih
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="kk-quick-actions">
                @if(!$isXII)
                <button type="button" class="kk-quick-btn" onclick="setAllStatus('naik')">
                    <i class='bx bx-up-arrow-alt'></i> Set Semua Naik Kelas
                </button>
                <button type="button" class="kk-quick-btn" onclick="setAllStatus('tinggal')">
                    <i class='bx bx-refresh'></i> Set Semua Tinggal Kelas
                </button>
                @else
                <button type="button" class="kk-quick-btn" onclick="setAllStatus('lulus')">
                    <i class='bx bx-graduation-cap'></i> Set Semua Lulus
                </button>
                @endif
                <button type="button" class="kk-quick-btn" onclick="setAllStatus('tidak_aktif')">
                    <i class='bx bx-x-circle'></i> Set Semua Tidak Aktif
                </button>
            </div>

            <!-- Summary -->
            <div class="kk-summary" id="statusSummary">
                @if(!$isXII)
                <div class="kk-summary-item naik">
                    <i class='bx bx-up-arrow-alt'></i>
                    Naik Kelas: <span class="count" id="countNaik">0</span>
                </div>
                <div class="kk-summary-item tinggal" style="background: #fff3cd; color: #856404;">
                    <i class='bx bx-refresh'></i>
                    Tinggal Kelas: <span class="count" id="countTinggal">0</span>
                </div>
                @else
                <div class="kk-summary-item lulus">
                    <i class='bx bx-graduation-cap'></i>
                    Lulus: <span class="count" id="countLulus">0</span>
                </div>
                @endif
                <div class="kk-summary-item tidak">
                    <i class='bx bx-x-circle'></i>
                    Tidak Aktif: <span class="count" id="countTidak">0</span>
                </div>
            </div>

            <!-- Table -->
            <table class="kk-siswa-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">
                            <input type="checkbox" id="checkAllSiswa" onchange="toggleSelectAll(this)" style="transform: scale(1.2); cursor: pointer;">
                        </th>
                        <th style="width:50px;">No</th>
                        <th>Nama Siswa</th>
                        <th>NIS</th>
                        <th>Jenis Kelamin</th>
                        <th style="width:180px;">Status</th>
                        @if(!$isXII)
                        <th style="width:200px;">Kelas Tujuan</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswaList as $i => $s)
                    <tr class="siswa-row" data-siswa-id="{{ $s->id }}">
                        <td style="text-align:center; vertical-align: middle;">
                            <input type="checkbox" class="siswa-checkbox" data-siswa-id="{{ $s->id }}" onchange="onSiswaCheckChange()" style="transform: scale(1.1); cursor: pointer;">
                        </td>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight:600; color:#0C447C;">{{ $s->nama }}</td>
                        <td>{{ $s->nis }}</td>
                        <td>{{ $s->jenis_kelamin }}</td>
                        <td>
                            @if($isXII)
                                <span class="status-display-badge badge-lulus">🎓 Lulus</span>
                                <input type="hidden" name="statuses[{{ $s->id }}]" class="status-input" value="lulus">
                            @else
                                <span class="status-display-badge badge-naik">✅ Naik Kelas</span>
                                <input type="hidden" name="statuses[{{ $s->id }}]" class="status-input" value="naik">
                            @endif
                        </td>
                        @if(!$isXII)
                        <td style="vertical-align: middle; font-weight: 600; color: #0C447C; transition: opacity 0.2s;">
                            <span class="target-kelas-display">{{ $targetKelasName ?? '-' }}</span>
                            <input type="hidden" name="target_kelas_ids[{{ $s->id }}]" class="target-kelas-input" value="{{ $upgradeTargetMap[$selectedKelas->id] ?? '' }}">
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="kk-actions">
                <a href="{{ route('coordinator.kenaikan-kelas') }}" class="kk-btn kk-btn-secondary">
                    <i class='bx bx-arrow-back'></i> Kembali
                </a>
                <button type="submit" class="kk-btn kk-btn-primary" id="submitBtn" onclick="return confirmKenaikan()" {{ $isLocked ? 'disabled' : '' }}>
                    <i class='bx bx-check-circle'></i> Proses Kenaikan Kelas
                </button>
            </div>
        </div>
    </form>
    </div><!-- end locked overlay -->

    @elseif($selectedKelas && $siswaList->count() === 0)
    <div class="kk-form-section">
        <div class="kk-empty-state">
            <i class='bx bx-user-x'></i>
            <p>Tidak ada siswa di kelas <strong>{{ $selectedKelas->nama_kelas }}</strong> untuk tahun ajaran aktif.</p>
        </div>
    </div>

    @else
    <div class="kk-form-section">
        <div class="kk-empty-state">
            <i class='bx bx-pointer'></i>
            <p>Pilih kelas di atas untuk melihat daftar siswa dan mengatur kenaikan kelas.</p>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Update status display badge, input, row styling, and summary counts
    function updateRowStatus(row, newStatus) {
        const statusInput = row.querySelector('.status-input');
        const statusBadge = row.querySelector('.status-display-badge');
        
        if (statusInput && statusBadge) {
            statusInput.value = newStatus;
            
            let label = '';
            statusBadge.className = 'status-display-badge';
            if (newStatus === 'naik') {
                label = '✅ Naik Kelas';
                statusBadge.classList.add('badge-naik');
            } else if (newStatus === 'tinggal') {
                label = '⚠️ Tinggal Kelas';
                statusBadge.classList.add('badge-tinggal');
            } else if (newStatus === 'lulus') {
                label = '🎓 Lulus';
                statusBadge.classList.add('badge-lulus');
            } else if (newStatus === 'tidak_aktif') {
                label = '❌ Tidak Aktif';
                statusBadge.classList.add('badge-tidak_aktif');
            }
            statusBadge.textContent = label;
        }

        // Update row styling
        row.className = 'siswa-row status-' + newStatus;

        // Hide/Show target kelas display depending on status
        const targetInput = row.querySelector('.target-kelas-input');
        const targetDisplay = row.querySelector('.target-kelas-display');
        if (targetInput) {
            const td = targetInput.closest('td');
            if (newStatus === 'naik') {
                targetInput.disabled = false;
                if (td) td.style.opacity = '1';
                targetInput.value = "{{ $selectedKelas ? ($upgradeTargetMap[$selectedKelas->id] ?? '') : '' }}";
                if (targetDisplay) targetDisplay.textContent = "{{ $targetKelasName ?? '-' }}";
            } else if (newStatus === 'tinggal') {
                targetInput.disabled = false;
                targetInput.value = "{{ $selectedKelas ? $selectedKelas->id : '' }}";
                if (targetDisplay) targetDisplay.textContent = "{{ $selectedKelas ? $selectedKelas->nama_kelas : '' }} (Mengulang)";
                if (td) td.style.opacity = '1';
            } else {
                targetInput.disabled = true;
                if (td) td.style.opacity = '0.4';
            }
        }

        updateSummary();
    }

    function updateSummary() {
        let naik = 0, tinggal = 0, lulus = 0, tidak = 0;
        document.querySelectorAll('.status-input').forEach(input => {
            if (input.value === 'naik') naik++;
            else if (input.value === 'tinggal') tinggal++;
            else if (input.value === 'lulus') lulus++;
            else if (input.value === 'tidak_aktif') tidak++;
        });
        const elNaik = document.getElementById('countNaik');
        const elTinggal = document.getElementById('countTinggal');
        const elLulus = document.getElementById('countLulus');
        const elTidak = document.getElementById('countTidak');
        if (elNaik) elNaik.textContent = naik;
        if (elTinggal) elTinggal.textContent = tinggal;
        if (elLulus) elLulus.textContent = lulus;
        if (elTidak) elTidak.textContent = tidak;
    }

    function setAllStatus(status) {
        document.querySelectorAll('.siswa-row').forEach(row => {
            // For XII classes, don't allow 'naik'
            const isXII = {{ $isXII ? 'true' : 'false' }};
            if (status === 'naik' && isXII) return;

            updateRowStatus(row, status);
        });
    }

    // Checkbox and Bulk Actions logic
    function toggleSelectAll(masterCheckbox) {
        document.querySelectorAll('.siswa-checkbox').forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
        onSiswaCheckChange();
    }

    function onSiswaCheckChange() {
        const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
        const toolbar = document.getElementById('bulkActionToolbar');
        const countText = document.getElementById('checkedCountText');
        const totalChecked = checkedBoxes.length;

        if (totalChecked > 0) {
            toolbar.style.display = 'flex';
            countText.textContent = `${totalChecked} siswa terpilih`;
        } else {
            toolbar.style.display = 'none';
        }

        // Keep master checkbox in sync
        const totalCheckboxes = document.querySelectorAll('.siswa-checkbox').length;
        const masterCheckbox = document.getElementById('checkAllSiswa');
        if (masterCheckbox) {
            masterCheckbox.checked = (totalChecked === totalCheckboxes && totalCheckboxes > 0);
        }
    }

    function applyBulkAction() {
        const bulkStatus = document.getElementById('bulkStatus').value;
        const targetSelect = document.getElementById('bulkTargetKelas');
        const bulkTargetVal = targetSelect ? targetSelect.value : null;
        const bulkTargetText = targetSelect ? targetSelect.options[targetSelect.selectedIndex].text : '';

        const checkedBoxes = document.querySelectorAll('.siswa-checkbox:checked');
        checkedBoxes.forEach(cb => {
            const row = cb.closest('tr');
            updateRowStatus(row, bulkStatus);
            
            if (bulkStatus === 'naik' && bulkTargetVal) {
                const targetInput = row.querySelector('.target-kelas-input');
                const targetDisplay = row.querySelector('.target-kelas-display');
                if (targetInput) targetInput.value = bulkTargetVal;
                if (targetDisplay) targetDisplay.textContent = bulkTargetText;
            }
        });

        // Reset selections
        const masterCheckbox = document.getElementById('checkAllSiswa');
        if (masterCheckbox) masterCheckbox.checked = false;
        document.querySelectorAll('.siswa-checkbox').forEach(cb => cb.checked = false);
        onSiswaCheckChange();
    }

    function confirmKenaikan() {
        const target = document.getElementById('targetTahun');
        if (!target.value) {
            alert('Pilih tahun ajaran tujuan terlebih dahulu!');
            return false;
        }

        let naik = 0, tinggal = 0, lulus = 0, tidak = 0;
        document.querySelectorAll('.status-input').forEach(input => {
            if (input.value === 'naik') naik++;
            else if (input.value === 'tinggal') tinggal++;
            else if (input.value === 'lulus') lulus++;
            else if (input.value === 'tidak_aktif') tidak++;
        });

        const targetText = target.options[target.selectedIndex].text;
        let msg = `Proses kenaikan kelas ke ${targetText}?\n\n`;
        if (naik > 0) msg += `✅ ${naik} siswa naik kelas\n`;
        if (tinggal > 0) msg += `⚠️ ${tinggal} siswa tinggal kelas\n`;
        if (lulus > 0) msg += `🎓 ${lulus} siswa lulus\n`;
        if (tidak > 0) msg += `❌ ${tidak} siswa tidak aktif\n`;
        msg += `\nSiswa yang naik/tinggal kelas akan DISALIN ke tahun ajaran tujuan masing-masing kelas targetnya. Data lama TIDAK akan dihapus.`;

        return confirm(msg);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.siswa-row').forEach(row => {
            const statusInput = row.querySelector('.status-input');
            if (statusInput) {
                updateRowStatus(row, statusInput.value);
            }
        });

        // Toggle bulkTargetWrap based on bulkStatus value
        const bulkStatusEl = document.getElementById('bulkStatus');
        if (bulkStatusEl) {
            bulkStatusEl.addEventListener('change', function() {
                const bulkTargetWrap = document.getElementById('bulkTargetWrap');
                if (bulkTargetWrap) {
                    if (this.value === 'naik') {
                        bulkTargetWrap.style.display = 'flex';
                    } else {
                        bulkTargetWrap.style.display = 'none';
                    }
                }
            });
        }
    });
</script>
@endsection
