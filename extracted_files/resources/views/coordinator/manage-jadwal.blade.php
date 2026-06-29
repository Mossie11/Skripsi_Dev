@extends('layouts.app')
@section('page_title', 'Manage Jadwal')
@section('styles')
    @include('coordinator.partials.manage-styles')
    <style>
        /* ── Filter Bar ─────────────────────────── */
        .jadwal-filters {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem 1.2rem;
            background: linear-gradient(135deg, #E6F1FB 0%, #f0f6fd 100%);
            border-radius: 10px;
            border: 1px solid #d0e3f5;
        }

        .jadwal-filters label {
            font-weight: 700;
            color: #0C447C;
            font-size: .92rem;
            white-space: nowrap;
        }

        .jadwal-filters select {
            padding: .55rem .9rem;
            background: #fff;
            border: 1px solid #aac5e0;
            color: #0C447C;
            border-radius: 6px;
            font-size: .92rem;
            cursor: pointer;
            min-width: 160px;
        }



        .jadwal-count {
            color: #378ADD;
            font-size: .88rem;
            font-weight: 600;
        }

        /* ── Timetable Grid ─────────────────────── */
        .timetable-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(12, 68, 124, .08);
        }

        .timetable {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
            min-width: 700px;
            background: #fff;
        }

        .timetable th {
            background: #0C447C !important;
            color: #fff !important;
            padding: 10px 12px;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            border: 1px solid #1a5a9a;
        }

        .timetable td {
            border: 1px solid #dce8f5;
            padding: 0;
            text-align: center;
            vertical-align: top;
            height: 52px;
            position: relative;
            color: #1a2a3a;
        }

        .timetable .col-jam {
            background: #f5f9fe;
            color: #378ADD;
            font-weight: 600;
            white-space: nowrap;
            text-align: right;
            padding: 8px 12px;
            font-size: .8rem;
            min-width: 100px;
        }

        .timetable .col-les {
            background: #0C447C;
            color: #fff;
            font-weight: 700;
            width: 40px;
            padding: 8px 6px;
        }

        .timetable .break-row td {
            background: linear-gradient(90deg, #e0edf8, #f0f6fd);
            color: #378ADD;
            font-style: italic;
            letter-spacing: .12em;
            font-size: .78rem;
            padding: 6px;
            text-align: center;
            font-weight: 600;
            border-color: #d0e3f5;
        }

        /* ── Subject cells ──────────────────────── */
        .subj-cell {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 5px 6px;
            height: 100%;
            min-height: 48px;
            transition: background .15s;
        }

        .subj-cell:hover {
            background: #f0f6fd;
        }

        .subj-cell .subj-name {
            font-weight: 700;
            color: #0C447C;
            font-size: .78rem;
            line-height: 1.2;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .subj-cell .subj-guru {
            font-size: .68rem;
            color: #7baada;
            line-height: 1.1;
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .empty-cell {
            color: #c5d8ed;
            font-size: .75rem;
            padding: 8px;
        }

        /* ── Color palette for subjects ─────────── */
        .subj-cell[data-color="0"] {
            border-left: 3px solid #EF9F27;
            background: #fff8ed;
        }

        .subj-cell[data-color="1"] {
            border-left: 3px solid #378ADD;
            background: #eaf3fc;
        }

        .subj-cell[data-color="2"] {
            border-left: 3px solid #27ae60;
            background: #edfaf3;
        }

        .subj-cell[data-color="3"] {
            border-left: 3px solid #e74c3c;
            background: #fdf0ef;
        }

        .subj-cell[data-color="4"] {
            border-left: 3px solid #8e44ad;
            background: #f7edfb;
        }

        .subj-cell[data-color="5"] {
            border-left: 3px solid #16a085;
            background: #e8f8f5;
        }

        .subj-cell[data-color="6"] {
            border-left: 3px solid #d35400;
            background: #fdf2e9;
        }

        .subj-cell[data-color="7"] {
            border-left: 3px solid #2c3e50;
            background: #eef2f5;
        }

        .subj-cell[data-color="8"] {
            border-left: 3px solid #c0392b;
            background: #fbeae9;
        }

        .subj-cell[data-color="9"] {
            border-left: 3px solid #1abc9c;
            background: #e8faf6;
        }

        .subj-cell[data-color="10"] {
            border-left: 3px solid #f39c12;
            background: #fef9e7;
        }

        .subj-cell[data-color="11"] {
            border-left: 3px solid #3498db;
            background: #ebf5fb;
        }

        .subj-cell[data-color="12"] {
            border-left: 3px solid #9b59b6;
            background: #f4ecf7;
        }

        .subj-cell[data-color="13"] {
            border-left: 3px solid #e67e22;
            background: #fef5e7;
        }

        .subj-cell[data-color="14"] {
            border-left: 3px solid #2ecc71;
            background: #eafaf1;
        }

        .subj-cell[data-color="15"] {
            border-left: 3px solid #e91e63;
            background: #fce4ec;
        }

        .subj-cell[data-color="16"] {
            border-left: 3px solid #00bcd4;
            background: #e0f7fa;
        }

        .subj-cell[data-color="17"] {
            border-left: 3px solid #795548;
            background: #efebe9;
        }

        .subj-cell[data-color="18"] {
            border-left: 3px solid #607d8b;
            background: #eceff1;
        }

        .subj-cell[data-color="19"] {
            border-left: 3px solid #ff5722;
            background: #fbe9e7;
        }

        /* ── No filter prompt ──────────────────── */
        .select-kelas-prompt {
            text-align: center;
            padding: 3rem 2rem;
            color: #7baada;
            font-size: 1rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(12, 68, 124, .08);
        }

        .select-kelas-prompt i {
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
            color: #d0e3f5;
        }

        /* ── Selection Modal ──────────────────── */
        .kelas-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(12, 68, 124, 0.45);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: kfadeIn .25s ease;
        }

        @keyframes kfadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .kelas-modal-box {
            background: #fff;
            border-radius: 16px;
            padding: 36px 40px;
            width: 420px;
            max-width: 92vw;
            box-shadow: 0 20px 60px rgba(12, 68, 124, 0.25);
            animation: kslideUp .3s ease;
        }

        @keyframes kslideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .kelas-modal-box .km-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0C447C 0%, #378ADD 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #fff;
            margin: 0 auto 20px;
        }

        .kelas-modal-box h2 {
            text-align: center;
            font-size: 1.35rem;
            font-weight: 700;
            color: #0C447C;
            margin: 0 0 6px;
        }

        .kelas-modal-box .km-subtitle {
            text-align: center;
            font-size: .88rem;
            color: #6a9bc0;
            margin: 0 0 28px;
        }

        .kelas-modal-box .km-field {
            margin-bottom: 20px;
        }

        .kelas-modal-box .km-field label {
            display: block;
            font-size: .82rem;
            font-weight: 700;
            color: #0C447C;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .kelas-modal-box .km-field select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #dce8f5;
            border-radius: 10px;
            font-size: .95rem;
            color: #1a2a3a;
            background: #f8fbff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            cursor: pointer;
            appearance: auto;
        }

        .kelas-modal-box .km-field select:focus {
            border-color: #378ADD;
            box-shadow: 0 0 0 3px rgba(55, 138, 221, 0.15);
        }

        .km-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all .2s;
            margin-top: 8px;
            background: linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%);
            color: #fff;
        }

        .km-btn:hover {
            background: linear-gradient(135deg, #0d3e6e 0%, #155085 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(12, 68, 124, .2);
        }

        .km-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .km-btn-alt {
            width: 100%;
            padding: 12px;
            border: 2px solid #dce8f5;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #fff;
            color: #378ADD;
            margin-top: 10px;
            transition: all .2s;
        }

        .km-btn-alt:hover {
            border-color: #378ADD;
            background: #f0f7ff;
        }

        /* ── Edit mode timetable ─────────────── */
        #timetableView.edit-mode .subj-cell {
            cursor: pointer;
            position: relative;
        }

        #timetableView.edit-mode .subj-cell::after {
            content: '✏️ Edit';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(12, 68, 124, .85);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: .85rem;
            opacity: 0;
            transition: opacity .2s;
            border-radius: 4px;
        }

        #timetableView.edit-mode .subj-cell:hover::after {
            opacity: 1;
        }

        /* ── List view adjustments ─────────────── */
        #listView {
            display: none;
        }

        #listView.active {
            display: block;
        }

        #timetableView.active {
            display: block;
        }

        #timetableView {
            display: none;
        }

        /* ── Print-only header (hidden on screen) ── */
        .print-header {
            display: none;
        }

        /* ── Print Styles ──────────────────────── */
        @media print {
            @page {
                size: landscape;
                margin: 12mm 10mm;
            }

            /* Hide all UI chrome */
            .modern-sidebar,
            .navbar,
            .jadwal-filters,
            .header .actions,
            .kelas-modal-overlay,
            .modal-overlay,
            #batchUpdateForm,
            .select-kelas-prompt {
                display: none !important;
            }

            body {
                padding-left: 0 !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .content-container {
                padding: 0 !important;
                margin: 0 !important;
            }

            .container-manage {
                box-shadow: none !important;
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                border-radius: 0 !important;
            }

            .header {
                display: none !important;
            }

            /* Show print header */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 16px;
                padding-bottom: 12px;
                border-bottom: 3px double #0C447C;
            }

            .print-header .school-name {
                font-size: 18pt;
                font-weight: 700;
                color: #0C447C;
                margin: 0;
                letter-spacing: 1px;
            }

            .print-header .print-title {
                font-size: 13pt;
                font-weight: 600;
                color: #333;
                margin: 4px 0 2px;
            }

            .print-header .print-kelas {
                font-size: 11pt;
                color: #555;
                margin: 0;
            }

            /* Timetable print adjustments */
            #timetableView {
                display: block !important;
            }

            .timetable-wrapper {
                box-shadow: none !important;
                overflow: visible !important;
                border-radius: 0 !important;
            }

            .timetable {
                font-size: 9pt !important;
                min-width: unset !important;
                width: 100% !important;
            }

            .timetable th {
                background: #0C447C !important;
                color: #fff !important;
                padding: 6px 8px !important;
                font-size: 8pt !important;
            }

            .timetable td {
                border: 1px solid #999 !important;
                height: auto !important;
            }

            .timetable .col-jam {
                background: #f0f0f0 !important;
                font-size: 8pt !important;
                padding: 4px 8px !important;
            }

            .timetable .col-les {
                background: #0C447C !important;
                color: #fff !important;
                padding: 4px !important;
            }

            .break-row td {
                background: #e8e8e8 !important;
                padding: 4px !important;
                font-size: 8pt !important;
            }

            .subj-cell {
                padding: 3px 4px !important;
                min-height: 36px !important;
            }

            .subj-cell .subj-name {
                font-size: 7.5pt !important;
                color: #000 !important;
            }

            .subj-cell .subj-guru {
                font-size: 6.5pt !important;
                color: #444 !important;
                max-width: none !important;
                white-space: normal !important;
                overflow: visible !important;
            }

            /* Keep color borders for print */
            .subj-cell[data-color] {
                background: #fff !important;
            }

            .empty-cell {
                color: #ccc !important;
            }

            /* Hide edit-mode overlays */
            #timetableView.edit-mode .subj-cell::after {
                display: none !important;
            }
        }
    </style>
@endsection

@section('content')
    {{-- ═══ KELAS SELECTION MODAL ═══ --}}
    @if(!$filterKelas)
        <div class="kelas-modal-overlay" id="kelasSelectModal">
            <div class="kelas-modal-box">
                <div class="km-icon"><i class='bx bx-calendar'></i></div>
                <h2>Jadwal Pelajaran</h2>
                <p class="km-subtitle">Pilih kelas untuk melihat & mengelola jadwal</p>

                <form method="GET" action="{{ route('coordinator.manage-jadwal') }}" id="kelasModalForm">
                    <div class="km-field">
                        <label><i class='bx bx-building'></i> Kelas</label>
                        <select name="filter_kelas" id="modalFilterKelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $id => $namaKelas)
                                <option value="{{ $id }}">{{ $namaKelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="km-btn" id="modalKelasBtn" disabled>
                        <i class='bx bx-right-arrow-alt'></i> Tampilkan Jadwal
                    </button>
                </form>

            </div>
        </div>
    @endif

    <div class="container-manage">
        {{-- Print-only header --}}
        <div class="print-header">
            <p class="school-name">WR2School</p>
            <p class="print-title">Jadwal Pelajaran</p>
            @if($filterKelas)
                <p class="print-kelas">Kelas: {{ $kelasList[$filterKelas] ?? '-' }}</p>
            @endif
        </div>

        <div class="header">
            <h2><i class='bx bx-calendar'></i> Jadwal Pelajaran</h2>
            <div class="actions">
                <button class="btn btn-create" id="batchSaveBtn" onclick="submitBatchUpdates()"
                    style="margin-right:8px;background:#1e8a5a;display:none;">💾 Simpan Semua</button>
                <button class="btn btn-create" id="cancelEditBtn" onclick="cancelEditMode()"
                    style="margin-right:8px;background:#e03535;display:none;">❌ Cancel</button>
                <button class="btn btn-create" id="editToggleBtn" onclick="toggleEditMode()"
                    style="margin-right:8px;background:#358ae0;">✏️ Edit</button>
                @if($filterKelas)
                    <button class="btn btn-create" onclick="window.print()" style="margin-right:8px;background:#0C447C;"><i
                            class='bx bx-printer'></i> Print</button>
                @endif
                <button class="btn btn-create" onclick="openModal('createModal')">+ CREATE</button>
            </div>
        </div>

        <!-- ── Filters ──────────────────────────── -->
        <div class="jadwal-filters">
            <label><i class='bx bx-filter-alt'></i> Kelas:</label>
            <form id="filterForm" method="GET" action="{{ route('coordinator.manage-jadwal') }}"
                style="display:flex;gap:10px;align-items:center;">
                <select name="filter_kelas" id="filterKelas" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $id => $namaKelas)
                        <option value="{{ $id }}" @if($filterKelas == $id) selected @endif>{{ $namaKelas }}</option>
                    @endforeach
                </select>
            </form>
            @if($filterKelas)
                <span class="jadwal-count">{{ $displayedJadwalCount }} jadwal</span>
            @endif

        </div>

        <!-- ── Timetable View ───────────────────── -->
        <div id="timetableView" class="active">
            @if($filterKelas)
                <div class="timetable-wrapper">
                    <table class="timetable">
                        <thead>
                            <tr>
                                <th style="min-width:100px;">JAM</th>
                                <th style="width:40px;">LES</th>
                                @foreach($days as $day)
                                    <th>{{ strtoupper($day) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slot)
                                @if(isset($slot['break']))
                                    <tr class="break-row">
                                        <td colspan="{{ count($days) + 2 }}">{{ $slot['break'] }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="col-jam">{{ $slot['jam'] }}–{{ $slot['end'] }}</td>
                                        <td class="col-les">{{ $slot['les'] }}</td>
                                        @foreach($days as $day)
                                            @php
                                                $cell = $jadwalMap[$slot['jam']][$day] ?? null;
                                                $colorIdx = 7;
                                                if ($cell) {
                                                    $subj = strtoupper($cell->mata_pelajaran);
                                                    if (preg_match('/MATEMATIKA|FISIKA|KIMIA|BIOLOGI|INFORMATIKA|TIK|EKONOMI|PROGRAMMING/', $subj)) {
                                                        $colorIdx = 1; // Blue (Eksak/Berhitung)
                                                    } elseif (preg_match('/BAHASA|CONVERSATION|MANDARIN/', $subj)) {
                                                        $colorIdx = 3; // Red (Bahasa)
                                                    } elseif (preg_match('/SEJARAH|GEOGRAFI|SOSIOLOGI|PKN|PANCASILA|AGAMA/', $subj)) {
                                                        $colorIdx = 2; // Green (Sosial & Humaniora)
                                                    } elseif (preg_match('/SENI|PRAKARYA|PENJAS|BASKET|FUTSAL|ORKES/', $subj)) {
                                                        $colorIdx = 0; // Orange (Seni & Olahraga)
                                                    } else {
                                                        $colorIdx = 4; // Purple (Lainnya)
                                                    }
                                                }
                                            @endphp
                                            <td>
                                                @if($cell)
                                                    <div class="subj-cell" data-color="{{ $colorIdx % 20 }}" title="{{ $cell->guru_nama }}"
                                                        onclick="if(editMode) openEditJadwal({{ json_encode($cell) }}, this)">
                                                        <span class="subj-name">{{ $cell->mata_pelajaran }}</span>
                                                        <span class="subj-guru">{{ $cell->guru_nama }}</span>
                                                    </div>
                                                @else
                                                    @php
                                                        $emptyMock = [
                                                            'id' => '',
                                                            'kelas_id' => $filterKelas,
                                                            'hari' => $day,
                                                            'jam_mulai' => $slot['jam'] . ':00',
                                                            'jam_selesai' => $slot['end'] . ':00',
                                                            'guru_id' => '',
                                                            'mata_pelajaran_id' => '',
                                                        ];
                                                    @endphp
                                                    <div class="subj-cell empty-cell-clickable"
                                                        onclick="if(editMode) openEditJadwal({{ json_encode($emptyMock) }}, this)"
                                                        style="background:transparent; border:none;">
                                                        <span class="empty-cell">–</span>
                                                        <span class="subj-name" style="display:none;"></span>
                                                        <span class="subj-guru" style="display:none;"></span>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="select-kelas-prompt">
                    <i class='bx bx-calendar-event'></i>
                    <p>Pilih kelas untuk melihat jadwal dalam format timetable.</p>
                </div>
            @endif
        </div>
    </div>
    <!-- ── Create Modal ──────────────────────────── -->
    <div class="modal-overlay" id="createModal">
        <div class="create-modal-content" style="max-width:600px;">
            <button class="modal-close" onclick="closeModal('createModal')">×</button>
            <h2>Tambah Jadwal</h2>
            <form method="POST" action="{{ route('coordinator.manage-jadwal.action') }}">
                @csrf
                <input type="hidden" name="action" value="create">
                <div class="create-form-grid">
                    <div class="form-group"><label>Kelas *</label>
                        <select name="kelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $id => $namaKelas)<option value="{{ $id }}" @if($filterKelas == $id)
                            selected @endif>{{ $namaKelas }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Guru *</label>
                        <select name="guru_id" required id="guruSelect">
                            <option value="">-- Pilih Guru --</option>
                            @foreach($guruList as $g)<option value="{{ $g->id }}" data-mapel="{{ $g->mapel ?? '' }}">
                                {{ $g->nama }} ({{ $g->mapel ?? '-' }})
                            </option>@endforeach
                        </select>
                    </div>
                    <div class="form-group full-width"><label>Mata Pelajaran *</label>
                        <select name="mapel" required id="mapelSelect">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapelList as $id => $namaMapel)<option value="{{ $id }}">{{ $namaMapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Hari *</label>
                        <select name="hari" required>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Jam Mulai *</label><input type="time" name="jam_mulai" required></div>
                    <div class="form-group"><label>Jam Selesai *</label><input type="time" name="jam_selesai" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn" onclick="return confirm('Tambah jadwal?')">Tambah Jadwal</button>
            </form>
        </div>
    </div>

    <!-- ── Edit Modal ──────────────────────────── -->
    <div class="modal-overlay" id="editModal">
        <div class="create-modal-content" style="max-width:600px;">
            <button class="modal-close" onclick="closeModal('editModal')">×</button>
            <h2>Edit Jadwal</h2>
            <form id="editLocalForm">
                <input type="hidden" name="id" id="editId">
                <div class="create-form-grid">
                    <div class="form-group"><label>Kelas *</label>
                        <select name="kelas" required id="editKelas">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $id => $namaKelas)<option value="{{ $id }}">{{ $namaKelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Guru *</label>
                        <select name="guru_id" required id="editGuruSelect">
                            <option value="">-- Pilih Guru --</option>
                            @foreach($guruList as $g)<option value="{{ $g->id }}" data-mapel="{{ $g->mapel ?? '' }}">
                                {{ $g->nama }} ({{ $g->mapel ?? '-' }})
                            </option>@endforeach
                        </select>
                    </div>
                    <div class="form-group full-width"><label>Mata Pelajaran *</label>
                        <select name="mapel" required id="editMapelSelect">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapelList as $id => $namaMapel)<option value="{{ $id }}">{{ $namaMapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Hari *</label>
                        <select name="hari" required id="editHari">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Jam Mulai *</label><input type="time" name="jam_mulai" id="editJamMulai"
                            required></div>
                    <div class="form-group"><label>Jam Selesai *</label><input type="time" name="jam_selesai"
                            id="editJamSelesai" required></div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" class="submit-btn" style="margin-top: 0; flex: 1;"
                        onclick="applyLocalEdit()">Oke</button>
                    <button type="button" class="submit-btn" id="btnHapusJadwal"
                        style="margin-top: 0; flex: 1; background: linear-gradient(135deg, #e03535 0%, #c82333 100%); box-shadow: 0 4px 12px rgba(224, 53, 53, 0.2); display: none;"
                        onclick="applyLocalDelete()">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <form id="batchUpdateForm" method="POST" action="{{ route('coordinator.manage-jadwal.action') }}" style="display:none;">
        @csrf
        <input type="hidden" name="action" value="batch_update">
        <input type="hidden" name="updates" id="batchUpdatesData">
    </form>
@endsection

@section('scripts')
    @include('coordinator.partials.manage-scripts')
    <script>


        function filterDropdowns(guruSel, mapelSel, trigger) {
            const guruVal = guruSel.value;
            const mapelVal = mapelSel.value;

            if (trigger === 'guru') {
                const selectedGuru = guruSel.options[guruSel.selectedIndex];
                const mapels = (selectedGuru && selectedGuru.getAttribute('data-mapel')) ? selectedGuru.getAttribute('data-mapel').split(',').map(s => s.trim().toUpperCase()) : [];

                for (let i = 1; i < mapelSel.options.length; i++) {
                    const opt = mapelSel.options[i];
                    const mapelName = opt.text.toUpperCase();
                    if (guruVal === '' || mapels.includes(mapelName)) {
                        opt.hidden = false;
                        opt.style.display = '';
                        opt.disabled = false;
                    } else {
                        opt.hidden = true;
                        opt.style.display = 'none';
                        opt.disabled = true;
                    }
                }

                const currentMapelName = mapelSel.options[mapelSel.selectedIndex] ? mapelSel.options[mapelSel.selectedIndex].text.toUpperCase() : '';
                if (guruVal !== '' && !mapels.includes(currentMapelName)) {
                    for (let i = 1; i < mapelSel.options.length; i++) {
                        if (!mapelSel.options[i].hidden && mapelSel.options[i].value !== '') {
                            mapelSel.selectedIndex = i;
                            break;
                        }
                    }
                }
            } else if (trigger === 'mapel') {
                const selectedMapelName = mapelSel.options[mapelSel.selectedIndex] ? mapelSel.options[mapelSel.selectedIndex].text.toUpperCase() : '';

                for (let i = 1; i < guruSel.options.length; i++) {
                    const opt = guruSel.options[i];
                    const guruMapels = (opt.getAttribute('data-mapel') || '').split(',').map(s => s.trim().toUpperCase());

                    if (mapelVal === '' || guruMapels.includes(selectedMapelName)) {
                        opt.hidden = false;
                        opt.style.display = '';
                        opt.disabled = false;
                    } else {
                        opt.hidden = true;
                        opt.style.display = 'none';
                        opt.disabled = true;
                    }
                }

                const currentGuruMapels = (guruSel.options[guruSel.selectedIndex] ? (guruSel.options[guruSel.selectedIndex].getAttribute('data-mapel') || '') : '').split(',').map(s => s.trim().toUpperCase());
                if (mapelVal !== '' && !currentGuruMapels.includes(selectedMapelName)) {
                    for (let i = 1; i < guruSel.options.length; i++) {
                        if (!guruSel.options[i].hidden && guruSel.options[i].value !== '') {
                            guruSel.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const createGuruSel = document.getElementById('guruSelect');
            const createMapelSel = document.getElementById('mapelSelect');
            if (createGuruSel && createMapelSel) {
                createGuruSel.addEventListener('change', () => filterDropdowns(createGuruSel, createMapelSel, 'guru'));
                createMapelSel.addEventListener('change', () => filterDropdowns(createGuruSel, createMapelSel, 'mapel'));
            }

            const editGuruSel = document.getElementById('editGuruSelect');
            const editMapelSel = document.getElementById('editMapelSelect');
            if (editGuruSel && editMapelSel) {
                editGuruSel.addEventListener('change', () => filterDropdowns(editGuruSel, editMapelSel, 'guru'));
                editMapelSel.addEventListener('change', () => filterDropdowns(editGuruSel, editMapelSel, 'mapel'));
            }
        });

        window.pendingUpdates = {};
        window.currentEditCell = null;

        function openEditJadwal(j, el = null) {
            window.currentEditCell = el;
            document.getElementById('editId').value = j.id;
            document.getElementById('editKelas').value = j.kelas_id;

            // Set guru
            const guruSel = document.getElementById('editGuruSelect');
            for (let i = 0; i < guruSel.options.length; i++) {
                guruSel.options[i].hidden = false;
                guruSel.options[i].style.display = '';
                guruSel.options[i].disabled = false;
                if (guruSel.options[i].value == j.guru_id) {
                    guruSel.selectedIndex = i;
                }
            }

            const mapelSel = document.getElementById('editMapelSelect');
            for (let i = 0; i < mapelSel.options.length; i++) {
                mapelSel.options[i].hidden = false;
                mapelSel.options[i].style.display = '';
                mapelSel.options[i].disabled = false;
            }
            mapelSel.value = j.mata_pelajaran_id;

            document.getElementById('editHari').value = j.hari;

            // The time fields might include seconds 'HH:MM:SS', browsers prefer 'HH:MM'
            document.getElementById('editJamMulai').value = j.jam_mulai.substring(0, 5);
            document.getElementById('editJamSelesai').value = j.jam_selesai.substring(0, 5);

            if (guruSel.value) {
                filterDropdowns(guruSel, mapelSel, 'guru');
                mapelSel.value = j.mata_pelajaran_id; // restore in case filtering deselected it
            } else if (mapelSel.value) {
                filterDropdowns(guruSel, mapelSel, 'mapel');
                guruSel.value = j.guru_id;
            }

            const btnHapus = document.getElementById('btnHapusJadwal');
            if (btnHapus) {
                if (j.id && !j.id.toString().startsWith('new_')) {
                    btnHapus.style.display = 'inline-block';
                } else {
                    btnHapus.style.display = 'none';
                }
            }

            openModal('editModal');
        }

        function applyLocalDelete() {
            const id = document.getElementById('editId').value;
            if (!id) return;

            if (confirm('Yakin ingin menghapus jadwal ini?')) {
                window.pendingUpdates[id] = {
                    id: id,
                    is_delete: true
                };

                if (window.currentEditCell) {
                    if (window.currentEditCell.tagName === 'TR') {
                        window.currentEditCell.style.display = 'none';
                    } else {
                        window.currentEditCell.removeAttribute('data-color');
                        window.currentEditCell.style.borderLeft = 'none';
                        window.currentEditCell.style.background = 'transparent';
                        window.currentEditCell.style.boxShadow = 'inset 0 0 0 2px #e03535';

                        const subjName = window.currentEditCell.querySelector('.subj-name');
                        const subjGuru = window.currentEditCell.querySelector('.subj-guru');
                        const emptySpan = window.currentEditCell.querySelector('.empty-cell');

                        if (subjName) {
                            subjName.textContent = '';
                            subjName.style.display = 'none';
                        }
                        if (subjGuru) {
                            subjGuru.textContent = '';
                            subjGuru.style.display = 'none';
                        }
                        if (emptySpan) {
                            emptySpan.textContent = '–';
                            emptySpan.style.display = 'inline';
                        }
                    }

                    document.getElementById('batchSaveBtn').style.display = 'inline-block';
                }

                closeModal('editModal');
            }
        }

        function applyLocalEdit() {
            // Get values
            let id = document.getElementById('editId').value;
            let isNew = false;
            if (!id) {
                id = 'new_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                isNew = true;
            }

            const kelasId = document.getElementById('editKelas').value;
            const guruSel = document.getElementById('editGuruSelect');
            const guruId = guruSel.value;
            const guruNama = guruSel.options[guruSel.selectedIndex] ? guruSel.options[guruSel.selectedIndex].text.split(' (')[0] : '-';
            const mapelSel = document.getElementById('editMapelSelect');
            const mapelId = mapelSel.value;
            const mapelNama = mapelSel.options[mapelSel.selectedIndex] ? mapelSel.options[mapelSel.selectedIndex].text : '-';
            const hari = document.getElementById('editHari').value;
            const jamMulai = document.getElementById('editJamMulai').value;
            const jamSelesai = document.getElementById('editJamSelesai').value;

            // Basic validation
            if (!kelasId || !guruId || !mapelId || !hari || !jamMulai || !jamSelesai) {
                alert('Silakan lengkapi semua data wajib!');
                return;
            }

            // Save to pendingUpdates
            window.pendingUpdates[id] = {
                id: id,
                is_new: isNew,
                kelas_id: kelasId,
                guru_id: guruId,
                mata_pelajaran_id: mapelId,
                hari: hari,
                jam_mulai: jamMulai,
                jam_selesai: jamSelesai
            };

            // Update UI (Optimistic update)
            if (window.currentEditCell) {
                if (window.currentEditCell.tagName === 'TR') {
                    // Update list view row
                    const tds = window.currentEditCell.querySelectorAll('td');
                    if (tds.length >= 7) {
                        tds[2].textContent = mapelNama;
                        tds[3].textContent = guruNama;
                        tds[4].textContent = hari;
                        tds[5].textContent = jamMulai;
                        tds[6].textContent = jamSelesai;
                    }
                    window.currentEditCell.style.background = '#fff8ed'; // Highlight edited row
                    window.currentEditCell.style.borderLeft = '3px solid #EF9F27';
                } else {
                    // Update timetable cell
                    const subjName = window.currentEditCell.querySelector('.subj-name');
                    const subjGuru = window.currentEditCell.querySelector('.subj-guru');
                    if (subjName) {
                        subjName.textContent = mapelNama;
                        subjName.style.display = 'block';
                    }
                    if (subjGuru) {
                        subjGuru.textContent = guruNama;
                        subjGuru.style.display = 'block';
                    }

                    const emptySpan = window.currentEditCell.querySelector('.empty-cell');
                    if (emptySpan) emptySpan.style.display = 'none';

                    window.currentEditCell.style.boxShadow = 'inset 0 0 0 2px #EF9F27';
                    window.currentEditCell.style.background = '#fff8ed';
                    window.currentEditCell.style.borderLeft = '3px solid #EF9F27';
                }

                document.getElementById('batchSaveBtn').style.display = 'inline-block';
            }

            closeModal('editModal');
        }

        function submitBatchUpdates() {
            if (Object.keys(window.pendingUpdates).length === 0) return;

            if (confirm('Simpan semua perubahan jadwal ke database?')) {
                document.getElementById('batchUpdatesData').value = JSON.stringify(Object.values(window.pendingUpdates));
                document.getElementById('batchUpdateForm').submit();
            }
        }

        let editMode = false;
        function toggleEditMode() {
            editMode = true;
            const btn = document.getElementById('editToggleBtn');
            const tt = document.getElementById('timetableView');

            if (tt) tt.classList.add('edit-mode');

            btn.style.display = 'none';
            document.getElementById('batchSaveBtn').style.display = 'inline-block';
            document.getElementById('cancelEditBtn').style.display = 'inline-block';
        }

        function cancelEditMode() {
            if (Object.keys(window.pendingUpdates).length > 0) {
                if (!confirm('Anda memiliki perubahan yang belum disimpan. Yakin ingin membatalkan?')) return;
            }
            window.location.reload();
        }

        // Modal kelas selection
        var modalFilterKelas = document.getElementById('modalFilterKelas');
        var modalKelasBtn = document.getElementById('modalKelasBtn');
        if (modalFilterKelas) {
            modalFilterKelas.addEventListener('change', function () {
                modalKelasBtn.disabled = !this.value;
            });
        }


    </script>
@endsection