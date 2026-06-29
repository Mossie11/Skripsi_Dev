@extends('layouts.app')
@section('page_title','Manage Kelas')
@section('styles')
@include('coordinator.partials.manage-styles')
<style>
    /* Premium Select2 Styling to match theme */
    .select2-container--default .select2-selection--single {
        background-color: #E6F1FB !important;
        border: 1px solid #aac5e0 !important;
        height: 42px !important;
        border-radius: 6px !important;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0C447C !important;
        font-weight: 600;
        padding-left: 10px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .select2-dropdown {
        border: 1px solid #aac5e0 !important;
        box-shadow: 0 4px 15px rgba(12, 68, 124, 0.15);
        border-radius: 6px !important;
        background-color: #fff !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #aac5e0 !important;
        border-radius: 4px;
        outline: none;
        padding: 6px 10px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0C447C !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #E6F1FB !important;
        color: #0C447C !important;
    }
</style>
@endsection
@section('content')
<div class="container-manage">
    <div class="header">
        <h2><i class='bx bx-building'></i> Daftar Kelas</h2>
        <button class="btn btn-create" onclick="openModal('createModal')">
            <i class='bx bx-plus'></i> Tambah Kelas
        </button>
    </div>

    <div class="table-wrapper">
        <table style="min-width:500px;">
            <thead>
                <tr>
                    <th style="padding-left:1rem!important;width:auto;">#</th>
                    <th>Nama Kelas</th>
                    <th>Wali Kelas</th>
                    <th>Jumlah Siswa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $i => $kelas)
                <tr>
                    <td style="padding-left:1rem!important;">{{ $i + 1 }}</td>
                    <td style="font-weight:bold;color:#0C447C;">{{ $kelas->nama_kelas }}</td>
                    <td>{{ $kelas->wali_kelas ?? '-' }}</td>
                    <td>
                        <span style="
                            display:inline-block; padding:2px 12px; border-radius:12px; font-weight:600; font-size:.85rem;
                            {{ $kelas->jumlah_siswa > 0 ? 'background:#e6f9f0;color:#1a8a5a;' : 'background:#f5f5f5;color:#999;' }}
                        ">{{ $kelas->jumlah_siswa }}</span>
                    </td>
                    <td style="display:flex;gap:8px;">
                        <button class="btn btn-create" style="padding:.4rem .8rem;font-size:.85rem;"
                            onclick="openEditModal({{ json_encode($kelas) }})">
                            ✏️ Edit
                        </button>
                        <form method="POST" action="{{ route('coordinator.manage-kelas.action') }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="{{ $kelas->id }}">
                            <button type="submit" class="btn" style="background:#e03535;color:#fff;padding:.4rem .8rem;font-size:.85rem;" onclick="return confirm('Hapus kelas {{ $kelas->nama_kelas }}? Data kelas di tabel lain tidak akan terhapus.')">🗑️ Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:2rem;color:#999;font-style:italic;">
                        Belum ada kelas. Klik "Tambah Kelas" untuk menambahkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal-overlay" id="createModal">
    <div class="create-modal-content" style="max-width:450px;">
        <button class="modal-close" onclick="closeModal('createModal')">×</button>
        <h2 style="margin-bottom:20px;">Tambah Kelas Baru</h2>
        <form method="POST" action="{{ route('coordinator.manage-kelas.action') }}">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="form-group" style="margin-bottom:15px;"><label>Nama Kelas *</label>
                <select name="nama_kelas" required class="edit-select" style="padding:.6rem;background:#E6F1FB;border:1px solid #aac5e0;color:#0C447C;border-radius:4px;width:100%;">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $kelasOpt)
                    <option value="{{ $kelasOpt }}">{{ $kelasOpt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:20px;"><label>Wali Kelas</label>
                <select name="wali_kelas" id="createWaliKelas" class="edit-select" style="width:100%;">
                    <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                    @foreach($waliKelasList as $wk)
                    <option value="{{ $wk->id }}">{{ $wk->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="submit-btn">Tambah Kelas</button>
        </form>
    </div>
</div>

{{-- Edit/Rename Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="create-modal-content" style="max-width:450px;">
        <button class="modal-close" onclick="closeModal('editModal')">×</button>
        <h2 style="margin-bottom:20px;">Edit Kelas</h2>
        <form method="POST" action="{{ route('coordinator.manage-kelas.action') }}">
            @csrf
            <input type="hidden" name="action" value="rename">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="old_nama_kelas" id="editOldNamaKelas">
            <div class="form-group" style="margin-bottom:15px;"><label>Nama Kelas *</label>
                <select name="new_nama_kelas" id="editNamaKelas" required class="edit-select" style="padding:.6rem;background:#E6F1FB;border:1px solid #aac5e0;color:#0C447C;border-radius:4px;width:100%;">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $kelasOpt)
                    <option value="{{ $kelasOpt }}">{{ $kelasOpt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:20px;"><label>Wali Kelas</label>
                <select name="new_wali_kelas" id="editWaliKelas" class="edit-select" style="width:100%;">
                    <option value="">-- Pilih Wali Kelas --</option>
                    @foreach($waliKelasList as $wk)
                    <option value="{{ $wk->id }}">{{ $wk->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="submit-btn" onclick="return confirm('Simpan perubahan? Jika nama kelas berubah, semua referensi di siswa, guru/wali kelas, dan jadwal akan ikut berubah.')">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
@section('scripts')
@include('coordinator.partials.manage-scripts')
<script>
function openEditModal(kelas) {
    document.getElementById('editId').value = kelas.id;
    document.getElementById('editOldNamaKelas').value = kelas.nama_kelas;
    document.getElementById('editNamaKelas').value = kelas.nama_kelas;
    
    // Update select2 value and trigger change event to refresh UI
    $('#editWaliKelas').val(kelas.user_walikelas_id || '').trigger('change');
    
    openModal('editModal');
}

$(document).ready(function() {
    $('#createWaliKelas').select2({
        dropdownParent: $('#createModal')
    });
    $('#editWaliKelas').select2({
        dropdownParent: $('#editModal')
    });
});
</script>
@endsection
