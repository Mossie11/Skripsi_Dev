@extends('layouts.app')
@section('page_title','Manage Guru')
@section('styles')@include('coordinator.partials.manage-styles')@endsection

@section('content')
<div class="container-manage">
    <div class="header">
        <h2><i class='bx bx-user-voice'></i> List User Guru</h2>
        <div class="actions">
            <button class="btn btn-export-header" id="headerExportBtn">Export</button>
            <button class="btn btn-delete-header" id="headerDeleteBtn">Delete</button>
            <button class="btn btn-create" onclick="openModal('createModal')">+ CREATE</button>
        </div>
    </div>

    <div class="filters">
        <form class="search-box" method="GET" action="{{ route('coordinator.manage-guru') }}">
            <select name="filter_field" onchange="document.getElementById('searchInput').value=''; this.form.submit();" style="max-width: 150px; padding:.75rem; border:2px solid #e0e8f0; border-radius:6px; color:#0C447C; outline:none; background:#fff;">
                <option value="semua" {{ request('filter_field') === 'semua' ? 'selected' : '' }}>Semua Field</option>
                <option value="nama" {{ request('filter_field') === 'nama' ? 'selected' : '' }}>Nama</option>
                <option value="username" {{ request('filter_field') === 'username' ? 'selected' : '' }}>Username</option>
                <option value="nip" {{ request('filter_field') === 'nip' ? 'selected' : '' }}>NIP</option>
                <option value="mapel" {{ request('filter_field') === 'mapel' ? 'selected' : '' }}>Mapel Diajar</option>
            </select>
            <input type="text" name="search" id="searchInput" placeholder="Search..." value="{{ request('search') }}">
            <button type="submit">🔍</button>
            @if(request('search') || (request('filter_field') && request('filter_field') !== 'semua'))
                <a href="{{ route('coordinator.manage-guru') }}" class="btn btn-back" style="padding:.6rem;">Clear</a>
            @endif
        </form>
    </div>

    <form id="mainDeleteForm" method="POST" action="{{ route('coordinator.manage-guru.action') }}">
        @csrf
        <input type="hidden" name="action" value="delete">

        <div class="pagination">
            <span>{{ $total > 0 ? ($offset+1).'-'.min($offset+count($pageData),$total) : '0' }} / {{ $total }}</span>
            <div class="page-nav">
                @php
                    $qs = '';
                    if (request('search')) $qs .= '&search='.urlencode(request('search'));
                    if (request('filter_field') && request('filter_field') !== 'semua') $qs .= '&filter_field='.urlencode(request('filter_field'));
                @endphp
                <a href="?page=1{{ $qs }}" class="{{ $page<=1 ? 'disabled' : '' }}">«</a>
                <a href="?page={{ $page-1 }}{{ $qs }}" class="{{ $page<=1 ? 'disabled' : '' }}">‹</a>
                <span style="padding:.4rem;">{{ $page }} / {{ $totalPages }}</span>
                <a href="?page={{ $page+1 }}{{ $qs }}" class="{{ $page>=$totalPages ? 'disabled' : '' }}">›</a>
                <a href="?page={{ $totalPages }}{{ $qs }}" class="{{ $page>=$totalPages ? 'disabled' : '' }}">»</a>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" class="checkbox" id="selectAll"></th>
                        <th>Username</th><th>Nama</th><th>NUPTK</th><th>Mapel Diajar</th><th>Kelas Diajar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageData as $g)
                    <tr>
                        <td class="checkbox-cell">
                            <button type="button" class="row-delete-pill" onclick="deleteSingleRow({{ $g->id }})">🗑️</button>
                            <input type="checkbox" name="ids[]" value="{{ $g->id }}" class="checkbox-item checkbox">
                        </td>
                        <td>{{ $g->username ?? '-' }}</td>
                        <td><span class="clickable-name" onclick="showGuruProfile({{ json_encode($g) }})">{{ $g->nama ?? '-' }}</span></td>
                        <td>{{ $g->nuptk ?? '-' }}</td>
                        <td>{{ $g->mapel ?? '-' }}</td>
                        <td style="max-width:220px;">
                            <div class="kelas-badges">
                                @if($g->kelas_diajar && $g->kelas_diajar !== '-')
                                    @foreach(explode(',', $g->kelas_diajar) as $kls)
                                        @php
                                            $kelasName = trim($kls);
                                            $badgeBg = '#0C447C';
                                            if (str_starts_with($kelasName, 'XII')) {
                                                $badgeBg = '#e74c3c';
                                            } elseif (str_starts_with($kelasName, 'XI')) {
                                                $badgeBg = '#f1c40f';
                                            } elseif (str_starts_with($kelasName, 'X')) {
                                                $badgeBg = '#27ae60';
                                            }
                                        @endphp
                                        <span class="kelas-badge" style="background: {{ $badgeBg }}; color: #fff; border: 1px solid {{ $badgeBg }};">{{ $kelasName }}</span>
                                    @endforeach
                                @else
                                    <span style="color:#aaa;">-</span>
                                @endif
                            </div>
                        </td>
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
        <h2>Create New Guru</h2>
        <form method="POST" action="{{ route('coordinator.manage-guru.action') }}">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="create-form-grid">
                <div class="form-group full-width"><label>Login Credentials</label><hr style="border:0;height:1px;background:#dce8f5;"></div>
                <div class="form-group"><label>Username *</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password *</label><input type="password" name="password" required></div>
                <div class="form-group full-width" style="margin-top:10px;"><label>Guru Details</label><hr style="border:0;height:1px;background:#dce8f5;"></div>
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama" required></div>
                <div class="form-group"><label>NUPTK</label><input type="text" name="nuptk"></div>
                <div class="form-group"><label>NIP</label><input type="text" name="nip"></div>
                <div class="form-group"><label>Jenis Kelamin</label><select name="jenis_kelamin"><option>Laki-laki</option><option>Perempuan</option></select></div>
                <div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir"></div>
                <div class="form-group"><label>Tempat Lahir</label><input type="text" name="tempat_lahir"></div>
                <div class="form-group"><label>No HP</label><input type="text" name="no_hp"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                <div class="form-group full-width"><label>Alamat</label><textarea name="alamat" rows="3"></textarea></div>
            </div>
            <button type="submit" class="submit-btn" onclick="return confirm('Create guru?')">Create Guru</button>
        </form>
    </div>
</div>

<form id="forceResetPasswordForm" method="POST" action="{{ route('coordinator.manage-guru.action') }}" style="display:none;">
    @csrf
    <input type="hidden" name="action" value="force_reset_password_flag">
    <input type="hidden" name="edit_id" id="forceResetEditId">
</form>

{{-- Profile Modal --}}
<div class="modal-overlay" id="profilePopupModal">
    <div class="profile-modal-content">
        <button class="modal-close" onclick="closeProfile()">×</button>
        <div id="profileViewMode">
            <div class="profile-header">
                <div>Profile | <span id="pNameHeader">-</span></div>
                <div style="display:flex; gap:10px;">
                    <button class="btn-edit" style="background:#dc3545; color:#fff; border-color:#dc3545;" onclick="triggerForceResetPassword()">🔑 Reset PW</button>
                    <button class="btn-edit" onclick="enterEditMode()">✏️ Edit</button>
                </div>
            </div>
            <div class="profile-pic-placeholder"></div>
            <div class="profile-section-title">Data Guru</div>
            <table class="profile-data-table">
                <tr><td class="profile-label">Nama Lengkap</td><td class="profile-colon">:</td><td class="profile-value" id="pNama">-</td></tr>
                <tr><td class="profile-label">NUPTK</td><td class="profile-colon">:</td><td class="profile-value" id="pNuptk">-</td></tr>
                <tr><td class="profile-label">NIP</td><td class="profile-colon">:</td><td class="profile-value" id="pNip">-</td></tr>
                <tr><td class="profile-label">Mapel Diajar</td><td class="profile-colon">:</td><td class="profile-value" id="pMapel">-</td></tr>
                <tr><td class="profile-label">Kelas Diajar</td><td class="profile-colon">:</td><td class="profile-value" id="pKelas">-</td></tr>
                <tr><td class="profile-label">Jenis Kelamin</td><td class="profile-colon">:</td><td class="profile-value" id="pJk">-</td></tr>
                <tr><td class="profile-label">Tempat Lahir</td><td class="profile-colon">:</td><td class="profile-value" id="pTempatLahir">-</td></tr>
                <tr><td class="profile-label">Tanggal Lahir</td><td class="profile-colon">:</td><td class="profile-value" id="pTglLahir">-</td></tr>
                <tr><td class="profile-label">No HP</td><td class="profile-colon">:</td><td class="profile-value" id="pNoHp">-</td></tr>
                <tr><td class="profile-label">Email</td><td class="profile-colon">:</td><td class="profile-value" id="pEmail">-</td></tr>
                <tr><td class="profile-label">Alamat</td><td class="profile-colon">:</td><td class="profile-value" id="pAlamat">-</td></tr>
                <tr><td class="profile-label">Username</td><td class="profile-colon">:</td><td class="profile-value" id="pUsername">-</td></tr>
                <tr><td class="profile-label">Wali Kelas</td><td class="profile-colon">:</td><td class="profile-value" id="pWaliKelas">-</td></tr>
            </table>
        </div>
        <div id="profileEditMode" style="display:none;">
            <div class="profile-header">
                <div>Edit | <span id="pEditNameHeader">-</span></div>
            </div>
            <form method="POST" action="{{ route('coordinator.manage-guru.action') }}" id="editForm">
                @csrf
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="edit_id" id="editId">
                <table class="profile-data-table">
                    <tr><td class="profile-label">Nama Lengkap</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nama" id="editNama" required></td></tr>
                    <tr><td class="profile-label">NUPTK</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nuptk" id="editNuptk"></td></tr>
                    <tr><td class="profile-label">NIP</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nip" id="editNip"></td></tr>
                    <tr><td class="profile-label">Mapel</td><td class="profile-colon">:</td><td><span id="editMapelReadOnly" style="font-weight:600;color:#0C447C;">-</span></td></tr>
                    <tr><td class="profile-label">Kelas Diajar</td><td class="profile-colon">:</td><td><span id="editKelasReadOnly" style="font-weight:600;color:#0C447C;">-</span></td></tr>
                    <tr><td class="profile-label">Wali Kelas</td><td class="profile-colon">:</td><td><span id="editWaliKelasReadOnly" style="font-weight:600;color:#0C447C;">-</span></td></tr>
                    <tr><td class="profile-label">Jenis Kelamin</td><td class="profile-colon">:</td><td><select class="edit-select" name="jenis_kelamin" id="editJk"><option>Laki-laki</option><option>Perempuan</option></select></td></tr>
                    <tr><td class="profile-label">Tempat Lahir</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="tempat_lahir" id="editTempatLahir"></td></tr>
                    <tr><td class="profile-label">Tanggal Lahir</td><td class="profile-colon">:</td><td><input class="edit-input" type="date" name="tanggal_lahir" id="editTglLahir"></td></tr>
                    <tr><td class="profile-label">No HP</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="no_hp" id="editNoHp"></td></tr>
                    <tr><td class="profile-label">Email</td><td class="profile-colon">:</td><td><input class="edit-input" type="email" name="email" id="editEmail"></td></tr>
                    <tr><td class="profile-label">Alamat</td><td class="profile-colon">:</td><td><textarea class="edit-input" name="alamat" id="editAlamat" rows="3"></textarea></td></tr>
                    <tr><td class="profile-label">Username</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="username" id="editUsername"></td></tr>
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

    window.triggerForceResetPassword = function() {
        const u = getCurrentUser();
        if (!u) return;
        if (confirm('Apakah Anda yakin ingin meminta reset password untuk ' + u.nama + '? User akan langsung diminta memasukkan password baru saat melakukan login berikutnya.')) {
            document.getElementById('forceResetEditId').value = u.id;
            document.getElementById('forceResetPasswordForm').submit();
        }
    };

    function showGuruProfile(u) {
        showProfile(u, [
            {id:'pNameHeader', val:u.nama}, {id:'pNama', val:u.nama}, {id:'pNuptk', val:u.nuptk}, {id:'pNip', val:u.nip},
            {id:'pMapel', val:u.mapel}, {id:'pKelas', val:u.kelas_diajar},
            {id:'pJk', val:u.jenis_kelamin},
            {id:'pTempatLahir', val:u.tempat_lahir},
            {id:'pTglLahir', val:u.tanggal_lahir}, {id:'pNoHp', val:u.no_hp},
            {id:'pEmail', val:u.email},
            {id:'pAlamat', val:u.alamat}, {id:'pUsername', val:u.username},
            {id:'pWaliKelas', val: u.is_walikelas ? ('✅ Ya — ' + (u.kelas_wali_nama || '-')) : '—'},
        ]);
    }

    const _origEnter = window.enterEditMode;
    window.enterEditMode = function() {
        const u = getCurrentUser();
        if (!u) return;
        document.getElementById('pEditNameHeader').textContent = u.nama || '';
        document.getElementById('editId').value   = u.id;

        document.getElementById('editNama').value  = u.nama || '';
        document.getElementById('editNuptk').value = u.nuptk || '';
        document.getElementById('editNip').value   = u.nip  || '';
        
        document.getElementById('editMapelReadOnly').textContent = u.mapel || '-';
        document.getElementById('editKelasReadOnly').textContent = u.kelas_diajar || '-';
        document.getElementById('editWaliKelasReadOnly').textContent = u.is_walikelas ? ('Ya (' + (u.kelas_wali_nama || '-') + ')') : '—';
        
        document.getElementById('editJk').value    = u.jenis_kelamin || 'Laki-laki';
        document.getElementById('editTempatLahir').value = u.tempat_lahir || '';
        document.getElementById('editTglLahir').value = u.tanggal_lahir || '';
        document.getElementById('editNoHp').value  = u.no_hp  || '';
        document.getElementById('editEmail').value = u.email || '';
        document.getElementById('editAlamat').value= u.alamat || '';
        document.getElementById('editUsername').value= u.username || '';
        document.getElementById('profileViewMode').style.display = 'none';
        document.getElementById('profileEditMode').style.display = 'block';
    };
    
    $(document).ready(function() {
        // No select2 initialization needed for read-only fields
    });
</script>
@endsection
