@extends('layouts.app')
@section('page_title', 'Data Kelas')
@section('styles')
<style>
    select option { background:#fff !important; color:#1a2a3a !important; }
    .header { display:flex; justify-content:space-between; align-items:center; padding:1.5rem 0; background:#E6F1FB; }
    .header h1 { font-size:1.5rem; font-weight:600; margin:0; color:#0C447C; }
    .header-actions { display:flex; gap:10px; }
    .btn { padding:.5rem 1rem; border:none; border-radius:4px; font-weight:bold; font-size:.85rem; cursor:pointer; text-transform:uppercase; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
    .btn-create { background:#0C447C; color:#fff; }
    .btn-create:hover { background:#1aa179; }
    .search-container { display:flex; margin-bottom:20px; background:#E6F1FB; border-radius:4px; overflow:hidden; border:1px solid #dce8f5; }
    .search-input { flex:1; background:#E6F1FB; border:none; padding:12px 15px; color:#378ADD; font-size:.9rem; outline:none; }
    .search-input::placeholder { color:#6a9bc0; }
    .search-btn { background:#378ADD; border:none; width:45px; display:flex; align-items:center; justify-content:center; cursor:pointer; }
    .search-btn:hover { background:#0056b3; }
    .search-icon { width:16px; height:16px; fill:#fff; }
    .table-container { background:#fff; border-radius:4px; overflow-x:auto; border:1px solid #dce8f5; }
    .data-table { width:100%; border-collapse:collapse; font-size:.85rem; color:#1a3a5a; min-width:1100px; }
    .data-table th, .data-table td { text-align:left; padding:12px 15px; border-bottom:1px solid #dce8f5; color:#1a2a3a; }
    .data-table th { font-weight:bold; color:#fff !important; background:#0C447C !important; }
    .data-table tbody tr:hover { background:#e0edf8; }
    .custom-checkbox { appearance:none; -webkit-appearance:none; width:18px; height:18px; background:#fff; border:2px solid #7baada; border-radius:4px; cursor:pointer; display:inline-block; vertical-align:middle; transition:all .2s; }
    .custom-checkbox:checked { background:#378ADD; border:1px solid #0056b3; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E"); background-size:80%; background-position:center; background-repeat:no-repeat; }
    .pm-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:9999; align-items:center; justify-content:center; }
    .pm-overlay.active { display:flex; }
    .pm-box { background:#fff; border:1px solid #dce8f5; border-radius:12px; padding:2rem; width:90%; max-width:480px; position:relative; color:#1a3a5a; box-shadow:0 20px 60px rgba(0,0,0,.7); max-height:90vh; overflow-y:auto; }
    .pm-close { position:absolute; top:1rem; right:1rem; background:none; border:none; color:#378ADD; font-size:1.5rem; cursor:pointer; line-height:1; }
    .pm-avatar { width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg,#378ADD 0%,#6830CD 100%); display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:700; color:#fff; margin:0 auto 1.2rem; }
    .pm-title { text-align:center; font-size:1.15rem; font-weight:700; color:#0C447C; margin-bottom:.3rem; }
    .pm-sub { text-align:center; font-size:.8rem; color:#6a9bc0; margin-bottom:1.5rem; }
    .pm-grid { display:grid; grid-template-columns:1fr 1fr; gap:.7rem 1.2rem; }
    .pm-field { background:#E6F1FB; border-radius:8px; padding:.65rem 1rem; }
    .pm-label { font-size:.72rem; color:#378ADD; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.2rem; font-weight:bold; }
    .pm-value { font-size:.9rem; color:#1a3a5a; font-weight:500; }
    .pm-field.full { grid-column:span 2; }
    .clickable-name { color:#0C447C; cursor:pointer; text-decoration:none; font-weight:500; }
    .clickable-name:hover { text-decoration:underline; }
    .pm-edit-row { display:flex; flex-direction:column; gap:.3rem; }
    .pm-edit-label { font-size:.72rem; color:#0C447C; text-transform:uppercase; letter-spacing:.05em; font-weight:bold; }
    .pm-edit-input, .pm-edit-select { background:#E6F1FB; border:1px solid #c8d8ec; border-radius:6px; color:#0C447C; padding:.5rem .8rem; font-size:.9rem; width:100%; outline:none; font-family:inherit; }
    .pm-edit-input:focus, .pm-edit-select:focus { border-color:#378ADD; }
    .pm-actions { display:flex; gap:.7rem; margin-top:1.2rem; }
    .pm-btn-save { flex:1; background:#0C447C; color:#fff; border:none; border-radius:6px; padding:.65rem; font-weight:700; cursor:pointer; font-size:.9rem; }
    .pm-btn-save:hover { background:#088c66; }
    .pm-btn-cancel { background:#E6F1FB; color:#1a3a5a; border:none; border-radius:6px; padding:.65rem 1rem; font-weight:600; cursor:pointer; font-size:.9rem; }
    .pm-edit-btn { position:absolute; top:1rem; left:1rem; background:#0C447C; color:#fff; border:none; border-radius:6px; padding:.35rem .85rem; font-size:.8rem; font-weight:600; cursor:pointer; }
    .pm-edit-btn:hover { background:#005a9e; }
    .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:.75rem 1rem; border-radius:6px; margin-bottom:1rem; }
</style>
@endsection

@section('content')
@if(session('success'))
<div class="alert-success">✅ {{ session('success') }}</div>
@endif

<div class="header">
    <h1>List Siswa – Kelas {{ $myKelas }}</h1>
    <div class="header-actions">
        <button class="btn btn-create" onclick="document.getElementById('createModal').classList.add('active')">+ CREATE</button>
    </div>
</div>

<div class="search-container">
    <input type="text" id="searchInput" class="search-input" placeholder="Search by Nama, NIS, atau NISN...">
    <button class="search-btn"><svg class="search-icon" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg></button>
</div>

<div class="table-container">
    <table class="data-table" id="siswaTable">
        <thead>
            <tr>
                <th width="40" style="text-align:center;"><input type="checkbox" class="custom-checkbox" id="selectAll"></th>
                <th>Nama</th><th>NIS</th><th>NISN</th><th>Kelas</th><th>Jenis Kelamin</th><th>No HP</th><th>No HP Orangtua</th><th>Tanggal Lahir</th><th>Tempat Lahir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswaList as $s)
            <tr class="data-row">
                <td style="text-align:center;"><input type="checkbox" class="custom-checkbox row-check"></td>
                <td class="col-nama"><span class="clickable-name" onclick='showProfile(@json($s))'>{{ $s->nama ?: '-' }}</span></td>
                <td class="col-nis">{{ $s->nis ?: '-' }}</td>
                <td class="col-nisn">{{ $s->nisn ?: '-' }}</td>
                <td>{{ $s->kelas ?: '-' }}</td>
                <td>{{ $s->jenis_kelamin ?: '-' }}</td>
                <td>{{ $s->no_hp ?: '-' }}</td>
                <td>{{ $s->no_hp_orangtua ?: '-' }}</td>
                <td>{{ $s->tanggal_lahir ?: '-' }}</td>
                <td>{{ $s->tempat_lahir ?: '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center;padding:2rem;">Tidak ada data siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Create Modal --}}
<div class="pm-overlay" id="createModal">
    <div class="pm-box" style="max-width:600px;">
        <button class="pm-close" type="button" onclick="document.getElementById('createModal').classList.remove('active')">×</button>
        <div class="pm-title" style="text-align:left;font-size:1.5rem;border-bottom:1px solid #c8d8ec;padding-bottom:10px;margin-bottom:15px;">Create Siswa Baru</div>
        <form method="POST" action="{{ route('walikelas.kelas.create') }}">
            @csrf
            <div class="pm-edit-label" style="margin-bottom:4px;">Data Siswa (Kelas: {{ $myKelas }})</div>
            <div class="pm-grid" style="margin-bottom:15px;">
                <div class="pm-edit-row" style="grid-column:span 2;"><label class="pm-edit-label">Nama Lengkap *</label><input class="pm-edit-input" type="text" name="nama" required></div>
                <div class="pm-edit-row"><label class="pm-edit-label">NIS</label><input class="pm-edit-input" type="text" name="nis"></div>
                <div class="pm-edit-row"><label class="pm-edit-label">NISN</label><input class="pm-edit-input" type="text" name="nisn"></div>
                <div class="pm-edit-row"><label class="pm-edit-label">Jenis Kelamin</label><select class="pm-edit-select" name="jenis_kelamin"><option>Laki-laki</option><option>Perempuan</option></select></div>
                <div class="pm-edit-row"><label class="pm-edit-label">Tanggal Lahir</label><input class="pm-edit-input" type="date" name="tanggal_lahir"></div>
                <div class="pm-edit-row"><label class="pm-edit-label">No HP</label><input class="pm-edit-input" type="text" name="no_hp"></div>
                <div class="pm-edit-row"><label class="pm-edit-label">No HP Orangtua</label><input class="pm-edit-input" type="text" name="no_hp_orangtua"></div>
                <div class="pm-edit-row" style="grid-column:span 2;"><label class="pm-edit-label">Tempat Lahir</label><textarea class="pm-edit-input" name="tempat_lahir" rows="2"></textarea></div>
            </div>
            <div class="pm-actions">
                <button type="submit" class="pm-btn-save">✚ Create Siswa</button>
                <button type="button" class="pm-btn-cancel" onclick="document.getElementById('createModal').classList.remove('active')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Profile / Edit Modal --}}
<div class="pm-overlay" id="profileModal">
    <div class="pm-box">
        <button class="pm-close" onclick="closeProfile()">×</button>
        <div id="pmViewMode">
            <button class="pm-edit-btn" onclick="enterEdit()">✏️ Edit</button>
            <div class="pm-avatar" id="pmAvatar">?</div>
            <div class="pm-title" id="pmName">—</div>
            <div class="pm-sub" id="pmKelasGender">—</div>
            <div class="pm-grid">
                <div class="pm-field"><div class="pm-label">NIS</div><div class="pm-value" id="pmNis">—</div></div>
                <div class="pm-field"><div class="pm-label">NISN</div><div class="pm-value" id="pmNisn">—</div></div>
                <div class="pm-field"><div class="pm-label">Tanggal Lahir</div><div class="pm-value" id="pmTgl">—</div></div>
                <div class="pm-field"><div class="pm-label">No HP Siswa</div><div class="pm-value" id="pmHp">—</div></div>
                <div class="pm-field full"><div class="pm-label">No HP Orangtua</div><div class="pm-value" id="pmHpOrtu">—</div></div>
                <div class="pm-field full"><div class="pm-label">Tempat Lahir</div><div class="pm-value" id="pmTempat Lahir">—</div></div>
            </div>
        </div>
        <div id="pmEditMode" style="display:none;">
            <div class="pm-avatar" id="pmEditAvatar">?</div>
            <div class="pm-title" style="margin-bottom:1.2rem;">Edit Profil Siswa</div>
            <form method="POST" id="editSiswaForm">
                @csrf
                <div class="pm-grid">
                    <div class="pm-edit-row full" style="grid-column:span 2;"><label class="pm-edit-label">Nama Lengkap *</label><input class="pm-edit-input" type="text" name="nama" id="editNama" required></div>
                    <div class="pm-edit-row"><label class="pm-edit-label">NIS</label><input class="pm-edit-input" type="text" name="nis" id="editNis"></div>
                    <div class="pm-edit-row"><label class="pm-edit-label">NISN</label><input class="pm-edit-input" type="text" name="nisn" id="editNisn"></div>
                    <div class="pm-edit-row"><label class="pm-edit-label">Kelas</label><input class="pm-edit-input" type="text" name="kelas" id="editKelas"></div>
                    <div class="pm-edit-row"><label class="pm-edit-label">Jenis Kelamin</label><select class="pm-edit-select" name="jenis_kelamin" id="editJk"><option>Laki-laki</option><option>Perempuan</option></select></div>
                    <div class="pm-edit-row"><label class="pm-edit-label">Tanggal Lahir</label><input class="pm-edit-input" type="date" name="tanggal_lahir" id="editTgl"></div>
                    <div class="pm-edit-row"><label class="pm-edit-label">No HP Siswa</label><input class="pm-edit-input" type="text" name="no_hp" id="editHp"></div>
                    <div class="pm-edit-row" style="grid-column:span 2;"><label class="pm-edit-label">No HP Orangtua</label><input class="pm-edit-input" type="text" name="no_hp_orangtua" id="editHpOrtu"></div>
                    <div class="pm-edit-row" style="grid-column:span 2;"><label class="pm-edit-label">Tempat Lahir</label><textarea class="pm-edit-input" name="tempat_lahir" id="editTempat Lahir" rows="2"></textarea></div>
                </div>
                <div class="pm-actions">
                    <button type="button" class="pm-btn-cancel" onclick="cancelEdit()">← Kembali</button>
                    <button type="submit" class="pm-btn-save">💾 Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.addEventListener('click', e => {
        const cm = document.getElementById('createModal');
        if (cm && e.target === cm) cm.classList.remove('active');
        const pm = document.getElementById('profileModal');
        if (pm && e.target === pm) { pm.classList.remove('active'); cancelEdit(); }
    });

    let currentSiswa = null;
    function showProfile(s) {
        currentSiswa = s;
        document.getElementById('pmAvatar').textContent = (s.nama || '?').charAt(0).toUpperCase();
        document.getElementById('pmAvatar').style.backgroundImage = 'linear-gradient(135deg,#378ADD 0%,#6830CD 100%)';
        document.getElementById('pmName').textContent = s.nama || '-';
        document.getElementById('pmKelasGender').textContent = `${s.kelas || '-'} · ${s.jenis_kelamin || '-'}`;
        document.getElementById('pmNis').textContent = s.nis || '-';
        document.getElementById('pmNisn').textContent = s.nisn || '-';
        document.getElementById('pmTgl').textContent = s.tanggal_lahir || '-';
        document.getElementById('pmHp').textContent = s.no_hp || '-';
        document.getElementById('pmHpOrtu').textContent = s.no_hp_orangtua || '-';
        document.getElementById('pmTempat Lahir').textContent = s.tempat_lahir || '-';
        document.getElementById('pmViewMode').style.display = 'block';
        document.getElementById('pmEditMode').style.display = 'none';
        document.getElementById('profileModal').classList.add('active');
    }
    function enterEdit() {
        if (!currentSiswa) return;
        const s = currentSiswa;
        document.getElementById('pmEditAvatar').textContent = (s.nama || '?').charAt(0).toUpperCase();
        document.getElementById('editNama').value = s.nama || '';
        document.getElementById('editNis').value = s.nis || '';
        document.getElementById('editNisn').value = s.nisn || '';
        document.getElementById('editKelas').value = s.kelas || '';
        document.getElementById('editJk').value = s.jenis_kelamin || 'Laki-laki';
        document.getElementById('editTgl').value = s.tanggal_lahir || '';
        document.getElementById('editHp').value = s.no_hp || '';
        document.getElementById('editHpOrtu').value = s.no_hp_orangtua || '';
        document.getElementById('editTempat Lahir').value = s.tempat_lahir || '';
        document.getElementById('editSiswaForm').action = `/walikelas/kelas/${s.id}`;
        document.getElementById('pmViewMode').style.display = 'none';
        document.getElementById('pmEditMode').style.display = 'block';
    }
    function cancelEdit() {
        document.getElementById('pmViewMode').style.display = 'block';
        document.getElementById('pmEditMode').style.display = 'none';
    }
    function closeProfile() { document.getElementById('profileModal').classList.remove('active'); }

    // Search
    document.getElementById('searchInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.data-row').forEach(row => {
            const nama = row.querySelector('.col-nama').textContent.toLowerCase();
            const nis  = row.querySelector('.col-nis').textContent.toLowerCase();
            const nisn = row.querySelector('.col-nisn').textContent.toLowerCase();
            row.style.display = (nama+nis+nisn).includes(q) ? '' : 'none';
        });
    });
    // Select All
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-check').forEach(c => {
            if (c.closest('tr').style.display !== 'none') c.checked = this.checked;
        });
    });
</script>
@endsection
