@extends('layouts.app')
@section('page_title', 'Cetak Rapor')
@section('styles')
<style>
    select option { background:#fff !important; color:#1a2a3a !important; }

    /* ── Modal Overlay ── */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(12, 68, 124, 0.45); backdrop-filter: blur(4px);
        z-index: 1000; display: flex; align-items: center; justify-content: center;
        animation: fadeIn .25s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-box {
        background: #fff; border-radius: 16px; padding: 36px 40px;
        width: 460px; max-width: 92vw;
        box-shadow: 0 20px 60px rgba(12, 68, 124, 0.25);
        animation: slideUp .3s ease;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-box .modal-icon {
        width: 56px; height: 56px; border-radius: 14px;
        background: linear-gradient(135deg, #0C447C 0%, #378ADD 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; color: #fff; margin: 0 auto 20px;
    }
    .modal-box h2 {
        text-align: center; font-size: 1.35rem; font-weight: 700;
        color: #0C447C; margin: 0 0 6px;
    }
    .modal-box .modal-subtitle {
        text-align: center; font-size: .88rem; color: #6a9bc0;
        margin: 0 0 28px;
    }

    .modal-field { margin-bottom: 20px; }
    .modal-field label {
        display: block; font-size: .82rem; font-weight: 700;
        color: #0C447C; margin-bottom: 8px; text-transform: uppercase;
        letter-spacing: .5px;
    }
    .modal-field select {
        width: 100%; padding: 12px 16px; border: 2px solid #dce8f5;
        border-radius: 10px; font-size: .95rem; color: #1a2a3a;
        background: #f8fbff; outline: none;
        transition: border-color .2s, box-shadow .2s;
        cursor: pointer; appearance: auto;
    }
    .modal-field select:focus {
        border-color: #378ADD;
        box-shadow: 0 0 0 3px rgba(55, 138, 221, 0.15);
    }

    .modal-btn {
        width: 100%; padding: 14px; border: none; border-radius: 10px;
        font-size: 1rem; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: all .2s; margin-top: 8px;
    }
    .modal-btn.primary {
        background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%);
        color: #fff;
    }
    .modal-btn.primary:hover { background: linear-gradient(135deg, #0d3e6e 0%, #155085 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,68,124,.2); }
    .modal-btn.primary:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

    /* ── Page Content (shown after modal) ── */
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
    .meta-badge.jenis { background: #0C447C; color: #fff; }
    .btn-change {
        display: inline-flex; align-items: center; gap: 6px;
        background: #fff; border: 2px solid #dce8f5; color: #378ADD;
        padding: 8px 16px; border-radius: 8px; font-size: .85rem; font-weight: 600;
        cursor: pointer; transition: all .2s; text-decoration: none;
    }
    .btn-change:hover { border-color: #378ADD; background: #f0f7ff; }

    .header-actions { display: flex; gap: 10px; }
    .btn { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 700; font-size: .88rem; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all .2s; }
    .btn-print { background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); color: #fff; }
    .btn-print:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,68,124,.2); }
    .btn-print:disabled { background: #a0c4e8; cursor: not-allowed; opacity: .6; transform: none; }

    .search-container {
        display: flex; margin-bottom: 20px; background: #fff;
        border-radius: 10px; overflow: hidden; border: 1px solid #dce8f5;
        box-shadow: 0 2px 8px rgba(12,68,124,.04);
    }
    .search-input { flex: 1; background: #fff; border: none; padding: 14px 18px; color: #1a2a3a; font-size: .9rem; outline: none; }
    .search-input::placeholder { color: #9fc8f0; }

    .table-container {
        background: #fff; border-radius: 10px; overflow-x: auto;
        border: 1px solid #dce8f5; box-shadow: 0 2px 12px rgba(12,68,124,.06);
    }
    .data-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
    .data-table th, .data-table td { text-align: left; padding: 14px 18px; border-bottom: 1px solid #eef3fa; color: #333; }
    .data-table th { font-weight: 700; color: #fff !important; background: #0C447C !important; font-size: .78rem; text-transform: uppercase; letter-spacing: .5px; }
    .data-table tbody tr { transition: background .15s; }
    .data-table tbody tr:hover { background: #f5f9ff; }

    .custom-checkbox { appearance: none; -webkit-appearance: none; width: 20px; height: 20px; background: #fff; border: 2px solid #c5d8ec; border-radius: 5px; cursor: pointer; display: inline-block; vertical-align: middle; transition: all .2s; }
    .custom-checkbox:checked { background: #378ADD; border-color: #378ADD; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E"); background-size: 80%; background-position: center; background-repeat: no-repeat; }
    .custom-checkbox:hover { border-color: #378ADD; }

    .print-icon {
        display: inline-flex; align-items: center; gap: 4px;
        color: #0C447C; font-weight: 600; text-decoration: none;
        background: #f0f7ff; padding: 6px 14px; border-radius: 6px;
        border: 1px solid #dce8f5; cursor: pointer; font-size: .82rem;
        transition: all .2s;
    }
    .print-icon:hover { background: #0C447C; color: #fff; border-color: #0C447C; }
    .print-icon.disabled { opacity: 0.5; cursor: not-allowed; background: #e0e0e0; color: #666; border-color: #ccc; }
    .print-icon.disabled:hover { background: #e0e0e0; color: #666; border-color: #ccc; }

    .info-box {
        padding: 40px; text-align: center; color: #6a9bc0;
        font-size: .95rem; background: #fff; border-radius: 10px;
        border: 1px solid #dce8f5;
    }
    .alert-banner {
        background: #fff3cd; color: #856404; border: 1px solid #ffeeba;
        padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;
        display: flex; align-items: center; gap: 10px; font-weight: 600;
    }
    .alert-banner.success {
        background: #d4edda; color: #155724; border-color: #c3e6cb;
    }
</style>
@endsection

@section('content')
@if(!$isGlobalComplete)
<div style="background:#fff; border-radius:16px; padding:40px; text-align:center; box-shadow:0 10px 30px rgba(12,68,124,0.08); max-width:600px; margin:4rem auto; border: 1px solid #dce8f5;">
    <div style="width:72px; height:72px; background:#fff3cd; color:#856404; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2.5rem; margin:0 auto 24px;">
        <i class='bx bx-lock-alt'></i>
    </div>
    <h2 style="color:#0C447C; font-weight:700; margin-bottom:12px;">Cetak Rapor Ditangguhkan</h2>
    <p style="color:#5a8ab0; font-size:.95rem; line-height:1.6; margin-bottom:28px;">
        @if($filterKelas)
            Pengisian nilai dan absensi untuk kelas <strong>{{ $kelasList[$filterKelas] ?? '-' }}</strong> pada periode <strong>{{ $jenisRapor === 'UAS' ? 'Rapor Akhir' : $jenisRapor }}</strong> belum lengkap (belum mencapai progress 100%). Halaman cetak rapor dikunci demi menjaga validitas data rapor siswa.
        @else
            Belum ada satu kelas pun yang lengkap data nilai dan absensinya untuk periode <strong>{{ $jenisRapor === 'UAS' ? 'Rapor Akhir' : $jenisRapor }}</strong> (belum mencapai progress 100%). Halaman cetak rapor dikunci demi menjaga validitas data rapor siswa.
        @endif
    </p>
    <div style="display:flex; justify-content:center; align-items:center; gap:10px; margin-bottom:15px;">
        <form method="GET" style="margin:0;display:inline-flex;align-items:center;gap:10px;" id="filterFormLock">
            <label for="jenisRaporSelLock" style="font-weight:bold;font-size:.9rem;color:#378ADD;">Jenis Rapor:</label>
            <select name="tipe" id="jenisRaporSelLock" class="filter-select" onchange="const urlParams = new URLSearchParams(window.location.search); urlParams.set('tipe', this.value); urlParams.delete('kelas'); window.location.search = urlParams.toString();" style="background:#0C447C; color:#fff; border:1px solid #dce8f5; padding:8px 15px; border-radius:4px; font-size:.9rem; outline:none; cursor:pointer;">
                <option value="UH1" {{ $jenisRapor==='UH1'?'selected':'' }}>UH1</option>
                <option value="UTS" {{ $jenisRapor==='UTS'?'selected':'' }}>UTS</option>
                <option value="UH2" {{ $jenisRapor==='UH2'?'selected':'' }}>UH2</option>
                <option value="UAS" {{ $jenisRapor==='UAS'?'selected':'' }}>Rapor Akhir</option>
            </select>
            <label for="semesterSelLock" style="font-weight:bold;font-size:.9rem;color:#378ADD;margin-left:10px;">Semester:</label>
            <select name="semester" id="semesterSelLock" class="filter-select" onchange="const urlParams = new URLSearchParams(window.location.search); urlParams.set('semester', this.value); urlParams.delete('kelas'); window.location.search = urlParams.toString();" style="background:#0C447C; color:#fff; border:1px solid #dce8f5; padding:8px 15px; border-radius:4px; font-size:.9rem; outline:none; cursor:pointer;">
                <option value="1" {{ $activeSemester==='1'?'selected':'' }}>1 (Ganjil)</option>
                <option value="2" {{ $activeSemester==='2'?'selected':'' }}>2 (Genap)</option>
            </select>
        </form>
    </div>
    @if($filterKelas)
        <a href="{{ route('coordinator.cetak', ['tipe' => $jenisRapor, 'semester' => $activeSemester]) }}" class="btn btn-print" style="margin-top:15px; background:#0C447C; color:#fff; text-decoration:none; display:inline-block; font-weight:bold; padding:8px 16px; border-radius:4px;">
            Kembali ke Pemilihan Kelas
        </a>
    @endif
</div>
@else

{{-- ═══ SELECTION MODAL ═══ --}}
@if(!$filterKelas)
<div class="modal-overlay" id="selectModal">
    <div class="modal-box">
        <div class="modal-icon"><i class='bx bx-printer'></i></div>
        <h2>Cetak Rapor</h2>
        <p class="modal-subtitle">Pilih kelas dan jenis rapor untuk memulai</p>

        <form method="GET" id="modalFilterForm">
            <div class="modal-field">
                <label for="modalTipe"><i class='bx bx-file'></i> Jenis Rapor</label>
                <select name="tipe" id="modalTipe" onchange="const urlParams = new URLSearchParams(window.location.search); urlParams.set('tipe', this.value); urlParams.delete('kelas'); window.location.search = urlParams.toString();">
                    <option value="UH1" {{ $jenisRapor === 'UH1' ? 'selected' : '' }}>UH1</option>
                    <option value="UTS" {{ $jenisRapor === 'UTS' ? 'selected' : '' }}>UTS</option>
                    <option value="UH2" {{ $jenisRapor === 'UH2' ? 'selected' : '' }}>UH2</option>
                    <option value="UAS" {{ $jenisRapor === 'UAS' ? 'selected' : '' }}>Rapor Akhir</option>
                </select>
            </div>

            <div class="modal-field">
                <label for="modalSemester"><i class='bx bx-book-reader'></i> Semester</label>
                <select name="semester" id="modalSemester" onchange="const urlParams = new URLSearchParams(window.location.search); urlParams.set('semester', this.value); urlParams.delete('kelas'); window.location.search = urlParams.toString();">
                    <option value="1" {{ $activeSemester === '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $activeSemester === '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </div>

            <div class="modal-field">
                <label for="modalKelas"><i class='bx bx-building'></i> Kelas</label>
                <select name="kelas" id="modalKelas" required {{ count($kelasList) === 0 ? 'disabled' : '' }}>
                    @if(count($kelasList) > 0)
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $kelasId => $kelasNama)
                            @php
                                $isComp = isset($kelasProgress[$kelasId]) && $kelasProgress[$kelasId];
                            @endphp
                            <option value="{{ $kelasId }}" {{ !$isComp ? 'disabled style=color:#a0a0a0;' : '' }}>
                                {{ $kelasNama }} {{ !$isComp ? '(Belum Lengkap)' : '(Lengkap)' }}
                            </option>
                        @endforeach
                    @else
                        <option value="">-- Tidak ada kelas --</option>
                    @endif
                </select>
            </div>

            <button type="submit" class="modal-btn primary" id="modalSubmitBtn" disabled>
                <i class='bx bx-right-arrow-alt'></i> Tampilkan Siswa
            </button>
        </form>
    </div>
</div>
@endif

{{-- ═══ PAGE CONTENT (only when kelas selected) ═══ --}}
@if($filterKelas)
<div class="page-header">
    <h1>📄 Cetak Rapor</h1>
    <div class="header-meta">
        <span class="meta-badge kelas"><i class='bx bx-building'></i> {{ $kelasList[$filterKelas] ?? '-' }}</span>
        <span class="meta-badge jenis"><i class='bx bx-file'></i> {{ $jenisRapor === 'UAS' ? 'Rapor Akhir' : $jenisRapor }}</span>
        <a href="{{ route('coordinator.cetak', ['tipe' => $jenisRapor, 'semester' => $activeSemester]) }}" class="btn-change">
            <i class='bx bx-refresh'></i> Ganti
        </a>
    </div>
    <div class="header-actions">
        <button class="btn btn-print" id="btnCetakTerpilih" disabled>
            <i class='bx bx-printer'></i> CETAK TERPILIH
        </button>
    </div>
</div>

    @if(!$isComplete)
        <div class="alert-banner" style="background:#fff3cd; color:#856404; border-color:#ffeeba;">
            <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
            <div>
                Peringatan: Data nilai <strong>{{ $jenisRapor === 'UAS' ? 'Rapor Akhir' : $jenisRapor }}</strong> belum lengkap 
                (Terisi: {{ $completedCount }} dari {{ $expectedCount }} nilai). Rapor tetap dapat dicetak, namun hasilnya mungkin belum sempurna.
            </div>
        </div>
    @else
        <div class="alert-banner success">
            <i class='bx bx-check-circle' style="font-size: 1.4rem;"></i>
            <div>
                Data nilai <strong>{{ $jenisRapor === 'UAS' ? 'Rapor Akhir' : $jenisRapor }}</strong> sudah lengkap. Rapor siap dicetak.
            </div>
        </div>
    @endif

<div class="search-container">
    <input type="text" id="searchInput" class="search-input" placeholder="🔍 Cari berdasarkan Nama atau NIS...">
</div>

<div class="table-container">
    <table class="data-table" id="siswaTable">
        <thead>
            <tr>
                <th width="40" style="text-align:center;"><input type="checkbox" class="custom-checkbox" id="selectAll"></th>
                <th width="50">No</th>
                <th>Nama Siswa</th>
                <th>NIS</th>
                <th>Kelas</th>
                <th width="120" style="text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswaList as $idx => $s)
            <tr class="data-row">
                <td style="text-align:center;"><input type="checkbox" class="custom-checkbox row-check" data-id="{{ $s->id }}"></td>
                <td>{{ $idx + 1 }}</td>
                <td class="col-nama" style="font-weight:600;">{{ $s->nama ?: '-' }}</td>
                <td class="col-nis" style="color:#666; font-size:.85rem;">{{ $s->nis ?: '-' }}</td>
                <td>{{ $s->nama_kelas ?: '-' }}</td>
                <td style="text-align:center;">
                    <span class="print-icon {{ !$isGlobalComplete ? 'disabled' : '' }}" onclick="cetakIndividu('{{ $s->id }}')" style="{{ !$isGlobalComplete ? 'opacity:0.5;cursor:not-allowed;' : '' }}"><i class='bx bx-printer'></i> Cetak</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="info-box" style="border:none;">Tidak ada data siswa untuk kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif
@endif
@endsection

@section('scripts')
<script>
    // ── Modal logic ──
    var modalKelas = document.getElementById('modalKelas');
    var modalSubmitBtn = document.getElementById('modalSubmitBtn');
    if (modalKelas) {
        modalKelas.addEventListener('change', function() {
            modalSubmitBtn.disabled = !this.value;
        });
    }

    // ── Page logic (only when kelas is selected) ──
    const ACTIVE_YEAR = @json($activeYear);
    const MY_KELAS   = @json($filterKelas);
    const JENIS      = @json(strtolower($jenisRapor));
    const SEMESTER   = @json($activeSemester);
    const IS_GLOBAL_COMPLETE = @json($isGlobalComplete);

    const IS_COMPLETE = @json($isComplete);

    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.data-row').forEach(row => {
                const nama = row.querySelector('.col-nama').textContent.toLowerCase();
                const nis  = row.querySelector('.col-nis').textContent.toLowerCase();
                row.style.display = (nama+nis).includes(q) ? '' : 'none';
            });
            updateBtnState();
        });
    }

    const selectAll = document.getElementById('selectAll');
    const rowChecks = document.querySelectorAll('.row-check');
    const btnCetak  = document.getElementById('btnCetakTerpilih');

    function updateBtnState() {
        if(!btnCetak) return;
        if (!IS_GLOBAL_COMPLETE) {
            btnCetak.disabled = true;
            return;
        }
        btnCetak.disabled = !Array.from(rowChecks).some(c => c.checked && c.closest('.data-row').style.display !== 'none');
    }

    if(selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.data-row').forEach(row => {
                if (row.style.display !== 'none') row.querySelector('.row-check').checked = this.checked;
            });
            updateBtnState();
        });
    }

    rowChecks.forEach(chk => {
        chk.addEventListener('change', function() {
            let allChecked = true;
            document.querySelectorAll('.data-row').forEach(r => {
                if (r.style.display !== 'none' && !r.querySelector('.row-check').checked) allChecked = false;
            });
            if(selectAll) selectAll.checked = allChecked;
            updateBtnState();
        });
    });

    if(btnCetak) {
        btnCetak.addEventListener('click', function() {
            if (!IS_GLOBAL_COMPLETE) {
                alert('Cetak rapor ditangguhkan karena penginputan nilai/absensi kelas ini belum mencapai progress 100%.');
                return;
            }
            const ids = Array.from(rowChecks)
                .filter(c => c.checked && c.closest('.data-row').style.display !== 'none')
                .map(c => c.dataset.id);
            if (ids.length > 0) {
                window.open(`/coordinator/print-rapor?siswa_ids=${ids.join(',')}&tipe=${JENIS}&tahun=${encodeURIComponent(ACTIVE_YEAR)}&kelas=${encodeURIComponent(MY_KELAS)}&semester=${encodeURIComponent(SEMESTER)}`, '_blank');
            }
        });
    }

    function cetakIndividu(siswaNis) {
        if (!IS_GLOBAL_COMPLETE) {
            alert('Cetak rapor ditangguhkan karena penginputan nilai/absensi kelas ini belum mencapai progress 100%.');
            return;
        }
        window.open(`/coordinator/print-rapor?siswa_ids=${siswaNis}&tipe=${JENIS}&tahun=${encodeURIComponent(ACTIVE_YEAR)}&kelas=${encodeURIComponent(MY_KELAS)}&semester=${encodeURIComponent(SEMESTER)}`, '_blank');
    }
</script>
@endsection
