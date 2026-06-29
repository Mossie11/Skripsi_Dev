@extends('layouts.app')
@section('page_title', 'Dashboard Guru')
@section('styles')
<style>
    .guru-greeting { font-size:1.6rem; font-weight:bold; color:#0C447C; margin-bottom:.5rem; text-transform:uppercase; }
    .guru-subtitle  { font-size:1.1rem; color:#378ADD; margin-bottom:1.5rem; }
    .guru-dashboard-grid { display:flex; gap:24px; flex-wrap:wrap; }

    /* Calendar */
    .calendar-panel { flex:1; min-width:280px; background:#f0f7ff; border-radius:10px; padding:1.2rem; }
    .calendar-panel-title { font-size:.95rem; color:#378ADD; margin-bottom:1rem; }
    .calendar-nav { display:flex; justify-content:space-between; align-items:center; margin-bottom:.75rem; }
    .calendar-nav button { background:none; border:none; color:#1a3a5a; font-size:1.2rem; cursor:pointer; padding:4px 8px; }
    .calendar-month-label { font-size:1rem; font-weight:600; color:#0C447C; }
    .calendar-table { width:100%; border-collapse:collapse; text-align:center; }
    .calendar-table th { color:#7baada; font-weight:600; font-size:.85rem; padding:6px 0; }
    .calendar-table td { padding:8px 0; font-size:.9rem; cursor:pointer; border-radius:50%; color:#1a2a3a; }
    .calendar-table td:hover { background:#E6F1FB; }
    .calendar-table td.today { background:#378ADD; color:#fff; border-radius:50%; font-weight:bold; }
    .calendar-table td.other-month { color:#c0d8f0; }
    .calendar-table td.selected { background:#0C447C; color:#fff; border-radius:50%; }
    .calendar-table td.has-jadwal { font-weight:800; color:#1a3a5a; position:relative; }
    .calendar-table td.has-jadwal::after { content:''; position:absolute; bottom:4px; left:50%; transform:translateX(-50%); width:5px; height:5px; background:#378ADD; border-radius:50%; }
    .calendar-table td.today.has-jadwal, .calendar-table td.selected.has-jadwal { color:#fff; }
    .calendar-table td.today.has-jadwal::after, .calendar-table td.selected.has-jadwal::after { background:#fff; }

    /* Jadwal Panel */
    .jadwal-panel { flex:1; min-width:260px; }
    .jadwal-heading { font-size:1.1rem; font-weight:bold; color:#0C447C; margin-bottom:.75rem; }
    .jadwal-empty { color:#7baada; font-size:.95rem; }
    .jadwal-date-label { font-size:.82rem; color:#6a9bc0; margin-bottom:.75rem; }
    .jadwal-item { background:#f0f7ff; border-left:3px solid #007bff; border-radius:4px; padding:.6rem .9rem; margin-bottom:.6rem; }
    .jadwal-item .ji-jam   { font-size:.82rem; color:#6a9bc0; margin-bottom:2px; }
    .jadwal-item .ji-kelas { font-weight:bold; font-size:.95rem; color:#0C447C; }
    .jadwal-item .ji-mapel { font-size:.88rem; color:#378ADD; }

    /* Daftar Kelas */
    .daftar-kelas-section { margin-top:2rem; }
    .daftar-kelas-title { font-size:1.1rem; font-weight:bold; color:#0C447C; margin-bottom:1rem; padding-bottom:.5rem; border-bottom:1px solid #dce8f5; }
    .daftar-kelas-grid { display:flex; flex-wrap:wrap; gap:12px; }
    .kelas-card { background:#0C447C; border-radius:8px; padding:1rem 1.4rem; min-width:160px; }
    .kelas-card .kc-nama  { font-size:1.1rem; font-weight:bold; color:#fff; margin-bottom:8px; }
    .kelas-card .kc-mapel-list { display:flex; flex-wrap:wrap; gap:4px; }
    .kelas-card .kc-mapel-tag { background:rgba(255,255,255,.15); color:#b8d8f8; padding:2px 8px; border-radius:4px; font-size:.72rem; font-weight:600; }

    /* mapel badge */
    .mapel-badge { background:#EF9F27; color:#fff; padding:4px 16px; border-radius:20px; font-size:.9rem; font-weight:600; }
    .mapel-warn  { background:#e03535; color:#fff; padding:4px 16px; border-radius:20px; font-size:.85rem; }
</style>
@endsection

@section('content')
<div style="background:#fff; border-radius:12px; padding:2rem; box-shadow:0 2px 12px rgba(12,68,124,.08);">

    <div class="guru-greeting">Hi, {{ strtoupper($user->nama ?? 'Guru') }}</div>
    <div class="guru-subtitle">Jadwal Mengajar Hari Ini</div>

    <div class="guru-dashboard-grid">
        {{-- Calendar --}}
        <div class="calendar-panel">
            <div class="calendar-panel-title">Pilih salah satu tanggal</div>
            <div class="calendar-nav">
                <button id="calPrev">&#10094;</button>
                <span class="calendar-month-label" id="calMonthLabel"></span>
                <button id="calNext">&#10095;</button>
            </div>
            <table class="calendar-table">
                <thead>
                    <tr><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th><th>Su</th></tr>
                </thead>
                <tbody id="calBody"></tbody>
            </table>
        </div>

        {{-- Jadwal Panel --}}
        <div class="jadwal-panel">
            <div class="jadwal-heading">
                Jadwal Mengajar
                @if($user->mapel ?? null)
                    <span class="mapel-badge" style="margin-left:10px;font-size:.8rem;">📚 {{ $user->mapel }}</span>
                @else
                    <span class="mapel-warn" style="margin-left:10px;font-size:.8rem;">⚠️ Mapel belum diset</span>
                @endif
            </div>
            <div class="jadwal-date-label" id="jadwalDateLabel"></div>
            <div id="jadwalContent"><span class="jadwal-empty">Tidak ada jadwal</span></div>
        </div>
    </div>

    {{-- Daftar Kelas --}}
    <div class="daftar-kelas-section">
        <div class="daftar-kelas-title">Daftar Kelas</div>
        <div class="daftar-kelas-grid" id="daftarKelasGrid">
            <span style="color:#7baada;font-size:.9rem;">Memuat...</span>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var GURU_JADWAL_DAYS = @json($jadwalHari);
    const JADWAL_API          = '{{ route("guru.api.jadwal") }}';
    const KELAS_API           = '{{ route("guru.api.kelas") }}';

    (function() {
        const calBody      = document.getElementById('calBody');
        const calMonthLbl  = document.getElementById('calMonthLabel');
        const calPrev      = document.getElementById('calPrev');
        const calNext      = document.getElementById('calNext');
        const jadwalCont   = document.getElementById('jadwalContent');
        const jadwalDateLbl= document.getElementById('jadwalDateLabel');
        const daftarGrid   = document.getElementById('daftarKelasGrid');

        const monthNames  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const monthNamesId= ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const dayNamesId  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        const today = new Date();
        let curMonth = today.getMonth(), curYear = today.getFullYear();
        let selDate  = { y: today.getFullYear(), m: today.getMonth(), d: today.getDate() };

        function pad(n) { return String(n).padStart(2,'0'); }

        function renderCalendar(month, year) {
            calBody.innerHTML = '';
            calMonthLbl.textContent = monthNames[month] + ' ' + year;
            let firstDay = new Date(year, month, 1).getDay();
            firstDay = firstDay === 0 ? 6 : firstDay - 1;
            const daysInMonth = new Date(year, month+1, 0).getDate();
            const daysInPrev  = new Date(year, month, 0).getDate();
            let date = 1, nextDate = 1;

            for (let row = 0; row < 6; row++) {
                const tr = document.createElement('tr');
                let hasContent = false;
                for (let col = 0; col < 7; col++) {
                    const td = document.createElement('td');
                    const idx = row * 7 + col;
                    if (idx < firstDay) {
                        td.textContent = daysInPrev - firstDay + idx + 1;
                        td.classList.add('other-month');
                    } else if (date > daysInMonth) {
                        td.textContent = nextDate++;
                        td.classList.add('other-month');
                    } else {
                        const d = date;
                        td.textContent = d;
                        const jsDay = new Date(year, month, d).getDay();
                        const mapHari = {1:'senin',2:'selasa',3:'rabu',4:'kamis',5:'jumat',6:'sabtu',0:'minggu'};
                        if (GURU_JADWAL_DAYS && GURU_JADWAL_DAYS.includes(mapHari[jsDay])) {
                            td.classList.add('has-jadwal');
                        } else {
                            td.style.cursor = 'default';
                        }
                        if (d === today.getDate() && month === today.getMonth() && year === today.getFullYear())
                            td.classList.add('today');
                        if (d === selDate.d && month === selDate.m && year === selDate.y)
                            td.classList.add('selected');
                        td.addEventListener('click', () => {
                            selDate = { y: year, m: month, d };
                            renderCalendar(curMonth, curYear);
                            fetchJadwal(year, month, d);
                        });
                        date++; hasContent = true;
                    }
                    tr.appendChild(td);
                }
                calBody.appendChild(tr);
                if (date > daysInMonth && !hasContent) break;
            }
        }

        function fetchJadwal(year, month, day) {
            const dateStr = year + '-' + pad(month+1) + '-' + pad(day);
            const dateObj = new Date(year, month, day);
            jadwalDateLbl.textContent = dayNamesId[dateObj.getDay()] + ', ' + day + ' ' + monthNamesId[month] + ' ' + year;
            jadwalCont.innerHTML = '<span class="jadwal-empty">Memuat...</span>';
            fetch(JADWAL_API + '?date=' + dateStr)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { jadwalCont.innerHTML = '<span class="jadwal-empty">Tidak ada jadwal</span>'; return; }
                    jadwalCont.innerHTML = data.map(j => `
                        <div class="jadwal-item">
                            <div class="ji-jam">${j.jam_mulai} – ${j.jam_selesai}</div>
                            <div class="ji-kelas">${j.kelas}</div>
                            <div class="ji-mapel">${j.mata_pelajaran}</div>
                        </div>`).join('');
                })
                .catch(() => { jadwalCont.innerHTML = '<span class="jadwal-empty">Gagal memuat jadwal</span>'; });
        }

        function loadDaftarKelas() {
            fetch(KELAS_API)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { daftarGrid.innerHTML = '<span style="color:#7baada;font-size:.9rem;">Belum ada kelas terdaftar</span>'; return; }
                    daftarGrid.innerHTML = data.map(k => {
                        const mapelTags = (k.mapel || []).map(m => `<span class="kc-mapel-tag">${m}</span>`).join('');
                        return `
                        <div class="kelas-card">
                            <div class="kc-nama">${k.nama_kelas}</div>
                            <div class="kc-mapel-list">${mapelTags || '<span style="color:#6a9bc0;font-size:.8rem;">-</span>'}</div>
                        </div>`;
                    }).join('');
                })
                .catch(() => { daftarGrid.innerHTML = '<span style="color:#7baada;">Gagal memuat kelas</span>'; });
        }

        calPrev.addEventListener('click', () => { curMonth--; if (curMonth < 0) { curMonth = 11; curYear--; } renderCalendar(curMonth, curYear); });
        calNext.addEventListener('click', () => { curMonth++; if (curMonth > 11) { curMonth = 0; curYear++; } renderCalendar(curMonth, curYear); });

        renderCalendar(curMonth, curYear);
        fetchJadwal(today.getFullYear(), today.getMonth(), today.getDate());
        loadDaftarKelas();
    })();
</script>
@endsection
