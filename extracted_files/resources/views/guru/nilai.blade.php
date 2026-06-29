@extends('layouts.app')
@section('page_title', 'Nilai Siswa')
@section('styles')
<style>
    select option { background:#fff !important; color:#1a2a3a !important; }
    .page-header { font-size:1.4rem; font-weight:bold; color:#0C447C; display:flex; align-items:center; }
    .controls-row { display:flex; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:1.5rem; background:#fff; padding:1rem 1.5rem; border-radius:8px; border:1px solid #dce8f5; box-shadow: 0 4px 15px rgba(12, 68, 124, 0.05); }
    .control-group { display:flex; align-items:center; gap:10px; }
    .control-group label { color:#378ADD; font-size:.95rem; white-space:nowrap; font-weight:600; }
    .kelas-select { padding:.5rem .8rem; background:#E6F1FB; border:1px solid #aac5e0; color:#0C447C; border-radius:6px; font-size:.95rem; cursor:pointer; min-width:140px; }
    .info-bar { background:#E6F1FB; border:1px solid #dce8f5; border-radius:6px; padding:.65rem 1rem; font-size:.88rem; color:#0C447C; margin-bottom:1.5rem; display:none; }
    .info-bar.visible { display:block; }
    .nilai-table-wrapper { overflow-x:auto; }
    .nilai-table { width:100%; border-collapse:collapse; font-size:.88rem; min-width:780px; }
    .nilai-table th { background:#0C447C !important; border:1px solid #dce8f5; padding:10px 12px; text-align:center; color:#fff !important; font-weight:700; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
    .nilai-table td { border:1px solid #dce8f5; padding:6px 8px; color:#1a2a3a; vertical-align:middle; }
    .nilai-table td.td-nama { font-weight:600; color:#0C447C; white-space:nowrap; padding-left:12px; }
    .nilai-table tbody tr:hover { background:#e0edf8; }
    .grade-input { width:60px; padding:5px 6px; background:#fff; border:1px solid #dce8f5; color:#0C447C; border-radius:4px; text-align:center; font-size:.88rem; }
    .grade-input:focus { outline:none; border-color:#378ADD; background:#e0edf8; }
    .grade-input::-webkit-outer-spin-button, .grade-input::-webkit-inner-spin-button { -webkit-appearance:none; }
    .grade-input[type=number] { -moz-appearance:textfield; }
    .deskriptif-input { width:100%; min-width:160px; padding:5px 8px; background:#fff; border:1px solid #dce8f5; color:#0C447C; border-radius:4px; font-size:.85rem; }
    .deskriptif-input:focus { outline:none; border-color:#378ADD; }
    .nilai-akhir-cell { text-align:center; font-weight:bold; color:#4ecf9a; font-size:.95rem; }
    .nilai-akhir-cell.empty { color:#aac5e0; }
    .grade-input:disabled, .deskriptif-input:disabled { background:transparent; border-color:transparent; color:#1a2a3a; font-weight:500; cursor:default; }
    .state-msg { text-align:center; padding:2.5rem; color:#7baada; font-size:.95rem; }
    .state-msg.error-col { color:#d44; }
    .btn-edit-all { display:inline-flex; align-items:center; gap:6px; background:#EF9F27; color:#fff; border:none; padding:6px 14px; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; transition:all .2s; }
    .btn-edit-all:hover { background:#d48a1b; }
    .btn-save-all { display:none; align-items:center; gap:6px; background:#28a745; color:#fff; border:none; padding:6px 14px; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; transition:all .2s; }
    .btn-save-all:hover { background:#1e7e34; }
    .btn-save-all:disabled { opacity:.6; cursor:not-allowed; }
    .btn-cancel-all { display:none; align-items:center; gap:6px; background:#dc3545; color:#fff; border:none; padding:6px 14px; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; transition:all .2s; }
    .btn-cancel-all:hover { background:#bd2130; }
    .status-toast { position:fixed; top:20px; right:20px; background:#28a745; color:#fff; padding:12px 24px; border-radius:8px; font-weight:600; font-size:.9rem; z-index:9999; display:none; box-shadow:0 4px 15px rgba(0,0,0,.15); }
    .status-toast.error { background:#dc3545; }
    .mapel-section-title { font-size:1rem; font-weight:bold; color:#0C447C; margin-bottom:.75rem; padding-bottom:.4rem; border-bottom:1px solid #dce8f5; }
    
    /* ── Tabs ── */
    .tab-container {
        display: flex; gap: 0; margin-bottom: 0;
        border-bottom: 2px solid #dce8f5;
        margin-top: 1rem;
    }
    .tab-btn {
        background: none; border: none; padding: 14px 28px;
        font-size: 1rem; font-weight: 600; color: #6a9bc0;
        cursor: pointer; position: relative;
        transition: color .2s, background .2s;
        border-radius: 8px 8px 0 0;
    }
    .tab-btn:hover { background: #f0f7ff; color: #0C447C; }
    .tab-btn.active {
        color: #0C447C; background: #fff;
        border: 2px solid #dce8f5; border-bottom: 2px solid #fff;
        margin-bottom: -2px;
    }
    .tab-panel { display: none; background: #fff; border: 2px solid #dce8f5; border-top: none; border-radius: 0 0 12px 12px; padding: 24px; }
    .tab-panel.active { display: block; }
    .tab-panel .nilai-table-wrapper { box-shadow: none; border: none; background: transparent; }
    .lab-readonly-cell { text-align:center; font-weight:bold; color:#0C447C; font-size:.95rem; }
    .lab-readonly-cell.empty { color:#aac5e0; }
    
    /* ── Modal Styles ── */
    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(12, 68, 124, 0.4); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000;
    }
    .modal-box {
        background: #fff; width: 100%; max-width: 480px; border-radius: 16px;
        padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); text-align: center;
    }
    .modal-icon { width: 64px; height: 64px; background: #E6F1FB; color: #378ADD; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px; }
    .modal-box h2 { color: #0C447C; margin: 0 0 8px; font-size: 1.5rem; }
    .modal-subtitle { color: #6a9bc0; margin-bottom: 24px; font-size: .95rem; }
    .modal-field { margin-bottom: 20px; text-align: left; }
    .modal-field label { display: block; font-weight: 600; color: #1a2a3a; margin-bottom: 8px; font-size: .9rem; display: flex; align-items: center; gap: 6px; }
    .modal-field select { width: 100%; padding: 12px 14px; border: 2px solid #eef3fa; border-radius: 8px; font-size: .95rem; color: #333; outline: none; transition: all .2s; cursor: pointer; }
    .modal-field select:focus { border-color: #378ADD; background: #fbfdff; }
    .modal-btn {
        width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 700;
        font-size: 1rem; cursor: pointer; transition: all .2s; margin-top: 8px;
    }
    .modal-btn.primary { background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); color: #fff; }
    .modal-btn.primary:hover { background: linear-gradient(135deg, #0d3e6e 0%, #155085 100%); transform: translateY(-1px); }
    .modal-btn.primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    .container-manage { width: 100%; padding: 0; }
    .page-header-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1.25rem 1.75rem; background: #fff; border-radius: 8px; border: 1px solid #dce8f5; box-shadow: 0 4px 15px rgba(12, 68, 124, 0.05); }
    .page-header-container .page-header { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .btn-change { display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 2px solid #dce8f5; color: #378ADD; padding: 6px 14px; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; transition: all .2s; text-decoration: none; }
    .btn-change:hover { border-color: #378ADD; background: #f0f7ff; }
</style>
@endsection

@section('content')
<div class="container-manage">
    
    <div class="modal-overlay" id="selectModal">
        <div class="modal-box">
            <div class="modal-icon"><i class='bx bx-edit'></i></div>
            <h2>Kelola Nilai Siswa</h2>
            <p class="modal-subtitle">Pilih kelas yang diajar untuk melihat dan mengedit nilai siswa.</p>

            <div class="modal-field">
                <label for="kelasSelect"><i class='bx bx-building'></i> Kelas</label>
                <select id="kelasSelect">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $id => $namaKelas)
                    <option value="{{ $id }}">{{ $namaKelas }}</option>
                    @endforeach
                </select>
            </div>

            <button class="modal-btn primary" id="modalSubmitBtn" disabled>
                <i class='bx bx-search'></i> Tampilkan Nilai
            </button>
        </div>
    </div>

    <div id="mainContent" style="display:none;">
        <div class="page-header-container">
            <div class="page-header">
                <i class='bx bx-edit'></i> Nilai Siswa
                @if($mapel)
                    <span style="background:#EF9F27;color:#fff;padding:4px 16px;border-radius:20px;font-size:.85rem;font-weight:600;margin-left:10px;">📚 {{ $mapel }}</span>
                @else
                    <span style="background:#e03535;color:#fff;padding:4px 16px;border-radius:20px;font-size:.85rem;margin-left:10px;">⚠️ Mapel belum diset</span>
                @endif
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button class="btn-edit-all" id="btnEditAll" style="display:none;"><i class='bx bx-edit-alt'></i> Edit Nilai</button>
                <button class="btn-cancel-all" id="btnCancelAll"><i class='bx bx-x'></i> Batal</button>
                <button class="btn-save-all" id="btnSaveAll"><i class='bx bx-save'></i> Simpan</button>
                <button class="btn-change" onclick="window.location.reload()"><i class='bx bx-refresh'></i> Ganti Kelas</button>
            </div>
        </div>
        <div class="status-toast" id="statusToast"></div>

        <div class="info-bar" id="infoBar"></div>

        <div class="controls-row" id="filterControls" style="display:none;">
            <div class="control-group">
                <label><i class='bx bx-calendar'></i> Semester:</label>
                <select id="semesterFilter" class="kelas-select">
                    <option value="1" {{ $activeSemester === 'Ganjil' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $activeSemester === 'Genap' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </div>
        </div>

        <div id="nilaiContainer"><p class="state-msg">Pilih kelas dari dropdown di atas.</p></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const NILAI_API = '{{ route("guru.api.nilai.get") }}';
    const NILAI_POST_API = '{{ route("guru.api.nilai.post") }}';
    const ACTIVE_SEMESTER = '{{ $activeSemester }}';

    // Column definitions per semester
    const ALL_COLUMNS = [
        { key: 'tugas1', label: 'T.1' },
        { key: 'uh1',    label: 'UH1' },
        { key: 'tugas2', label: 'T.2' },
        { key: 'uh2',    label: 'UH2' },
        { key: 'uts',    label: 'UTS' },
        { key: 'uas',    label: 'UAS' },
    ];

    let currentAllowedTypes = [];
    let currentBobot = null;

    (function() {
        const kelasSelect   = document.getElementById('kelasSelect');
        const nilaiContainer= document.getElementById('nilaiContainer');
        const infoBar       = document.getElementById('infoBar');

        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const selectModal    = document.getElementById('selectModal');
        const mainContent    = document.getElementById('mainContent');

        window.switchMapelTab = function(idx, btn) {
            btn.closest('.tab-container').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            nilaiContainer.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            const panel = document.getElementById('panel-mapel-' + idx);
            if (panel) panel.classList.add('active');
        };

        window.switchLabTab = function(btn) {
            btn.closest('.tab-container').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            nilaiContainer.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            const panel = document.getElementById('panel-lab');
            if (panel) panel.classList.add('active');
        };

        kelasSelect.addEventListener('change', function() {
            modalSubmitBtn.disabled = !this.value;
        });

        modalSubmitBtn.addEventListener('click', function() {
            if (!kelasSelect.value) return;
            selectModal.style.display = 'none';
            mainContent.style.display = 'block';
            loadNilai(kelasSelect.value);
        });

        function loadNilai(kelas) {
            const sem = document.getElementById('semesterFilter').value;
            nilaiContainer.innerHTML = '<p class="state-msg">Memuat data...</p>';
            infoBar.classList.remove('visible');
            fetch(NILAI_API + '?kelas=' + encodeURIComponent(kelas) + '&semester=' + sem)
                .then(r => r.json())
                .then(data => {
                    if (data.error) { nilaiContainer.innerHTML = `<p class="state-msg error-col">${data.error}</p>`; return; }
                    currentAllowedTypes = data.allowed_types || ['tugas1','uh1','tugas2','uh2','uts','uas'];
                    currentBobot = data.bobot || null;
                    document.getElementById('filterControls').style.display = 'flex';
                    renderTable(data, kelas);
                })
                .catch(() => { nilaiContainer.innerHTML = '<p class="state-msg error-col">Gagal memuat data.</p>'; });
        }

        document.getElementById('semesterFilter').addEventListener('change', function() {
            if (kelasSelect.value) {
                loadNilai(kelasSelect.value);
            }
        });

        function renderTable(data, kelas) {
            const siswaList = data.siswa || [];
            const mapelList = data.mapel_list || [];
            const allowedTypes = data.allowed_types || ['tugas1','uh1','tugas2','uh2','uts','uas'];
            const typeStatus = data.type_status || {};
            const activeSemester = data.active_semester || 'Genap';
            const visibleCols = ALL_COLUMNS.filter(c => allowedTypes.includes(c.key));

            if (!siswaList.length) { nilaiContainer.innerHTML = '<p class="state-msg">Tidak ada siswa di kelas ini.</p>'; return; }
            if (!mapelList.length) { nilaiContainer.innerHTML = '<p class="state-msg">Anda tidak mengajar mapel apapun di kelas ini.</p>'; return; }

            const semLabel = activeSemester === 'Ganjil' ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)';
            infoBar.textContent = `Kelas: ${kelas} · Mata Pelajaran: ${mapelList.join(', ')} · ${siswaList.length} siswa · ${semLabel}`;
            infoBar.classList.add('visible');

            let html = '';
            
            // Render Tab Buttons
            html += '<div class="tab-container">';
            mapelList.forEach((mapel, idx) => {
                const activeClass = idx === 0 ? 'active' : '';
                html += `<button class="tab-btn ${activeClass}" onclick="switchMapelTab(${idx}, this)">
                    📚 ${escHtml(mapel)}
                </button>`;
            });
            const myLabs = data.my_labs || [];
            if (myLabs.length > 0) {
                html += `<button class="tab-btn" onclick="switchLabTab(this)">🔬 Nilai Laboratorium</button>`;
            }
            html += '</div>';

            // Render Tab Panels
            mapelList.forEach((mapel, idx) => {
                const activeClass = idx === 0 ? 'active' : '';
                html += `<div class="tab-panel ${activeClass}" id="panel-mapel-${idx}">
                    <div class="nilai-table-wrapper">
                    <table class="nilai-table" data-mapel="${escHtml(mapel)}" data-kelas="${escHtml(kelas)}">
                        <thead><tr>
                            <th style="text-align:left;width:200px;">Nama Siswa</th>`;
                visibleCols.forEach(c => { html += `<th class="col-period col-${c.key}" style="width:85px;">${escHtml(c.label)}</th>`; });
                html += `<th class="col-na" style="width:85px;">Nilai Akhir</th><th style="min-width:220px;">Nilai Deskriptif</th>
                        </tr></thead><tbody>`;

                siswaList.forEach(siswa => {
                    // Filter out mismatched religion subjects
                    if (mapel.startsWith('PEND. AGAMA')) {
                        const agama = (siswa.agama || '').toLowerCase();
                        const mapelLower = mapel.toLowerCase();
                        let matches = false;
                        if (agama === 'islam' && mapelLower.includes('islam')) matches = true;
                        else if (agama === 'kristen' && mapelLower.includes('kristen')) matches = true;
                        else if (agama === 'katolik' && mapelLower.includes('katolik')) matches = true;
                        else if (agama === 'hindu' && mapelLower.includes('hindu')) matches = true;
                        else if (agama === 'budha' && mapelLower.includes('buddha')) matches = true;
                        else if (agama === 'buddha' && mapelLower.includes('buddha')) matches = true;
                        else if (agama === 'konghucu' && mapelLower.includes('konghucu')) matches = true;
                        
                        if (!matches) return; // Skip this student for this religion mapel table
                    }

                    const md = (siswa.mapel || []).find(m => m.nama === mapel) || {};
                    const na = md.nilai_akhir, desk = md.nilai_deskriptif || '';
                    html += `<tr data-siswa-id="${siswa.siswa_id}">
                        <td class="td-nama">${escHtml(siswa.nama)}</td>`;
                    visibleCols.forEach(c => {
                        const val = md[c.key] ?? '';
                        const isOpen = typeStatus[c.key] !== false;
                        const styleTitle = !isOpen ? 'title="Periode ditutup"' : '';
                        const styleBg = !isOpen ? 'background:#eee;' : '';
                        html += `<td class="col-period col-${c.key}" style="text-align:center;" ${styleTitle}><input type="number" class="grade-input" data-field="${c.key}" data-is-open="${isOpen}" min="0" max="100" value="${escHtml(String(val))}" placeholder="-" disabled style="${styleBg}"></td>`;
                    });
                    html += `<td class="col-na ${na!==null&&na!==undefined?'nilai-akhir-cell':'nilai-akhir-cell empty'}" data-na-cell="1">${na!==null&&na!==undefined?na:'-'}</td>
                        <td><input type="text" class="deskriptif-input" data-field="nilai_deskriptif" value="${escHtml(desk)}" placeholder="Catatan deskriptif..." disabled></td>
                    </tr>`;
                });
                html += `</tbody></table></div></div>`;
            });

            // Render Lab Tab Panel
            if (myLabs.length > 0) {
                html += `<div class="tab-panel" id="panel-lab">
                    <div class="nilai-table-wrapper">
                    <table class="nilai-table" id="labTable">
                        <thead><tr>
                            <th style="text-align:left;width:200px;">Nama Siswa</th>
                            <th style="width:140px;">Lab Fisika</th>
                            <th style="width:140px;">Lab Kimia</th>
                            <th style="width:140px;">Lab Biologi</th>
                        </tr></thead>
                        <tbody>`;
                siswaList.forEach(siswa => {
                    const lab = siswa.lab || {};
                    const fVal = lab.FISIKA !== null && lab.FISIKA !== undefined ? lab.FISIKA : '';
                    const kVal = lab.KIMIA !== null && lab.KIMIA !== undefined ? lab.KIMIA : '';
                    const bVal = lab.BIOLOGI !== null && lab.BIOLOGI !== undefined ? lab.BIOLOGI : '';

                    const fEditable = myLabs.includes('FISIKA');
                    const kEditable = myLabs.includes('KIMIA');
                    const bEditable = myLabs.includes('BIOLOGI');

                    const fInput = fEditable 
                        ? `<input type="number" class="lab-grade-input" data-siswa-id="${siswa.siswa_id}" data-mapel="FISIKA" min="0" max="100" value="${fVal}" placeholder="-" disabled style="width:70px;text-align:center;padding:4px;border:1px solid #ccc;border-radius:4px;">`
                        : `<span class="lab-readonly-cell ${fVal===''?'empty':''}">${fVal===''?'—':fVal}</span>`;

                    const kInput = kEditable 
                        ? `<input type="number" class="lab-grade-input" data-siswa-id="${siswa.siswa_id}" data-mapel="KIMIA" min="0" max="100" value="${kVal}" placeholder="-" disabled style="width:70px;text-align:center;padding:4px;border:1px solid #ccc;border-radius:4px;">`
                        : `<span class="lab-readonly-cell ${kVal===''?'empty':''}">${kVal===''?'—':kVal}</span>`;

                    const bInput = bEditable 
                        ? `<input type="number" class="lab-grade-input" data-siswa-id="${siswa.siswa_id}" data-mapel="BIOLOGI" min="0" max="100" value="${bVal}" placeholder="-" disabled style="width:70px;text-align:center;padding:4px;border:1px solid #ccc;border-radius:4px;">`
                        : `<span class="lab-readonly-cell ${bVal===''?'empty':''}">${bVal===''?'—':bVal}</span>`;

                    html += `<tr data-siswa-id="${siswa.siswa_id}">
                        <td class="td-nama">${escHtml(siswa.nama)}</td>
                        <td style="text-align:center;">${fInput}</td>
                        <td style="text-align:center;">${kInput}</td>
                        <td style="text-align:center;">${bInput}</td>
                    </tr>`;
                });
                html += `</tbody></table></div></div>`;
            }

            nilaiContainer.innerHTML = html;
            nilaiContainer.querySelectorAll('.grade-input').forEach(inp => {
                inp.addEventListener('input', () => recalcRow(inp.closest('tr')));
            });

            // Show edit button now that data is loaded
            document.getElementById('btnEditAll').style.display = 'inline-flex';
        }

        function recalcRow(tr) {
            const vals = {};
            let allFilled = true;
            
            currentAllowedTypes.forEach(f => {
                const inp = tr.querySelector(`[data-field="${f}"]`);
                const v = inp ? inp.value.trim() : '';
                if (v !== '') {
                    vals[f] = parseFloat(v);
                } else {
                    vals[f] = null;
                    allFilled = false;
                }
            });

            const naCell = tr.querySelector('[data-na-cell]');
            
            if (allFilled && currentBobot) {
                let naRaw = 0;
                let totalWeight = 0;
                currentAllowedTypes.forEach(f => {
                    const weight = parseFloat(currentBobot[f]) || 0;
                    naRaw += vals[f] * (weight / 100);
                    totalWeight += weight;
                });
                
                if (totalWeight > 0 && totalWeight < 100) {
                    naRaw = naRaw / (totalWeight / 100);
                }
                
                naCell.textContent = Math.ceil(naRaw);
                naCell.className = 'nilai-akhir-cell';
            } else {
                naCell.textContent = '-';
                naCell.className = 'nilai-akhir-cell empty';
            }
        }

        // ── Bulk Edit Mode ──
        const btnEditAll = document.getElementById('btnEditAll');
        const btnSaveAll = document.getElementById('btnSaveAll');
        const btnCancelAll = document.getElementById('btnCancelAll');
        const statusToast = document.getElementById('statusToast');

        // Store original values for cancel
        let originalValues = [];

        function storeOriginalValues() {
            originalValues = [];
            nilaiContainer.querySelectorAll('tbody tr').forEach(tr => {
                const row = { siswaId: tr.dataset.siswaId, fields: {} };
                tr.querySelectorAll('input').forEach(inp => {
                    const key = inp.dataset.field || inp.dataset.mapel;
                    row.fields[key] = inp.value;
                });
                originalValues.push(row);
            });
        }

        function enterEditMode() {
            storeOriginalValues();
            nilaiContainer.querySelectorAll('tbody tr').forEach(tr => {
                tr.querySelectorAll('input').forEach(inp => {
                    if (inp.classList.contains('grade-input')) {
                        if (inp.getAttribute('data-is-open') === 'true') inp.disabled = false;
                    } else {
                        inp.disabled = false;
                    }
                });
            });
            btnEditAll.style.display = 'none';
            btnSaveAll.style.display = 'inline-flex';
            btnCancelAll.style.display = 'inline-flex';
        }

        function exitEditMode() {
            nilaiContainer.querySelectorAll('tbody tr input').forEach(inp => inp.disabled = true);
            btnEditAll.style.display = 'inline-flex';
            btnSaveAll.style.display = 'none';
            btnCancelAll.style.display = 'none';
        }

        function showToast(msg, isError) {
            statusToast.textContent = msg;
            statusToast.className = 'status-toast' + (isError ? ' error' : '');
            statusToast.style.display = 'block';
            setTimeout(() => statusToast.style.display = 'none', 3000);
        }

        btnEditAll.addEventListener('click', enterEditMode);

        btnCancelAll.addEventListener('click', () => {
            // Restore original values
            nilaiContainer.querySelectorAll('tbody tr').forEach((tr, idx) => {
                if (originalValues[idx]) {
                    tr.querySelectorAll('input').forEach(inp => {
                        const key = inp.dataset.field || inp.dataset.mapel;
                        const orig = originalValues[idx].fields[key];
                        if (orig !== undefined) inp.value = orig;
                    });
                }
            });
            exitEditMode();
        });

        btnSaveAll.addEventListener('click', async () => {
            btnSaveAll.disabled = true;
            btnSaveAll.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Menyimpan...';

            const tables = nilaiContainer.querySelectorAll('.nilai-table');
            let successCount = 0, errorCount = 0;

            for (const table of tables) {
                if (table.id === 'labTable') continue;

                const kelas = table.dataset.kelas;
                const mapel = table.dataset.mapel;
                const rows = table.querySelectorAll('tbody tr');

                for (const tr of rows) {
                    const siswaId = tr.dataset.siswaId;
                    const getVal = f => {
                        const inp = tr.querySelector(`[data-field="${f}"]`);
                        if (!inp) return null;
                        const v = inp.value.trim();
                        return v !== '' ? parseInt(v) : null;
                    };
                    const payload = {
                        siswa_id: siswaId, mata_pelajaran: mapel, kelas,
                        semester: document.getElementById('semesterFilter').value,
                        nilai_deskriptif: tr.querySelector('[data-field="nilai_deskriptif"]').value.trim(),
                    };
                    currentAllowedTypes.forEach(type => { payload[type] = getVal(type); });

                    try {
                        const res = await fetch(NILAI_POST_API, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(payload),
                        }).then(r => r.json());

                        if (res.success) {
                            successCount++;
                            const naCell = tr.querySelector('[data-na-cell]');
                            if (res.nilai_akhir !== null && res.nilai_akhir !== undefined) {
                                naCell.textContent = res.nilai_akhir;
                                naCell.className = 'nilai-akhir-cell';
                            }
                        } else { errorCount++; }
                    } catch { errorCount++; }
                }
            }

            // Save Lab Grades
            const labInputs = nilaiContainer.querySelectorAll('.lab-grade-input');
            for (const inp of labInputs) {
                if (inp.disabled) continue;

                const siswaId = inp.dataset.siswaId;
                const mapel = inp.dataset.mapel;
                const val = inp.value.trim();
                const value = val !== '' ? parseInt(val) : null;

                try {
                    const res = await fetch('{{ route("guru.api.nilai.lab.post") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            siswa_id: siswaId,
                            mata_pelajaran: mapel,
                            value: value
                        }),
                    }).then(r => r.json());

                    if (res.success) {
                        successCount++;
                    } else {
                        errorCount++;
                    }
                } catch {
                    errorCount++;
                }
            }

            btnSaveAll.disabled = false;
            btnSaveAll.innerHTML = '<i class="bx bx-save"></i> Simpan';
            exitEditMode();

            if (errorCount === 0) {
                showToast(`✅ ${successCount} data berhasil disimpan!`, false);
            } else {
                showToast(`⚠️ ${successCount} berhasil, ${errorCount} gagal.`, true);
            }
        });

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    })();
</script>
@endsection
