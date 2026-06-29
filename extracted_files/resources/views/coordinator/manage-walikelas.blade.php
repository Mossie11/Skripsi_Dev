@extends('layouts.app')
@section('page_title','Manage Wali Kelas')
@section('styles')@include('coordinator.partials.manage-styles')@endsection

@section('content')
<div class="container-manage">
    <div class="header">
        <h2><i class='bx bx-user-check'></i> List User Wali Kelas</h2>
        <div class="actions">
            <button class="btn btn-export-header" id="headerExportBtn">Export</button>
            <button class="btn btn-delete-header" id="headerDeleteBtn">Delete</button>
            <button class="btn btn-create" onclick="openModal('createModal')">+ CREATE</button>
        </div>
    </div>

    <div class="filters">
        <form class="search-box" method="GET" action="{{ route('coordinator.manage-walikelas') }}">
            <select name="filter_field" onchange="document.getElementById('searchInput').value=''; this.form.submit();" style="max-width: 150px; padding:.75rem; border:2px solid #e0e8f0; border-radius:6px; color:#0C447C; outline:none; background:#fff;">
                <option value="semua" {{ request('filter_field') === 'semua' ? 'selected' : '' }}>Semua Field</option>
                <option value="nama" {{ request('filter_field') === 'nama' ? 'selected' : '' }}>Nama</option>
                <option value="username" {{ request('filter_field') === 'username' ? 'selected' : '' }}>Username</option>
                <option value="nip" {{ request('filter_field') === 'nip' ? 'selected' : '' }}>NIP</option>
                <option value="kelas" {{ request('filter_field') === 'kelas' ? 'selected' : '' }}>Kelas Wali</option>
            </select>
            <input type="text" name="search" id="searchInput" placeholder="Search..." value="{{ request('search') }}">
            <button type="submit">🔍</button>
            @if(request('search') || (request('filter_field') && request('filter_field') !== 'semua'))
                <a href="{{ route('coordinator.manage-walikelas') }}" class="btn btn-back" style="padding:.6rem;">Clear</a>
            @endif
        </form>
    </div>

    <form id="mainDeleteForm" method="POST" action="{{ route('coordinator.manage-walikelas.action') }}">
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
                <a href="?page=1{{ $qs }}" class="{{ $page<=1?'disabled':'' }}">«</a>
                <a href="?page={{ $page-1 }}{{ $qs }}" class="{{ $page<=1?'disabled':'' }}">‹</a>
                <span style="padding:.4rem;">{{ $page }} / {{ $totalPages }}</span>
                <a href="?page={{ $page+1 }}{{ $qs }}" class="{{ $page>=$totalPages?'disabled':'' }}">›</a>
                <a href="?page={{ $totalPages }}{{ $qs }}" class="{{ $page>=$totalPages?'disabled':'' }}">»</a>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" class="checkbox" id="selectAll"></th>
                        <th>Username</th><th>Nama</th><th>NIP</th><th>Kelas Wali</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageData as $wk)
                    <tr>
                        <td class="checkbox-cell">
                            <button type="button" class="row-delete-pill" onclick="deleteSingleRow({{ $wk->id }})">🗑️</button>
                            <input type="checkbox" name="ids[]" value="{{ $wk->id }}" class="checkbox-item checkbox">
                        </td>
                        <td>{{ $wk->username ?? '-' }}</td>
                        <td><span class="clickable-name" onclick="showWkProfile({{ json_encode($wk) }})">{{ $wk->nama ?? '-' }}</span></td>
                        <td>{{ $wk->nip ?? '-' }}</td>
                        <td>{{ $wk->kelas ?? '-' }}</td>
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
        <h2>Create New Wali Kelas</h2>
        <form method="POST" action="{{ route('coordinator.manage-walikelas.action') }}">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="create-form-grid">
                <div class="form-group full-width"><label>Login Credentials</label><hr style="border:0;height:1px;background:#dce8f5;"></div>
                <div class="form-group"><label>Username *</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password *</label><input type="password" name="password" required></div>
                <div class="form-group full-width" style="margin-top:10px;"><label>Wali Kelas Details</label><hr style="border:0;height:1px;background:#dce8f5;"></div>
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama" required></div>
                <div class="form-group"><label>NIP</label><input type="text" name="nip"></div>
                <div class="form-group">
                    <label>Kelas Wali</label>
                    <select name="kelas">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>No HP</label><input type="text" name="no_hp"></div>
            </div>
            <button type="submit" class="submit-btn" onclick="return confirm('Create wali kelas?')">Create Wali Kelas</button>
        </form>
    </div>
</div>

<form id="forceResetPasswordForm" method="POST" action="{{ route('coordinator.manage-walikelas.action') }}" style="display:none;">
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
            <div class="profile-section-title">Data Wali Kelas</div>
            <table class="profile-data-table">
                <tr><td class="profile-label">Username</td><td class="profile-colon">:</td><td class="profile-value" id="pUsername">-</td></tr>
                <tr><td class="profile-label">Nama Lengkap</td><td class="profile-colon">:</td><td class="profile-value" id="pNama">-</td></tr>
                <tr><td class="profile-label">NIP</td><td class="profile-colon">:</td><td class="profile-value" id="pNip">-</td></tr>
                <tr><td class="profile-label">Kelas Wali</td><td class="profile-colon">:</td><td class="profile-value" id="pKelas">-</td></tr>
                <tr><td class="profile-label">No HP</td><td class="profile-colon">:</td><td class="profile-value" id="pNoHp">-</td></tr>
            </table>
        </div>
        <div id="profileEditMode" style="display:none;">
            <div class="profile-header">
                <div>Edit | <span id="pEditNameHeader">-</span></div>
            </div>
            <form method="POST" action="{{ route('coordinator.manage-walikelas.action') }}" id="editForm">
                @csrf
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="edit_id" id="editId">
                <table class="profile-data-table">
                    <tr><td class="profile-label">Username</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="username" id="editUsername" required></td></tr>
                    <tr><td class="profile-label">Nama Lengkap</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nama" id="editNama" required></td></tr>
                    <tr><td class="profile-label">NIP</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="nip" id="editNip"></td></tr>
                    <tr><td class="profile-label">Kelas Wali</td><td class="profile-colon">:</td><td>
                        <select class="edit-select" name="kelas" id="editKelas">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </td></tr>
                    <tr><td class="profile-label">No HP</td><td class="profile-colon">:</td><td><input class="edit-input" type="text" name="no_hp" id="editNoHp"></td></tr>
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

    function showWkProfile(u) {
        showProfile(u, [
            {id:'pNameHeader', val:u.nama}, {id:'pUsername', val:u.username}, {id:'pNama', val:u.nama},
            {id:'pNip', val:u.nip}, {id:'pKelas', val:u.kelas},
            {id:'pNoHp', val:u.no_hp}
        ]);
    }

    window.enterEditMode = function() {
        const u = getCurrentUser(); if (!u) return;
        document.getElementById('pEditNameHeader').textContent = u.nama || '';
        document.getElementById('editId').value      = u.id;

        document.getElementById('editUsername').value = u.username || '';
        document.getElementById('editNama').value    = u.nama || '';
        document.getElementById('editNip').value     = u.nip  || '';
        document.getElementById('editKelas').value   = u.kelas_id || '';
        document.getElementById('editNoHp').value    = u.no_hp  || '';
        document.getElementById('profileViewMode').style.display = 'none';
        document.getElementById('profileEditMode').style.display = 'block';
    };
</script>
@endsection
