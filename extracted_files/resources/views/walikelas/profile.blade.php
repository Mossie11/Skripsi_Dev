@extends('layouts.app')
@section('page_title', 'Profile Wali Kelas')
@section('styles')
<style>
    .profile-card { background:#fff; border-radius:12px; padding:2.5rem; max-width:1000px; box-shadow:0 4px 20px rgba(12,68,124,.10); }
    .profile-header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #0C447C; padding-bottom:.8rem; margin-bottom:2rem; }
    .profile-title { font-size:2rem; font-weight:bold; color:#0C447C; margin:0; display:flex; align-items:center; gap:8px; }
    .btn-edit { padding:.6rem 1.6rem; background:#E6F1FB; color:#0C447C; border:1.5px solid #378ADD; border-radius:6px; cursor:pointer; font-weight:600; font-size:1.05rem; display:flex; align-items:center; gap:6px; transition:all .2s; }
    .btn-edit:hover { background:#378ADD; color:#fff; }
    .btn-cancel { padding:.6rem 1.6rem; background:#f5f5f5; color:#555; border:1.5px solid #ccc; border-radius:6px; cursor:pointer; font-weight:600; font-size:1.05rem; display:none; align-items:center; gap:6px; transition:all .2s; margin-left:.5rem; }
    .btn-cancel:hover { background:#e0e0e0; }
    .profile-body { display:flex; gap:3rem; }
    .profile-left { flex:1; min-width:0; }
    .profile-right { width:240px; flex-shrink:0; display:flex; flex-direction:column; align-items:center; gap:1.2rem; }
    .photo-container { width:200px; height:200px; border-radius:50%; border:3px solid #E6F1FB; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#E6F1FB; position:relative; }
    .photo-container img { width:100%; height:100%; object-fit:cover; }
    .photo-container .photo-placeholder { font-size:5.5rem; color:#0C447C; }
    .photo-upload-area { display:none; flex-direction:column; align-items:center; gap:.5rem; width:100%; }
    .photo-upload-area label { padding:.5rem 1.2rem; background:#E6F1FB; color:#0C447C; border:1.5px solid #378ADD; border-radius:6px; cursor:pointer; font-weight:600; font-size:.9rem; text-align:center; transition:all .2s; }
    .photo-upload-area label:hover { background:#378ADD; color:#fff; }
    .photo-upload-area input[type=file] { display:none; }
    .photo-name { font-size:.8rem; color:#888; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; text-align:center; }
    .btn-delete-photo { padding:.4rem 1rem; background:#fff0f0; color:#dc2626; border:1.5px solid #dc2626; border-radius:6px; cursor:pointer; font-weight:600; font-size:.85rem; display:none; align-items:center; gap:4px; transition:all .2s; }
    .btn-delete-photo:hover { background:#dc2626; color:#fff; }
    .profile-data-table { width:100%; border-collapse:collapse; }
    .profile-data-table td { padding:14px 0; border-bottom:1px solid #f0f4f8; vertical-align:middle; }
    .pl { width:40%; font-weight:600; color:#555; white-space:nowrap; font-size:1.15rem; }
    .pc { width:20px; color:#888; font-size:1.15rem; }
    .pv { color:#1a2a3a; font-size:1.15rem; }
    .edit-input { width:100%; padding:10px 14px; border:1px solid #aac5e0; border-radius:4px; background:#f9f9f9; font-size:1.15rem; display:none; }
    .edit-input:focus { outline:none; border-color:#378ADD; background:#fff; }
    .btn-save { padding:.8rem 2.2rem; background:#0C447C; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:1.15rem; display:none; }
    .btn-save:hover { background:#088c66; }
    .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:.75rem 1rem; border-radius:6px; margin-bottom:1rem; }
    @media(max-width:640px) {
        .profile-body { flex-direction:column-reverse; align-items:center; }
        .profile-right { width:100%; }
    }
</style>
@endsection

@section('content')
<div class="profile-card">


    <div class="profile-header">
        <div class="profile-title"><i class='bx bx-user'></i> Profile Wali Kelas</div>
        <div style="display:flex;align-items:center;">
            <button type="button" class="btn-edit" id="btnEdit" onclick="toggleEdit()">
                <i class='bx bx-edit-alt'></i> Edit
            </button>
            <button type="button" class="btn-cancel" id="btnCancel" onclick="cancelEdit()">
                <i class='bx bx-x'></i> Batal
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('walikelas.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="profile-body">
            <div class="profile-left">
                <table class="profile-data-table">
                    <tr>
                        <td class="pl">Username</td><td class="pc">:</td>
                        <td class="pv" style="font-weight:600;color:#0C447C;">{{ $user->username ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="pl">Nama Lengkap</td><td class="pc">:</td>
                        <td>
                            <span class="pv" data-field="nama">{{ $user->nama ?? '-' }}</span>
                            <input class="edit-input" type="text" name="nama" value="{{ $user->nama ?? '' }}" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="pl">Kelas yang Diajar</td><td class="pc">:</td>
                        <td class="pv" style="font-weight:600;color:#0C447C;">{{ $user->kelas_diajar ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="pl">No HP</td><td class="pc">:</td>
                        <td>
                            <span class="pv" data-field="no_hp">{{ $user->no_hp ?? '-' }}</span>
                            <input class="edit-input" type="text" name="no_hp" value="{{ $user->no_hp ?? '' }}">
                        </td>
                    </tr>
                </table>
                <div style="margin-top:1.5rem;">
                    <button type="submit" class="btn-save" id="btnSave">💾 Simpan Perubahan</button>
                </div>
            </div>
            <div class="profile-right">
                <div class="photo-container" id="photoContainer">
                    @if($user->foto)
                        <img src="{{ asset('storage/' . $user->foto) }}" alt="Profile Photo" id="photoPreview">
                    @else
                        <i class='bx bx-user photo-placeholder' id="photoPlaceholder"></i>
                        <img src="" alt="Profile Photo" id="photoPreview" style="display:none;">
                    @endif
                </div>
                <div class="photo-upload-area" id="photoUploadArea">
                    <label for="fotoInput"><i class='bx bx-camera'></i> Pilih Foto</label>
                    <input type="file" name="foto" id="fotoInput" accept="image/*" onchange="previewPhoto(this)">
                    <div class="photo-name" id="photoName"></div>
                </div>
                <input type="hidden" name="delete_foto" id="deleteFotoInput" value="0">
                @if($user->foto)
                <button type="button" class="btn-delete-photo" id="btnDeletePhoto" onclick="deletePhoto()">
                    <i class='bx bx-trash'></i> Hapus Foto
                </button>
                @endif
            </div>
        </div>
    </form>
</div>

<script>
function toggleEdit() {
    document.querySelectorAll('.pv[data-field]').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.edit-input').forEach(i => i.style.display = 'block');
    document.getElementById('btnSave').style.display = 'inline-block';
    document.getElementById('btnEdit').style.display = 'none';
    document.getElementById('btnCancel').style.display = 'flex';
    document.getElementById('photoUploadArea').style.display = 'flex';
    const delBtn = document.getElementById('btnDeletePhoto');
    if (delBtn && !document.getElementById('deleteFotoInput').value.includes('1')) delBtn.style.display = 'flex';
}

function cancelEdit() {
    document.querySelectorAll('.pv[data-field]').forEach(s => s.style.display = 'inline');
    document.querySelectorAll('.edit-input').forEach(i => i.style.display = 'none');
    document.getElementById('btnSave').style.display = 'none';
    document.getElementById('btnEdit').style.display = 'flex';
    document.getElementById('btnCancel').style.display = 'none';
    document.getElementById('photoUploadArea').style.display = 'none';
    document.getElementById('photoName').textContent = '';
    document.getElementById('fotoInput').value = '';
    document.getElementById('deleteFotoInput').value = '0';
    const delBtn = document.getElementById('btnDeletePhoto');
    if (delBtn) delBtn.style.display = 'none';
    const hasPhoto = {{ $user->foto ? 'true' : 'false' }};
    if (!hasPhoto) {
        document.getElementById('photoPreview').style.display = 'none';
        const ph = document.getElementById('photoPlaceholder');
        if (ph) ph.style.display = 'block';
    } else {
        document.getElementById('photoPreview').src = "{{ $user->foto ? asset('storage/' . $user->foto) : '' }}";
        document.getElementById('photoPreview').style.display = 'block';
    }

    document.querySelectorAll('.edit-input').forEach(input => {
        const name = input.name;
        const span = document.querySelector(`.pv[data-field="${name}"]`);
        if (span) {
            const original = span.textContent.trim();
            input.value = original === '-' ? '' : original;
        }
    });
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            const ph = document.getElementById('photoPlaceholder');
            if (ph) ph.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('photoName').textContent = input.files[0].name;
        document.getElementById('deleteFotoInput').value = '0';
        const delBtn = document.getElementById('btnDeletePhoto');
        if (delBtn) delBtn.style.display = 'flex';
    }
}

function deletePhoto() {
    document.getElementById('deleteFotoInput').value = '1';
    document.getElementById('photoPreview').style.display = 'none';
    const ph = document.getElementById('photoPlaceholder');
    if (!ph) {
        const icon = document.createElement('i');
        icon.className = 'bx bx-user photo-placeholder';
        icon.id = 'photoPlaceholder';
        document.getElementById('photoContainer').appendChild(icon);
    } else {
        ph.style.display = 'block';
    }
    document.getElementById('fotoInput').value = '';
    document.getElementById('photoName').textContent = '';
    document.getElementById('btnDeletePhoto').style.display = 'none';
}
</script>
@endsection