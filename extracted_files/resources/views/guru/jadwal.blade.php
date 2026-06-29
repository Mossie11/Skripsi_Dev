@extends('layouts.app')
@section('page_title', 'Jadwal Mengajar')
@section('styles')
<style>
    .page-header { font-size:1.4rem; font-weight:bold; color:#0C447C; margin-bottom:1.5rem; padding-bottom:.75rem; border-bottom:1px solid #dce8f5; display:flex; justify-content:space-between; align-items:center; }
    .kelas-select-wrapper { display:flex; align-items:center; gap:12px; margin-bottom:1.5rem; }
    .kelas-select-label { color:#378ADD; font-size:.95rem; white-space:nowrap; }
    .kelas-select { padding:.5rem .8rem; background:#E6F1FB; border:1px solid #555; color:#0C447C; border-radius:6px; font-size:.95rem; cursor:pointer; min-width:140px; }
    .sched-table-wrapper { overflow-x:auto; }
    .sched-table { width:100%; border-collapse:collapse; font-size:.82rem; min-width:600px; }
    .sched-table th, .sched-table td { border:1px solid #dce8f5; padding:8px 10px; text-align:center; color:#1a2a3a; }
    .sched-table thead th { background:#0C447C !important; font-weight:700; color:#fff !important; text-transform:uppercase; letter-spacing:.04em; }
    .sched-table .col-jam { text-align:right; color:#378ADD; white-space:nowrap; }
    .sched-table .col-les { color:#fff; font-weight:bold; background:#0C447C; }
    .sched-table .break-row td { background:#e0edf8; color:#378ADD; font-style:italic; letter-spacing:.1em; font-size:.78rem; padding:5px; }
    .sched-table .has-subj { background:#e0edf8; color:#0C447C; border-left:3px solid #EF9F27; font-weight:600; white-space:nowrap; }
    .mapel-color-1 { background: #eaf3fc !important; border-left: 3px solid #378ADD !important; color: #0C447C !important; font-weight: 600; white-space: nowrap; text-align: left; }
    .mapel-color-3 { background: #fdf0ef !important; border-left: 3px solid #e74c3c !important; color: #c0392b !important; font-weight: 600; white-space: nowrap; text-align: left; }
    .mapel-color-2 { background: #edfaf3 !important; border-left: 3px solid #27ae60 !important; color: #1e8a5a !important; font-weight: 600; white-space: nowrap; text-align: left; }
    .mapel-color-0 { background: #fff8ed !important; border-left: 3px solid #EF9F27 !important; color: #b25e00 !important; font-weight: 600; white-space: nowrap; text-align: left; }
    .mapel-color-4 { background: #f7edfb !important; border-left: 3px solid #8e44ad !important; color: #703893 !important; font-weight: 600; white-space: nowrap; text-align: left; }
    .sched-empty { color:#7baada; text-align:center; padding:2rem; font-size:.9rem; }
    .mapel-badge { background:#EF9F27; color:#fff; padding:4px 16px; border-radius:20px; font-size:.9rem; font-weight:600; }
    .mapel-warn  { background:#e03535; color:#fff; padding:4px 16px; border-radius:20px; font-size:.85rem; }
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
    .header-meta { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; font-size: .85rem; font-weight: 600; background: #EF9F27; color: #fff; margin-left:10px; }
    .btn-change { display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 2px solid #dce8f5; color: #378ADD; padding: 6px 14px; border-radius: 8px; font-size: .85rem; font-weight: 600; cursor: pointer; transition: all .2s; text-decoration: none; margin-left: 10px; }
    .btn-change:hover { border-color: #378ADD; background: #f0f7ff; }
</style>
@endsection

@section('content')
<div style="background:#fff; border-radius:12px; padding:2rem; box-shadow:0 2px 12px rgba(12,68,124,.08);">
    
    <div class="modal-overlay" id="selectModal">
        <div class="modal-box">
            <div class="modal-icon"><i class='bx bx-calendar'></i></div>
            <h2>Jadwal Mengajar</h2>
            <p class="modal-subtitle">Pilih kelas untuk melihat jadwal mengajar Anda.</p>

            <div class="modal-field">
                <label for="jadwalKelasSelect"><i class='bx bx-building'></i> Kelas</label>
                <select id="jadwalKelasSelect">
                    <option value="">Memuat data kelas...</option>
                </select>
            </div>

            <button class="modal-btn primary" id="modalSubmitBtn" disabled>
                <i class='bx bx-search'></i> Tampilkan Jadwal
            </button>
        </div>
    </div>

    <div class="page-header" id="pageHeader" style="display:none;">
        <div>
            <span><i class='bx bx-calendar'></i> Jadwal Mengajar</span>
            @if($mapel)
                <span class="mapel-badge">📚 {{ $mapel }}</span>
            @else
                <span class="mapel-warn">⚠️ Mapel belum diset — <a href="{{ route('guru.profile') }}" style="color:#fff;text-decoration:underline;">Set di Profil</a></span>
            @endif
            <span class="header-meta" id="headerKelasLabel" style="display:none;"></span>
        </div>
        <button class="btn-change" onclick="window.location.reload()"><i class='bx bx-refresh'></i> Ganti Kelas</button>
    </div>

    <div id="jadwalTableContainer" style="display:none;">
        <p class="sched-empty">Memuat kelas...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const KELAS_API       = '{{ route("guru.api.kelas") }}';
    const JADWAL_KELAS_API= '{{ route("guru.api.jadwal-kelas") }}';

    (function() {
        const kelasSelect    = document.getElementById('jadwalKelasSelect');
        const tableContainer = document.getElementById('jadwalTableContainer');

        const TIME_SLOTS = [
            { jam:'07:30', end:'08:10', les:1 },
            { jam:'08:10', end:'08:50', les:2 },
            { jam:'08:50', end:'09:30', les:3 },
            { jam:'09:30', end:'09:50', les:null, isBreak:true, label:'ISTIRAHAT I' },
            { jam:'09:50', end:'10:30', les:4 },
            { jam:'10:30', end:'11:10', les:5 },
            { jam:'11:10', end:'11:25', les:null, isBreak:true, label:'ISTIRAHAT II' },
            { jam:'11:25', end:'12:05', les:6 },
            { jam:'12:05', end:'12:45', les:7 },
            { jam:'12:45', end:'13:15', les:null, isBreak:true, label:'ISTIRAHAT III' },
            { jam:'13:15', end:'13:55', les:8 },
            { jam:'13:55', end:'14:35', les:9 },
        ];
        const DAYS = ['Senin','Selasa','Rabu','Kamis','Jumat'];

        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const selectModal    = document.getElementById('selectModal');
        const pageHeader     = document.getElementById('pageHeader');
        const headerKelasLabel = document.getElementById('headerKelasLabel');

        fetch(KELAS_API).then(r => r.json()).then(kelas => {
            kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
            if(kelas.length === 0) {
                kelasSelect.innerHTML = '<option value="">(Tidak ada kelas)</option>';
                return;
            }
            kelas.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.kelas_id; opt.textContent = k.nama_kelas;
                kelasSelect.appendChild(opt);
            });
        }).catch(() => { 
            kelasSelect.innerHTML = '<option value="">Gagal memuat data</option>';
        });

        kelasSelect.addEventListener('change', function() {
            modalSubmitBtn.disabled = !this.value;
        });

        modalSubmitBtn.addEventListener('click', function() {
            const selectedKelasId = kelasSelect.value;
            if (!selectedKelasId) return;
            
            // Get text
            const selectedText = kelasSelect.options[kelasSelect.selectedIndex].text;
            headerKelasLabel.textContent = 'Kelas ' + selectedText;
            headerKelasLabel.style.display = 'inline-flex';
            
            // Hide modal, show UI
            selectModal.style.display = 'none';
            pageHeader.style.display = 'flex';
            tableContainer.style.display = 'block';
            
            loadSchedule(selectedKelasId);
        });

        function loadSchedule(kelas) {
            tableContainer.innerHTML = '<p class="sched-empty">Memuat jadwal...</p>';
            fetch(JADWAL_KELAS_API + '?kelas=' + encodeURIComponent(kelas))
                .then(r => r.json()).then(renderTable)
                .catch(() => { tableContainer.innerHTML = '<p class="sched-empty" style="color:#d44;">Gagal memuat jadwal.</p>'; });
        }

        function renderTable(data) {
            if (!data.length) { tableContainer.innerHTML = '<p class="sched-empty">Belum ada jadwal untuk kelas ini.</p>'; return; }
            const schedule = {};
            data.forEach(row => {
                if (!schedule[row.jam_mulai]) schedule[row.jam_mulai] = {};
                schedule[row.jam_mulai][row.hari] = row.mata_pelajaran;
            });

            let html = '<div class="sched-table-wrapper"><table class="sched-table"><thead><tr>';
            html += '<th rowspan="2">Jam</th><th rowspan="2">LES</th>';
            html += `<th colspan="${DAYS.length}">Hari</th></tr><tr>`;
            DAYS.forEach(d => html += `<th>${d.toUpperCase()}</th>`);
            html += '</tr></thead><tbody>';

        function getMapelColorClass(subj) {
            if (!subj) return '';
            const s = subj.toUpperCase();
            if (/MATEMATIKA|FISIKA|KIMIA|BIOLOGI|INFORMATIKA|TIK|EKONOMI|PROGRAMMING/.test(s)) {
                return 'mapel-color-1';
            } else if (/BAHASA|CONVERSATION|MANDARIN/.test(s)) {
                return 'mapel-color-3';
            } else if (/SEJARAH|GEOGRAFI|SOSIOLOGI|PKN|PANCASILA|AGAMA/.test(s)) {
                return 'mapel-color-2';
            } else if (/SENI|PRAKARYA|PENJAS|BASKET|FUTSAL|ORKES/.test(s)) {
                return 'mapel-color-0';
            } else {
                return 'mapel-color-4';
            }
        }

        TIME_SLOTS.forEach(slot => {
            if (slot.isBreak) { html += `<tr class="break-row"><td colspan="${DAYS.length + 2}">${slot.label}</td></tr>`; return; }
            const jamLabel = slot.jam + '–' + slot.end;
            const slotData = schedule[slot.jam] || {};
            html += '<tr>';
            html += `<td class="col-jam">${jamLabel}</td>`;
            html += `<td class="col-les">${slot.les}</td>`;
            DAYS.forEach(day => {
                const subj = slotData[day] || '';
                if (slot.les === 9 && ['Senin','Selasa','Rabu'].includes(day)) {
                    html += '<td style="color:#7baada;">-</td>';
                } else {
                    if (subj) {
                        const cls = getMapelColorClass(subj);
                        html += `<td class="${cls}">${subj.toUpperCase()}</td>`;
                    } else {
                        html += '<td style="color:#7baada;">-</td>';
                    }
                }
            });
            html += '</tr>';
            });
            html += '</tbody></table></div>';
            tableContainer.innerHTML = html;
        }
    })();
</script>
@endsection
