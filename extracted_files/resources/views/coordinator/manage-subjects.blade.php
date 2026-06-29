@extends('layouts.app')
@section('page_title','Manage Subjects')
@section('styles')@include('coordinator.partials.manage-styles')
<style>
    .guru-pill { display:inline-flex; align-items:center; gap:6px; background:#E6F1FB; color:#0C447C; padding:4px 12px; border-radius:20px; font-size:.85rem; margin:3px; }
    .guru-pill .remove-btn { background:none; border:none; color:#e03535; cursor:pointer; font-size:1rem; font-weight:bold; line-height:1; padding:0 2px; display:none; }
    .guru-pill .remove-btn:hover { color:#b02020; }
    .add-guru-btn { display:none; align-items:center; gap:4px; background:#0C447C; color:#fff; padding:4px 12px; border-radius:20px; font-size:.85rem; margin:3px; border:none; cursor:pointer; }
    .add-guru-btn:hover { background:#378ADD; }
    .add-form-inline { display:inline-flex; align-items:center; gap:6px; margin:3px; }
    .add-form-inline select { padding:4px 8px; border:1px solid #aac5e0; border-radius:4px; font-size:.85rem; background:#f9f9f9; color:#0C447C; max-width:200px; }
    .add-form-inline .confirm-btn { background:#0C447C; color:#fff; border:none; border-radius:4px; padding:4px 10px; cursor:pointer; font-size:.85rem; }
    .add-form-inline .cancel-btn { background:#e03535; color:#fff; border:none; border-radius:4px; padding:4px 10px; cursor:pointer; font-size:.85rem; }
    /* Edit mode active */
    .edit-mode .guru-pill .remove-btn { display:inline; }
    .edit-mode .add-guru-btn { display:inline-flex; }
    .edit-mode .delete-mapel-form { display:inline-block !important; }
    
    #subjectsContainer .edit-view { display: none !important; }
    #subjectsContainer .non-edit-view { display: block !important; }
    #subjectsContainer.edit-mode .edit-view { display: flex !important; }
    #subjectsContainer.edit-mode .non-edit-view { display: none !important; }
    
    .tab-container { margin-bottom: 1.5rem; border-bottom: 2px solid #e0e8f0; display: flex; gap: 1rem; }
    .tab-btn { background: none; border: none; padding: 0.5rem 1rem; font-size: 1.05rem; font-weight: 600; color: #7baada; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.2s; }
    .tab-btn:hover { color: #358ae0; }
    .tab-btn.active { color: #0C447C; border-bottom-color: #0C447C; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
</style>
@endsection
@section('content')
<div class="container-manage" id="subjectsContainer">
    <div class="header">
        <h2><i class='bx bx-book'></i> Daftar Mata Pelajaran</h2>
        <div class="actions">
            <button class="btn btn-create" id="editToggleBtn" onclick="toggleEditMode()" style="margin-right:8px;background:#358ae0;">✏️ Edit</button>
            <button class="btn btn-create" onclick="openModal('createMapelModal')">+ CREATE</button>
        </div>
    </div>

    <div class="tab-container">
        <button class="tab-btn active" onclick="switchTab('mapel', this)">Daftar Mata Pelajaran</button>
        <button class="tab-btn" onclick="switchTab('ekskul', this)">Daftar Ekstrakurikuler</button>
        <button class="tab-btn" onclick="switchTab('lab', this)">Daftar Lab</button>
    </div>

    <div id="tab-mapel" class="tab-content active">
        <div class="table-wrapper" style="margin-bottom: 2rem;">
            <table style="min-width:500px;">
                <thead>
                    <tr>
                        <th style="padding-left:1rem!important;width:40px;">#</th>
                        <th style="width:250px;">Nama Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapelList as $mpId => $mp)
                    <tr>
                        <td style="padding-left:1rem!important;">{{ $loop->iteration }}</td>
                        <td style="font-weight:500;color:#0C447C;">
                            {{ $mp }}
                            <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}" class="delete-mapel-form" style="display:none;margin-left:8px;vertical-align:middle;">
                                @csrf
                                <input type="hidden" name="action" value="delete_mapel">
                                <input type="hidden" name="mata_pelajaran_id" value="{{ $mpId }}">
                                <button type="submit" style="background:none; border:none; color:#e03535; cursor:pointer; font-size:1.1rem; padding:0;" title="Hapus Mapel" onclick="return confirm('Yakin ingin menghapus mata pelajaran {{ $mp }}?')">🗑️</button>
                            </form>
                        </td>
                        <td>
                            @if(isset($guruByMapel[$mpId]) && $guruByMapel[$mpId]->count() > 0)
                                @foreach($guruByMapel[$mpId] as $guru)
                                    <span class="guru-pill">
                                        {{ $guru->nama }}
                                        <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}" style="display:inline;margin:0;">
                                            @csrf
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="guru_id" value="{{ $guru->id }}">
                                            <input type="hidden" name="mata_pelajaran_id" value="{{ $mpId }}">
                                            <button type="submit" class="remove-btn" title="Hapus guru dari mapel ini" onclick="return confirm('Hapus {{ $guru->nama }} dari {{ $mp }}?')">×</button>
                                        </form>
                                    </span>
                                @endforeach
                            @else
                                <span style="color:#aac5e0;font-size:.85rem;">Belum ada guru</span>
                            @endif

                            {{-- Add guru inline form --}}
                            <span id="addForm-{{ $mpId }}" style="display:none;">
                                <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}" class="add-form-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="assign">
                                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mpId }}">
                                    <select name="guru_id" required>
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach($allGuru as $g)
                                            @php
                                                $isAssigned = isset($guruByMapel[$mpId]) && $guruByMapel[$mpId]->contains('id', $g->id);
                                            @endphp
                                            @if(!$isAssigned)
                                                <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button type="submit" class="confirm-btn" onclick="return confirm('Assign guru ini ke {{ $mp }}?')">✓</button>
                                    <button type="button" class="cancel-btn" onclick="document.getElementById('addForm-{{ $mpId }}').style.display='none'">✕</button>
                                </form>
                            </span>
                            <button type="button" class="add-guru-btn" onclick="document.getElementById('addForm-{{ $mpId }}').style.display='inline-flex'">+ Tambah</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:2rem;color:#7baada;">Tidak ada mata pelajaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-ekskul" class="tab-content">
        <div class="table-wrapper" style="margin-bottom: 2rem;">
            <table style="min-width:500px;">
                <thead>
                    <tr>
                        <th style="padding-left:1rem!important;width:40px;">#</th>
                        <th style="width:250px;">Nama Ekstrakurikuler</th>
                        <th>Guru Pendamping</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ekskulList as $mpId => $mp)
                    <tr>
                        <td style="padding-left:1rem!important;">{{ $loop->iteration }}</td>
                        <td style="font-weight:500;color:#0C447C;">
                            {{ $mp }}
                            <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}" class="delete-mapel-form" style="display:none;margin-left:8px;vertical-align:middle;">
                                @csrf
                                <input type="hidden" name="action" value="delete_mapel">
                                <input type="hidden" name="mata_pelajaran_id" value="{{ $mpId }}">
                                <button type="submit" style="background:none; border:none; color:#e03535; cursor:pointer; font-size:1.1rem; padding:0;" title="Hapus Mapel" onclick="return confirm('Yakin ingin menghapus ekstrakurikuler {{ $mp }}?')">🗑️</button>
                            </form>
                        </td>
                        <td>
                            <!-- non-edit-mode view: display the string value -->
                            <div class="non-edit-view">
                                @if(!empty($ekskulPendamping[$mpId]))
                                    <span class="guru-pill" style="background:#E6F1FB; color:#0C447C; font-weight:600;">
                                        {{ $ekskulPendamping[$mpId] }}
                                    </span>
                                @else
                                    <span style="color:#aac5e0;font-size:.85rem;">Belum ada guru</span>
                                @endif
                            </div>

                            <!-- edit-mode view: dropdown select from guru list -->
                            <div class="edit-view">
                                <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}" style="display:flex; gap:6px; align-items:center;">
                                    @csrf
                                    <input type="hidden" name="action" value="update_ekskul_pendamping">
                                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mpId }}">
                                    <select name="guru_pendamping" style="padding:6px 10px; border:2px solid #aac5e0; border-radius:6px; font-size:.85rem; color:#0C447C; width: 250px; outline:none; background:#fff;">
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach($allGuru as $g)
                                            <option value="{{ $g->nama }}" {{ ($ekskulPendamping[$mpId] ?? '') == $g->nama ? 'selected' : '' }}>{{ $g->nama }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="confirm-btn" style="background:#0C447C; color:#fff; border:none; border-radius:6px; padding:6px 12px; cursor:pointer; font-size:.85rem; font-weight:600;">Simpan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:2rem;color:#7baada;">Tidak ada ekstrakurikuler</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-lab" class="tab-content">
        <div class="table-wrapper" style="margin-bottom: 2rem;">
            <table style="min-width:500px;">
                <thead>
                    <tr>
                        <th style="padding-left:1rem!important;width:40px;">#</th>
                        <th style="width:250px;">Nama Lab</th>
                        <th>Guru Pendamping</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labList as $mpId => $mp)
                    <tr>
                        <td style="padding-left:1rem!important;">{{ $loop->iteration }}</td>
                        <td style="font-weight:500;color:#0C447C;">
                            {{ $mp }}
                            <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}" class="delete-mapel-form" style="display:none;margin-left:8px;vertical-align:middle;">
                                @csrf
                                <input type="hidden" name="action" value="delete_mapel">
                                <input type="hidden" name="mata_pelajaran_id" value="{{ $mpId }}">
                                <button type="submit" style="background:none; border:none; color:#e03535; cursor:pointer; font-size:1.1rem; padding:0;" title="Hapus Mapel" onclick="return confirm('Yakin ingin menghapus lab {{ $mp }}?')">🗑️</button>
                            </form>
                        </td>
                        <td>
                            <!-- non-edit-mode view: display the string value -->
                            <div class="non-edit-view">
                                @if(!empty($labPendamping[$mpId]))
                                    <span class="guru-pill" style="background:#E6F1FB; color:#0C447C; font-weight:600;">
                                        {{ $labPendamping[$mpId] }}
                                    </span>
                                @else
                                    <span style="color:#aac5e0;font-size:.85rem;">Belum ada guru</span>
                                @endif
                            </div>

                            <!-- edit-mode view: dropdown select from guru list -->
                            <div class="edit-view">
                                <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}" style="display:flex; gap:6px; align-items:center;">
                                    @csrf
                                    <input type="hidden" name="action" value="update_lab_pendamping">
                                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mpId }}">
                                    <select name="guru_pendamping_lab" style="padding:6px 10px; border:2px solid #aac5e0; border-radius:6px; font-size:.85rem; color:#0C447C; width: 250px; outline:none; background:#fff;">
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach($allGuru as $g)
                                            <option value="{{ $g->nama }}" {{ ($labPendamping[$mpId] ?? '') == $g->nama ? 'selected' : '' }}>{{ $g->nama }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="confirm-btn" style="background:#0C447C; color:#fff; border:none; border-radius:6px; padding:6px 12px; cursor:pointer; font-size:.85rem; font-weight:600;">Simpan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:2rem;color:#7baada;">Tidak ada lab</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Create Mapel Modal ──────────────────────────── -->
<div class="modal-overlay" id="createMapelModal">
    <div class="create-modal-content" style="max-width:500px;">
        <button class="modal-close" onclick="closeModal('createMapelModal')">×</button>
        <h2>Tambah Mata Pelajaran</h2>
        <form method="POST" action="{{ route('coordinator.manage-subjects.action') }}">
            @csrf
            <input type="hidden" name="action" value="create_mapel">
            <div class="form-group" style="margin-bottom:20px;">
                <label>Nama Mata Pelajaran *</label>
                <input type="text" name="nama_mapel" required placeholder="Contoh: MATEMATIKA" style="width:100%;padding:10px;border:1px solid #aac5e0;border-radius:4px;color:#0C447C;">
            </div>
            <button type="submit" class="submit-btn" onclick="return confirm('Tambahkan mata pelajaran baru?')">Simpan</button>
        </form>
    </div>
</div>
@endsection
@section('scripts')
@include('coordinator.partials.manage-scripts')
<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    btn.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
    localStorage.setItem('manage_subjects_active_tab', tabId);
}

document.addEventListener('DOMContentLoaded', function() {
    const savedTab = localStorage.getItem('manage_subjects_active_tab');
    if (savedTab) {
        const tabBtn = Array.from(document.querySelectorAll('.tab-btn')).find(btn => btn.getAttribute('onclick').includes(savedTab));
        if (tabBtn) {
            switchTab(savedTab, tabBtn);
        }
    }
});

let editMode = false;
function toggleEditMode() {
    editMode = !editMode;
    const container = document.getElementById('subjectsContainer');
    const btn = document.getElementById('editToggleBtn');
    if (editMode) {
        container.classList.add('edit-mode');
        btn.textContent = '✅ Selesai';
        btn.style.background = '#e03535';
    } else {
        container.classList.remove('edit-mode');
        btn.textContent = '✏️ Edit';
        btn.style.background = '#358ae0';
        // Hide all open add forms
        document.querySelectorAll('[id^="addForm-"]').forEach(el => el.style.display = 'none');
    }
}
</script>
@endsection
