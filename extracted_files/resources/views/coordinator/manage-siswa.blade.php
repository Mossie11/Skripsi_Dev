@extends('layouts.app')
@section('page_title','Manage Siswa')
@section('styles')@include('coordinator.partials.manage-styles')@endsection

@section('content')
<div class="container-manage">
    <div class="header">
        <h2><i class='bx bx-group'></i> List Siswa</h2>
        <div class="actions">
            <button class="btn btn-export-header" id="headerExportBtn">Export</button>
            <button class="btn btn-delete-header" id="headerDeleteBtn">Delete</button>
            <button class="btn btn-create" onclick="openModal('createModal')">+ CREATE</button>
        </div>
    </div>

    <div class="filters">
        <form class="search-box" method="GET" action="{{ route('coordinator.manage-siswa') }}">
            <select name="filter_kelas" style="max-width:180px; padding:.75rem; border:2px solid #e0e8f0; border-radius:6px; color:#0C447C; outline:none; background:#fff;" onchange="this.form.submit()">
                <option value="">All Kelas</option>
                @foreach($kelasList as $id => $name)
                <option value="{{ $id }}" {{ $filterKelas == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select name="filter_field" onchange="document.getElementById('searchInput').value=''; this.form.submit();" style="max-width: 150px; padding:.75rem; border:2px solid #e0e8f0; border-radius:6px; color:#0C447C; outline:none; background:#fff;">
                <option value="semua" {{ request('filter_field') === 'semua' ? 'selected' : '' }}>Semua Field</option>
                <option value="nama" {{ request('filter_field') === 'nama' ? 'selected' : '' }}>Nama</option>
                <option value="nis" {{ request('filter_field') === 'nis' ? 'selected' : '' }}>NIS</option>
                <option value="nisn" {{ request('filter_field') === 'nisn' ? 'selected' : '' }}>NISN</option>
            </select>
            <input type="text" name="search" id="searchInput" placeholder="Search..." value="{{ $search }}">
            <button type="submit">🔍</button>
            @if($search || $filterKelas || (request('filter_field') && request('filter_field') !== 'semua'))
                <a href="{{ route('coordinator.manage-siswa') }}" class="btn btn-back" style="padding:.6rem;">Clear</a>
            @endif
        </form>
    </div>

    <form id="mainDeleteForm" method="POST" action="{{ route('coordinator.manage-siswa.action') }}">
        @csrf
        <input type="hidden" name="action" value="delete">

        <div class="pagination">
            <span>{{ $total > 0 ? ($offset+1).'-'.min($offset+count($pageData),$total) : '0' }} / {{ $total }}</span>
            <div class="page-nav">
                @php 
                    $qStr = '';
                    if ($search) $qStr .= '&search='.urlencode($search);
                    if ($filterKelas) $qStr .= '&filter_kelas='.urlencode($filterKelas);
                    if (request('filter_field') && request('filter_field') !== 'semua') $qStr .= '&filter_field='.urlencode(request('filter_field'));
                @endphp
                <a href="?page=1{{ $qStr }}" class="{{ $page<=1?'disabled':'' }}">«</a>
                <a href="?page={{ $page-1 }}{{ $qStr }}" class="{{ $page<=1?'disabled':'' }}">‹</a>
                <span style="padding:.4rem;">{{ $page }} / {{ $totalPages }}</span>
                <a href="?page={{ $page+1 }}{{ $qStr }}" class="{{ $page>=$totalPages?'disabled':'' }}">›</a>
                <a href="?page={{ $totalPages }}{{ $qStr }}" class="{{ $page>=$totalPages?'disabled':'' }}">»</a>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" class="checkbox" id="selectAll"></th>
                        <th>Nama</th><th>NIS</th><th>NISN</th><th>Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageData as $s)
                    <tr>
                        <td class="checkbox-cell">
                            <button type="button" class="row-delete-pill" onclick="deleteSingleRow('{{ $s->id }}')">🗑️</button>
                            <input type="checkbox" name="ids[]" value="{{ $s->id }}" class="checkbox-item checkbox">
                        </td>
                        <td><span class="clickable-name" onclick="showSiswaProfile({{ json_encode($s) }})">{{ $s->nama ?? '-' }}</span></td>
                        <td>{{ $s->nis ?? '-' }}</td>
                        <td>{{ $s->nisn ?? '-' }}</td>
                        <td>{{ $s->kelas ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td class="checkbox-cell">
                            <input type="checkbox" class="checkbox-item checkbox" disabled>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="floating-bar" id="floatingBar">
            <span id="selectedCount">0 selected</span>
            <button type="button" class="bin-icon" id="floatingDeleteBtn">🗑️</button>
            <div style="width:1px;height:24px;background:#dce8f5;"></div>
            <input type="checkbox" style="width:18px;height:18px;" checked disabled>
        </div>
    </form>
</div>

{{-- Create Modal --}}
<div class="modal-overlay" id="createModal">
    <div class="create-modal-content">
        <button class="modal-close" onclick="closeModal('createModal')">×</button>
        <h2>Create New Siswa</h2>
        <form method="POST" action="{{ route('coordinator.manage-siswa.action') }}">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="create-form-grid">
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama" required></div>
                <div class="form-group"><label>Jenis Kelamin</label><select name="jenis_kelamin"><option>Laki-laki</option><option>Perempuan</option></select></div>
                <div class="form-group"><label>NIS</label><input type="text" name="nis"></div>
                <div class="form-group"><label>NISN</label><input type="text" name="nisn"></div>
                <div class="form-group"><label>Kelas</label>
                    <select name="kelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $id => $namaKelas)
                            <option value="{{ $id }}">{{ $namaKelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir"></div>
                <div class="form-group"><label>Agama</label><select name="agama"><option>Islam</option><option>Kristen</option><option>Katolik</option><option>Hindu</option><option>Budha</option><option>Konghucu</option></select></div>
                <div class="form-group"><label>No HP</label><input type="text" name="no_hp"></div>
                <div class="form-group"><label>No HP Orangtua</label><input type="text" name="no_hp_orangtua"></div>
                <div class="form-group full-width"><label>Tempat Lahir</label><textarea name="tempat_lahir" rows="3"></textarea></div>
            </div>
            <button type="submit" class="submit-btn" onclick="return confirm('Create siswa?')">Create Siswa</button>
        </form>
    </div>
</div>

{{-- Profile Modal --}}
<div class="modal-overlay" id="profilePopupModal">
    <div class="profile-modal-content">
        <button class="modal-close" onclick="closeProfile()">×</button>
        <div id="profileViewMode">
            <div class="profile-header">
                <div>Profile | <span id="pNameHeader">-</span></div>
                <button class="btn-edit" onclick="enterEditMode()">✏️ Edit</button>
            </div>
            <div class="profile-pic-placeholder"></div>
            <div class="profile-section-title">Data Siswa</div>
            <table class="profile-data-table">
                <tr><td class="profile-label">Nama Lengkap</td><td class="profile-colon">:</td><td class="profile-value" id="pNama">-</td></tr>
                <tr><td class="profile-label">NIS / NISN</td><td class="profile-colon">:</td><td class="profile-value" id="pNisNisn">-</td></tr>
                <tr><td class="profile-label">Kelas</td><td class="profile-colon">:</td><td class="profile-value" id="pKelas">-</td></tr>
                <tr><td class="profile-label">Jenis Kelamin</td><td class="profile-colon">:</td><td class="profile-value" id="pJk">-</td></tr>
                <tr><td class="profile-label">Tanggal Lahir</td><td class="profile-colon">:</td><td class="profile-value" id="pTglLahir">-</td></tr>
                <tr><td class="profile-label">Agama</td><td class="profile-colon">:</td><td class="profile-value" id="pAgama">-</td></tr>
                <tr><td class="profile-label">No HP</td><td class="profile-colon">:</td><td class="profile-value" id="pNoHp">-</td></tr>
                <tr><td class="profile-label">No HP Orangtua</td><td class="profile-colon">:</td><td class="profile-value" id="pNoHpOrtu">-</td></tr>
                <tr><td class="profile-label">Tempat Lahir</td><td class="profile-colon">:</td><td class="profile-value" id="pTempat Lahir">-</td></tr>
            </table>
        </div>
        <div id="profileEditMode" style="display:none;">
            <div class="profile-header"><div>Edit | <span id="pEditNameHeader">-</span></div></div>
            <form method="POST" action="{{ route('coordinator.manage-siswa.action') }}" id="editForm">
                @csrf
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="edit_id" id="editId">
                <table class="profile-data-table">
                    <tr><td class="profile-label">Nama</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nama" id="editNama" required></td></tr>
                    <tr><td class="profile-label">NIS</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nis" id="editNis"></td></tr>
                    <tr><td class="profile-label">NISN</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nisn" id="editNisn"></td></tr>
                    <tr><td class="profile-label">Kelas</td><td class="profile-colon">:</td><td>
                        <select class="edit-select" name="kelas" id="editKelas">
                            <option value="">--</option>
                            @foreach($kelasList as $id => $namaKelas)
                                <option value="{{ $id }}">{{ $namaKelas }}</option>
                            @endforeach
                        </select>
                    </td></tr>
                    <tr><td class="profile-label">Jenis Kelamin</td><td class="profile-colon">:</td><td><select class="edit-select" name="jenis_kelamin" id="editJk"><option>Laki-laki</option><option>Perempuan</option></select></td></tr>
                    <tr><td class="profile-label">Tanggal Lahir</td><td class="profile-colon">:</td><td><input class="edit-input" type="date" name="tanggal_lahir" id="editTglLahir"></td></tr>
                    <tr><td class="profile-label">Agama</td><td class="profile-colon">:</td><td><select class="edit-select" name="agama" id="editAgama"><option>Islam</option><option>Kristen</option><option>Katolik</option><option>Hindu</option><option>Budha</option><option>Konghucu</option></select></td></tr>
                    <tr><td class="profile-label">No HP</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="no_hp" id="editNoHp"></td></tr>
                    <tr><td class="profile-label">No HP Orangtua</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="no_hp_orangtua" id="editNoHpOrtu"></td></tr>
                    <tr><td class="profile-label">Tempat Lahir</td><td class="profile-colon">:</td><td><textarea class="edit-input" name="tempat_lahir" id="editTempat Lahir" rows="3"></textarea></td></tr>
                </table>
                <div class="profile-actions">
                    <button type="submit" class="btn-save" onclick="return confirm('Save changes?')">💾 Save</button>
                    <button type="button" class="btn-cancel" onclick="cancelEditMode()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('coordinator.partials.manage-scripts')
<script>
    initManageTable('mainDeleteForm');

    function formatDateString(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const d = parseInt(parts[2], 10);
            const m = months[parseInt(parts[1], 10) - 1];
            const y = parts[0];
            return d + ' ' + m + ' ' + y;
        }
        return dateStr;
    }

    function showSiswaProfile(u) {
        showProfile(u, [
            {id:'pNameHeader', val:u.nama}, {id:'pNama', val:u.nama},
            {id:'pNisNisn', val:(u.nis||'-')+' / '+(u.nisn||'-')},
            {id:'pKelas', val:u.kelas}, {id:'pJk', val:u.jenis_kelamin},
            {id:'pTglLahir', val:formatDateString(u.tanggal_lahir)}, {id:'pAgama', val:u.agama},
            {id:'pNoHp', val:u.no_hp},
            {id:'pNoHpOrtu', val:u.no_hp_orangtua}, {id:'pTempat Lahir', val:u.tempat_lahir},
        ]);
    }

    window.enterEditMode = function() {
        const u = getCurrentUser(); if (!u) return;
        document.getElementById('pEditNameHeader').textContent = u.nama || '';
        document.getElementById('editId').value       = u.id;
        document.getElementById('editNama').value     = u.nama || '';
        document.getElementById('editNis').value      = u.nis  || '';
        document.getElementById('editNisn').value     = u.nisn || '';
        document.getElementById('editKelas').value    = u.kelas_id || '';
        document.getElementById('editJk').value       = u.jenis_kelamin || 'Laki-laki';
        document.getElementById('editTglLahir').value = u.tanggal_lahir || '';
        document.getElementById('editAgama').value    = u.agama || 'Islam';
        document.getElementById('editNoHp').value     = u.no_hp || '';
        document.getElementById('editNoHpOrtu').value = u.no_hp_orangtua || '';
        document.getElementById('editTempat Lahir').value   = u.tempat_lahir || '';
        document.getElementById('profileViewMode').style.display = 'none';
        document.getElementById('profileEditMode').style.display = 'block';
    };
</script>
@endsection
