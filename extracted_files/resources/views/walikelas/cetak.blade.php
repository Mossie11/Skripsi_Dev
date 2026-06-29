@extends('layouts.app')
@section('page_title', 'Cetak Report')
@section('styles')
<style>
    select option { background:#fff !important; color:#1a2a3a !important; }
    .header { display:flex; justify-content:space-between; align-items:center; padding:1.5rem 0; flex-wrap:wrap; gap:15px; }
    .header h1 { font-size:1.5rem; font-weight:600; margin:0; color:#0C447C; }
    .filters { display:flex; gap:15px; align-items:center; }
    .filter-select { background:#0C447C; color:#fff; border:1px solid #dce8f5; padding:8px 15px; border-radius:4px; font-size:.9rem; outline:none; cursor:pointer; }
    .header-actions { display:flex; gap:10px; }
    .btn { padding:8px 15px; border:none; border-radius:4px; font-weight:bold; font-size:.85rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
    .btn-print { background:#0C447C; color:#fff; }
    .btn-print:hover:not(:disabled) { background:#0d8a6b; }
    .btn-print:disabled { background:#378ADD; cursor:not-allowed; opacity:.6; }
    .search-container { display:flex; margin-bottom:20px; background:#E6F1FB; border-radius:4px; overflow:hidden; border:1px solid #dce8f5; }
    .search-input { flex:1; background:#E6F1FB; border:none; padding:12px 15px; color:#378ADD; font-size:.9rem; outline:none; }
    .search-input::placeholder { color:#6a9bc0; }
    .table-container { background:#fff; border-radius:4px; overflow-x:auto; border:1px solid #dce8f5; }
    .data-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .data-table th, .data-table td { text-align:left; padding:12px 15px; border-bottom:1px solid #dce8f5; color:#1a2a3a; }
    .data-table th { font-weight:bold; color:#fff !important; background:#0C447C !important; }
    .data-table tbody tr:hover { background:#e0edf8; }
    .custom-checkbox { appearance:none; -webkit-appearance:none; width:18px; height:18px; background:#fff; border:2px solid #7baada; border-radius:4px; cursor:pointer; display:inline-block; vertical-align:middle; transition:all .2s; }
    .custom-checkbox:checked { background:#378ADD; border-color:#378ADD; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E"); background-size:80%; background-position:center; background-repeat:no-repeat; }
    .print-icon { color:#fff; font-weight:bold; text-decoration:none; display:inline-block; text-align:center; background:#0C447C; padding:5px 10px; border-radius:4px; border:1px solid #dce8f5; cursor:pointer; }
    .print-icon:hover { background:#E6F1FB; color:#0C447C; border-color:#378ADD; }
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
        Pengisian nilai dan absensi untuk kelas Anda pada periode <strong>{{ $jenisRapor === 'UAS' ? 'Rapor Akhir' : $jenisRapor }}</strong> belum lengkap (belum mencapai progress 100%). Halaman cetak rapor dikunci demi menjaga validitas data rapor siswa.
    </p>
    <div style="display:flex; justify-content:center; align-items:center; gap:10px; margin-bottom:15px;">
        <form method="GET" style="margin:0;display:inline-flex;align-items:center;gap:10px;" id="filterFormLock">
            <label for="jenisRaporSelLock" style="font-weight:bold;font-size:.9rem;color:#378ADD;">Jenis Rapor:</label>
            <select name="tipe" id="jenisRaporSelLock" class="filter-select" onchange="this.form.submit()" style="background:#0C447C; color:#fff; border:1px solid #dce8f5; padding:8px 15px; border-radius:4px; font-size:.9rem; outline:none; cursor:pointer;">
                <option value="UH1" {{ $jenisRapor==='UH1'?'selected':'' }}>UH1</option>
                <option value="UTS" {{ $jenisRapor==='UTS'?'selected':'' }}>UTS</option>
                <option value="UH2" {{ $jenisRapor==='UH2'?'selected':'' }}>UH2</option>
                <option value="UAS" {{ $jenisRapor==='UAS'?'selected':'' }}>Rapor Akhir</option>
            </select>
            <label for="semesterSelLock" style="font-weight:bold;font-size:.9rem;color:#378ADD;margin-left:10px;">Semester:</label>
            <select name="semester" id="semesterSelLock" class="filter-select" onchange="this.form.submit()" style="background:#0C447C; color:#fff; border:1px solid #dce8f5; padding:8px 15px; border-radius:4px; font-size:.9rem; outline:none; cursor:pointer;">
                <option value="1" {{ $activeSemester==='1'?'selected':'' }}>1 (Ganjil)</option>
                <option value="2" {{ $activeSemester==='2'?'selected':'' }}>2 (Genap)</option>
            </select>
        </form>
    </div>
</div>
@else
<div class="header">
    <h1>Cetak Rapor – Kelas {{ $myKelas }}</h1>
    <div class="filters">
        <form method="GET" style="margin:0;display:inline-flex;align-items:center;gap:10px;" id="filterForm">
            <label for="jenisRaporSel" style="font-weight:bold;font-size:.9rem;color:#378ADD;">Jenis Rapor:</label>
            <select name="tipe" id="jenisRaporSel" class="filter-select" onchange="this.form.submit()">
                <option value="UH1" {{ $jenisRapor==='UH1'?'selected':'' }}>UH1</option>
                <option value="UTS" {{ $jenisRapor==='UTS'?'selected':'' }}>UTS</option>
                <option value="UH2" {{ $jenisRapor==='UH2'?'selected':'' }}>UH2</option>
                <option value="UAS" {{ $jenisRapor==='UAS'?'selected':'' }}>Rapor Akhir</option>
            </select>
            <label for="semesterSel" style="font-weight:bold;font-size:.9rem;color:#378ADD;margin-left:10px;">Semester:</label>
            <select name="semester" id="semesterSel" class="filter-select" onchange="this.form.submit()">
                <option value="1" {{ $activeSemester==='1'?'selected':'' }}>1 (Ganjil)</option>
                <option value="2" {{ $activeSemester==='2'?'selected':'' }}>2 (Genap)</option>
            </select>
        </form>
    </div>
    <div class="header-actions">
        <button class="btn btn-print" id="btnCetakTerpilih" disabled>🖨️ CETAK TERPILIH</button>
    </div>
</div>

<div class="search-container">
    <input type="text" id="searchInput" class="search-input" placeholder="Search by Nama or NIS...">
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
                <th width="100" style="text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswaList as $idx => $s)
            <tr class="data-row">
                <td style="text-align:center;"><input type="checkbox" class="custom-checkbox row-check" data-id="{{ $s->id }}"></td>
                <td>{{ $idx + 1 }}</td>
                <td class="col-nama">{{ $s->nama ?: '-' }}</td>
                <td class="col-nis">{{ $s->nis ?: '-' }}</td>
                <td>{{ $s->kelas ?: '-' }}</td>
                <td style="text-align:center;">
                    <span class="print-icon" onclick="cetakIndividu('{{ $s->id }}')" style="{{ !$isGlobalComplete ? 'opacity:0.5;cursor:not-allowed;' : '' }}">🖨️ Cetak</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:2rem;">Tidak ada data siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif
@endsection

@section('scripts')
<script>
    const ACTIVE_YEAR = @json($activeYear);
    const MY_KELAS   = @json($myKelas);
    const JENIS      = @json(strtolower($jenisRapor));
    const SEMESTER   = @json($activeSemester);
    const IS_GLOBAL_COMPLETE = @json($isGlobalComplete);

    // Search filter
    const searchInput = document.getElementById('searchInput');
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

    // Checkbox logic
    const selectAll = document.getElementById('selectAll');
    const rowChecks = document.querySelectorAll('.row-check');
    const btnCetak  = document.getElementById('btnCetakTerpilih');

    function updateBtnState() {
        if (!btnCetak) return;
        if (!IS_GLOBAL_COMPLETE) {
            btnCetak.disabled = true;
            return;
        }
        btnCetak.disabled = !Array.from(rowChecks).some(c => c.checked && c.closest('.data-row').style.display !== 'none');
    }

    if (selectAll) {
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
            if (selectAll) selectAll.checked = allChecked;
            updateBtnState();
        });
    });

    if (btnCetak) {
        btnCetak.addEventListener('click', function() {
            if (!IS_GLOBAL_COMPLETE) {
                alert('Cetak rapor ditangguhkan karena penginputan nilai/absensi kelas Anda belum mencapai progress 100%.');
                return;
            }
            const ids = Array.from(rowChecks)
                .filter(c => c.checked && c.closest('.data-row').style.display !== 'none')
                .map(c => c.dataset.id);
            if (ids.length > 0) {
                window.open(`/walikelas/print-rapor?siswa_ids=${ids.join(',')}&tipe=${JENIS}&tahun=${encodeURIComponent(ACTIVE_YEAR)}&kelas=${encodeURIComponent(MY_KELAS)}&semester=${encodeURIComponent(SEMESTER)}`, '_blank');
            }
        });
    }

    function cetakIndividu(siswaNis) {
        if (!IS_GLOBAL_COMPLETE) {
            alert('Cetak rapor ditangguhkan karena penginputan nilai/absensi kelas Anda belum mencapai progress 100%.');
            return;
        }
        window.open(`/walikelas/print-rapor?siswa_ids=${siswaNis}&tipe=${JENIS}&tahun=${encodeURIComponent(ACTIVE_YEAR)}&kelas=${encodeURIComponent(MY_KELAS)}&semester=${encodeURIComponent(SEMESTER)}`, '_blank');
    }
</script>
@endsection
