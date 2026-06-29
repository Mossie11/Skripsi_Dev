<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rapor – Kelas {{ $kelas }} – {{ $jenis === 'uas' ? 'Rapor Akhir' : strtoupper($jenis) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif !important;
            background: #f5f5f5;
            color: #000 !important;
            font-size: 12px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
        }

        .no-print {
            background: #0C447C;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print-now {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-close {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 8px;
        }

        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @page landscape-page {
            size: A4 landscape;
            margin: 0;
        }

        .page {
            width: 210mm;
            height: 297mm;
            padding: 10mm 15mm;
            margin: 10px auto;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            position: relative;
            page-break-after: always;
            overflow: hidden;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .landscape-layout {
            page: landscape-page;
            width: 297mm;
            height: 210mm;
            padding: 8mm 12mm;
            margin: 10px auto;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            position: relative;
            page-break-after: always;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            box-sizing: border-box;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        @media print {
            .page {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .page:last-of-type {
                page-break-after: auto;
            }

            .landscape-layout {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .landscape-layout:last-of-type {
                page-break-after: auto;
            }
        }

        .landscape-left {
            width: 134mm;
            display: flex;
            flex-direction: column;
        }

        .landscape-right {
            width: 134mm;
            display: flex;
            flex-direction: column;
        }

        .landscape-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin: 4px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .landscape-layout table.grades-table th,
        .landscape-layout table.grades-table td {
            padding: 3.5px 4px !important;
            font-size: 9px !important;
            line-height: 1.15 !important;
        }

        .landscape-left table.grades-table th,
        .landscape-left table.grades-table td {
            padding: 3.8px 4px !important;
        }

        .landscape-left table.grades-table thead tr:last-child th {
            font-size: 8px !important;
        }

        .landscape-layout table.grades-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .landscape-layout .section-header {
            font-weight: bold;
            background-color: #e6e6e6;
            text-align: center;
        }

        /* HEADER SECTION */
        .header-section {
            margin-bottom: 6px;
            font-size: 12px;
            line-height: 1.5;
        }

        .header-row {
            display: table;
            width: 100%;
        }

        .header-col {
            display: table-cell;
            vertical-align: top;
            padding: 0;
        }

        .header-col-left {
            width: 60%;
            padding-right: 10px;
        }

        .header-col-right {
            width: 40%;
        }

        .header-item {
            display: flex;
            margin-bottom: 2px;
        }

        .header-label {
            width: 150px;
            font-weight: normal;
        }

        .header-colon {
            width: 12px;
            text-align: center;
        }

        .header-value {
            flex: 1;
            font-weight: bold;
        }

        .header-col-right .header-label {
            width: 100px;
        }

        /* TITLE */
        .page-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 6px 0;
            text-decoration: underline;
        }

        /* TABLE STYLES */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.grades-table {
            table-layout: fixed;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 6px 5px;
            text-align: left;
            font-size: 11px;
            line-height: 1.3;
        }

        table.grades-table th,
        table.grades-table td,
        table.data-table th,
        table.data-table td {
            padding: 11px 5px;
        }

        table th {
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: center;
        }

        .col-no {
            width: 30px;
            text-align: center;
        }

        .col-nilai {
            width: 70px;
            text-align: center;
            font-weight: bold;
        }

        .col-predikat {
            width: 70px;
            text-align: center;
        }

        /* SECTION HEADERS */
        .section-header {
            background-color: #fff;
            font-weight: bold;
            padding-left: 10px !important;
        }

        /* KKTP TABLE */
        .kktp-label {
            width: 100px;
            font-weight: bold;
            text-align: left;
        }

        .kktp-desc {
            text-align: left;
        }

        /* FLEX CONTAINER */
        .flex-container {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }

        .flex-col {
            flex: 1;
        }

        .flex-col-title {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .flex-col table {
            margin-bottom: 0;
        }

        .flex-col table td {
            border: 1px solid #000;
            padding: 4px 5px;
            font-size: 10px;
        }

        /* CATATAN BOX */
        .catatan-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .catatan-box {
            border: 2px solid #000;
            padding: 10px 15px;
            margin-bottom: 10px;
            min-height: 40px;
            text-align: center;
        }

        .catatan-isi {
            font-size: 12px;
            font-weight: bold;
            font-style: italic;
        }

        /* SIGNATURE SECTION */
        .ttd-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 11px;
        }

        .ttd-box {
            text-align: center;
            width: 32%;
        }

        .ttd-date {
            margin-bottom: 10px;
        }

        .ttd-name {
            margin-top: 35px;
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd-position {
            font-weight: bold;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .page {
                width: 100%;
                height: auto;
                margin: 5px 0;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <h2>🖨️ Preview Rapor – Kelas {{ $kelas }} ({{ $jenis === 'uas' ? 'Rapor Akhir' : strtoupper($jenis) }})</h2>
        <div>
            <button class="btn-print-now" onclick="window.print()">🖨️ Cetak</button>
            <button class="btn-close" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>

    @forelse($siswaList as $s)
        @php
            $sGrades = $grades[$s->id] ?? [];
            $sEkskul = $ekskulData[$s->id] ?? [];
            $sAbsen = $absensiData[$s->id] ?? [];
            $sLab = $labData[$s->id] ?? [];
            $sKepribadian = $kepribadianData[$s->id] ?? [];

            $sakit = $sAbsen['Sakit'] ?? '-';
            $izin = $sAbsen['Izin'] ?? '-';
            $alpha = $sAbsen['Alpha'] ?? '-';
            if ($sakit !== '-')
                $sakit .= ' hari';
            if ($izin !== '-')
                $izin .= ' hari';
            if ($alpha !== '-')
                $alpha .= ' hari';

            // Map jenis to header label
            $jenisLabel = [
                'uh1' => 'Nilai UH 1',
                'uh2' => 'Nilai UH 2',
                'uts' => 'Nilai UTS',
                'uas' => 'Nilai Rapor Akhir',
                'tugas1' => 'Nilai Tugas 1',
                'tugas2' => 'Nilai Tugas 2'
            ];
            $nilaiHeader = $jenisLabel[$jenis] ?? 'Nilai Akhir';

            // Split subjects: 14 per page (fits perfectly now with the reduced 8px padding)
            $page1Count = 14;
            $page1Subjects = array_slice($subjects, 0, $page1Count);
            $page2Subjects = array_slice($subjects, $page1Count);
        @endphp

        @if(in_array($jenis, ['uh1', 'uts', 'uh2']))
            <!-- NEW LANDSCAPE TEMPLATE FOR UH1, UTS, UH2 -->
            <div class="landscape-layout">
                <!-- LEFT PAGE -->
                <div class="landscape-left">
                    <!-- HEADER -->
                    <div class="header-section" style="font-size: 10px; margin-bottom: 5px;">
                        <div style="display: flex; justify-content: space-between;">
                            <div style="width: 60%;">
                                <table style="border: none; margin: 0; width: 100%;">
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px; width: 100px;">Nama Peserta Didik</td>
                                        <td style="border: none; padding: 1px; width: 10px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">{{ $s->nama }}</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">NIS/NISN</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">
                                            {{ $s->nis ?: '-' }}/{{ $s->nisn ?: '-' }}
                                        </td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Nama Sekolah</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">SMAS W.R.SUPRATMAN 2</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Alamat</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold; font-size: 8.5px;">Jln.
                                            Brigjend Zein Hamid No. 33 Medan</td>
                                    </tr>
                                </table>
                            </div>
                            <div style="width: 38%;">
                                <table style="border: none; margin: 0; width: 100%;">
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px; width: 80px;">Kelas</td>
                                        <td style="border: none; padding: 1px; width: 10px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">{{ $kelas }}</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Fase</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">E</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Semester</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">
                                            {{ $activeSemester == 'Ganjil' ? '1' : '2' }}
                                        </td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Tahun Pelajaran</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">{{ $tahun }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="landscape-title">LAPORAN HASIL BELAJAR PESERTA DIDIK</div>

                    <!-- GRADES TABLE -->
                    <table class="grades-table">
                        <colgroup>
                            <col style="width: 20px;">
                            <col style="width: 130px;">
                            <col style="width: 55px;">
                            <col style="width: 55px;">
                            <col style="width: 55px;">
                            <col style="width: 55px;">
                            <col style="width: 55px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th rowspan="2" style="vertical-align: middle;">No</th>
                                <th rowspan="2" style="vertical-align: middle; text-align: left;">Mata Pelajaran</th>
                                <th colspan="2">Sumatif I/III (UH1)</th>
                                <th>Sumatif Tengah Semester (UTS)</th>
                                <th colspan="2">Sumatif II/IV (UH2)</th>
                            </tr>
                            <tr>
                                <th>Pengetahuan</th>
                                <th>Praktek Baik</th>
                                <th>Pengetahuan</th>
                                <th>Pengetahuan</th>
                                <th>Praktek Baik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="section-header">Kelompok Mata Pelajaran Umum</td>
                            </tr>
                            @foreach($subjects as $idx => $subj)
                                @php
                                    $subjGrades = $sGrades[$subj] ?? [];

                                    $uh1Val = isset($subjGrades['uh1']) ? $subjGrades['uh1']->score : null;
                                    $tugas1Val = isset($subjGrades['tugas1']) ? $subjGrades['tugas1']->score : null;
                                    $utsVal = isset($subjGrades['uts']) ? $subjGrades['uts']->score : null;
                                    $uh2Val = isset($subjGrades['uh2']) ? $subjGrades['uh2']->score : null;
                                    $tugas2Val = isset($subjGrades['tugas2']) ? $subjGrades['tugas2']->score : null;

                                    // Clean subject display name for matching reference
                                    $displaySubj = $subj;
                                    if ($subj === 'BAHASA & SASTRA INGGRIS (CONVERSATION)' || $subj === 'CONVERSATION') {
                                        $displaySubj = 'English Conversation';
                                    } elseif ($subj === 'BAHASA MANDARIN') {
                                        $displaySubj = 'Bahasa Mandarin';
                                    } elseif ($subj === 'PRAKARYA & KEWIRAUSAHAAN') {
                                        $displaySubj = 'Prakarya dan Kewirausahaan';
                                    } elseif ($subj === 'SENI MUSIK / SENI BUDAYA') {
                                        $displaySubj = 'Seni Budaya';
                                    } elseif ($subj === 'MATEMATIKA / MATEMATIKA WAJIB') {
                                        $displaySubj = 'Matematika';
                                    } elseif ($subj === 'PEND. PANCASILA / PKN') {
                                        $displaySubj = 'Pendidikan Pancasila';
                                    } elseif ($subj === 'SEJARAH / SEJARAH INDONESIA') {
                                        $displaySubj = 'Sejarah';
                                    } elseif ($subj === 'INFORMATIKA / TIK') {
                                        $displaySubj = 'Informatika';
                                    } elseif ($subj === 'PENJAS ORKES') {
                                        $displaySubj = 'Pendidikan Jasmani, Olahraga dan Kesehatan';
                                    } elseif ($subj === 'SEJARAH (Tingkat Lanjut)') {
                                        $displaySubj = 'Sejarah Tingkat Lanjut *';
                                    } elseif ($subj === 'MATEMATIKA (Tingkat Lanjut)') {
                                        $displaySubj = 'Matematika Tingkat Lanjut *';
                                    }
                                @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                                    <td>{{ $displaySubj }}</td>
                                    <td style="text-align: center;">{{ $uh1Val ?? '-' }}</td>
                                    <td style="text-align: center;">{{ $tugas1Val ?? '-' }}</td>
                                    <td style="text-align: center;">{{ $utsVal ?? '-' }}</td>
                                    <td style="text-align: center;">{{ $uh2Val ?? '-' }}</td>
                                    <td style="text-align: center;">{{ $tugas2Val ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="font-size: 8px; margin-top: 3px; font-style: italic;">
                        * Coret yang tidak perlu
                    </div>
                </div>

                <!-- RIGHT PAGE -->
                <div class="landscape-right">
                    <!-- HEADER -->
                    <div class="header-section" style="font-size: 10px; margin-bottom: 5px;">
                        <div style="display: flex; justify-content: space-between;">
                            <div style="width: 60%;">
                                <table style="border: none; margin: 0; width: 100%;">
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px; width: 100px;">Nama Peserta Didik</td>
                                        <td style="border: none; padding: 1px; width: 10px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">{{ $s->nama }}</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">NIS/NISN</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">
                                            {{ $s->nis ?: '-' }}/{{ $s->nisn ?: '-' }}
                                        </td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Nama Sekolah</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">SMAS W.R.SUPRATMAN 2</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Alamat</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold; font-size: 8.5px;">Jln.
                                            Brigjend Zein Hamid No. 33 Medan</td>
                                    </tr>
                                </table>
                            </div>
                            <div style="width: 38%;">
                                <table style="border: none; margin: 0; width: 100%;">
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px; width: 80px;">Kelas</td>
                                        <td style="border: none; padding: 1px; width: 10px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">{{ $kelas }}</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Fase</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">E</td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Semester</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">
                                            {{ $activeSemester == 'Ganjil' ? '1' : '2' }}
                                        </td>
                                    </tr>
                                    <tr style="border: none;">
                                        <td style="border: none; padding: 1px;">Tahun Pelajaran</td>
                                        <td style="border: none; padding: 1px;">:</td>
                                        <td style="border: none; padding: 1px; font-weight: bold;">{{ $tahun }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="landscape-title" style="visibility: hidden;">HEADER PLACEHOLDER</div>

                    <!-- ATTENDANCE & PERSONALITY TABLE -->
                    <table class="grades-table" style="margin-bottom: 8px;">
                        <colgroup>
                            <col style="width: 140px;">
                            <col style="width: 100px;">
                            <col style="width: 50px;">
                            <col style="width: 50px;">
                            <col style="width: 50px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th colspan="2">Laporan Ketidakhadiran & Kepribadian</th>
                                <th>Laporan 1</th>
                                <th>Laporan 2</th>
                                <th>Laporan 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $showLaporan1 = true;
                                $showLaporan2 = in_array($jenis, ['uts', 'uh2', 'uas']);
                                $showLaporan3 = in_array($jenis, ['uh2', 'uas']);

                                $absenUH1 = $absensiDataByPeriod[$s->id]['uh1'] ?? [];
                                $absenUTS = $absensiDataByPeriod[$s->id]['uts'] ?? [];
                                $absenUH2 = $absensiDataByPeriod[$s->id]['uh2'] ?? [];

                                $sakit1 = ($showLaporan1 && isset($absenUH1['Sakit'])) ? $absenUH1['Sakit'] . ' Hari' : '-';
                                $izin1 = ($showLaporan1 && isset($absenUH1['Izin'])) ? $absenUH1['Izin'] . ' Hari' : '-';
                                $alpha1 = ($showLaporan1 && isset($absenUH1['Alpha'])) ? $absenUH1['Alpha'] . ' Hari' : '-';

                                $sakit2 = ($showLaporan2 && isset($absenUTS['Sakit'])) ? $absenUTS['Sakit'] . ' Hari' : '-';
                                $izin2 = ($showLaporan2 && isset($absenUTS['Izin'])) ? $absenUTS['Izin'] . ' Hari' : '-';
                                $alpha2 = ($showLaporan2 && isset($absenUTS['Alpha'])) ? $absenUTS['Alpha'] . ' Hari' : '-';

                                $sakit3 = ($showLaporan3 && isset($absenUH2['Sakit'])) ? $absenUH2['Sakit'] . ' Hari' : '-';
                                $izin3 = ($showLaporan3 && isset($absenUH2['Izin'])) ? $absenUH2['Izin'] . ' Hari' : '-';
                                $alpha3 = ($showLaporan3 && isset($absenUH2['Alpha'])) ? $absenUH2['Alpha'] . ' Hari' : '-';
                            @endphp
                            <tr>
                                <td rowspan="3" style="vertical-align: middle; text-align: center; font-weight: bold;">
                                    Ketidakhadiran</td>
                                <td>Sakit</td>
                                <td style="text-align: center;">{{ $sakit1 }}</td>
                                <td style="text-align: center;">{{ $sakit2 }}</td>
                                <td style="text-align: center;">{{ $sakit3 }}</td>
                            </tr>
                            <tr>
                                <td>Izin</td>
                                <td style="text-align: center;">{{ $izin1 }}</td>
                                <td style="text-align: center;">{{ $izin2 }}</td>
                                <td style="text-align: center;">{{ $izin3 }}</td>
                            </tr>
                            <tr>
                                <td>Tanpa Keterangan</td>
                                <td style="text-align: center;">{{ $alpha1 }}</td>
                                <td style="text-align: center;">{{ $alpha2 }}</td>
                                <td style="text-align: center;">{{ $alpha3 }}</td>
                            </tr>
                            <tr>
                                <td rowspan="4" style="vertical-align: middle; text-align: center; font-weight: bold;">
                                    Kepribadian</td>
                                <td>Kelakuan</td>
                                <td style="text-align: center;">{{ $showLaporan1 ? ($sKepribadian['kepribadian_uh1']['Kelakuan'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan2 ? ($sKepribadian['kepribadian_uts']['Kelakuan'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan3 ? ($sKepribadian['kepribadian_uh2']['Kelakuan'] ?? '-') : '-' }}</td>
                            </tr>
                            <tr>
                                <td>Kerajinan</td>
                                <td style="text-align: center;">{{ $showLaporan1 ? ($sKepribadian['kepribadian_uh1']['Kerajinan'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan2 ? ($sKepribadian['kepribadian_uts']['Kerajinan'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan3 ? ($sKepribadian['kepribadian_uh2']['Kerajinan'] ?? '-') : '-' }}</td>
                            </tr>
                            <tr>
                                <td>Kerapian</td>
                                <td style="text-align: center;">{{ $showLaporan1 ? ($sKepribadian['kepribadian_uh1']['Kerapian'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan2 ? ($sKepribadian['kepribadian_uts']['Kerapian'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan3 ? ($sKepribadian['kepribadian_uh2']['Kerapian'] ?? '-') : '-' }}</td>
                            </tr>
                            <tr>
                                <td>Kedisiplinan</td>
                                <td style="text-align: center;">{{ $showLaporan1 ? ($sKepribadian['kepribadian_uh1']['Kedisiplinan'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan2 ? ($sKepribadian['kepribadian_uts']['Kedisiplinan'] ?? '-') : '-' }}</td>
                                <td style="text-align: center;">{{ $showLaporan3 ? ($sKepribadian['kepribadian_uh2']['Kedisiplinan'] ?? '-') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- LAPORAN SUMATIF BOXES -->
                    @php
                        $isUH1Active = ($jenis === 'uh1');
                        $isUTSActive = ($jenis === 'uts');
                        $isUH2Active = ($jenis === 'uh2');

                        $tahunRow = DB::table('tahun_ajaran')->where('nama', $tahun)->first();
                        $tahunId = $tahunRow ? $tahunRow->id : 1;
                        $activeSemesterNum = ($activeSemester === 'Ganjil') ? '1' : '2';
                        $activePeriod = DB::table('periode_nilai')->where('tahun_ajaran_id', $tahunId)->where('semester', $activeSemesterNum)->first();

                        $uh1Dates = '- s.d. -';
                        if ($activePeriod && $activePeriod->uh1_start && $activePeriod->uh1_end) {
                            $uh1Dates = \Carbon\Carbon::parse($activePeriod->uh1_start)->translatedFormat('d F Y') . ' s.d. ' . \Carbon\Carbon::parse($activePeriod->uh1_end)->translatedFormat('d F Y');
                        }

                        $utsDates = '- s.d. -';
                        if ($activePeriod && $activePeriod->uts_start && $activePeriod->uts_end) {
                            $utsDates = \Carbon\Carbon::parse($activePeriod->uts_start)->translatedFormat('d F Y') . ' s.d. ' . \Carbon\Carbon::parse($activePeriod->uts_end)->translatedFormat('d F Y');
                        }

                        $uh2Dates = '- s.d. -';
                        if ($activePeriod && $activePeriod->uh2_start && $activePeriod->uh2_end) {
                            $uh2Dates = \Carbon\Carbon::parse($activePeriod->uh2_start)->translatedFormat('d F Y') . ' s.d. ' . \Carbon\Carbon::parse($activePeriod->uh2_end)->translatedFormat('d F Y');
                        }
                    @endphp

                    <!-- Box Laporan Sumatif I / III -->
                    <div
                        style="border: 1px solid #000; margin-bottom: 6px; border-radius: 4px; overflow: hidden; background-color: {{ $isUH1Active ? '#fff' : '#f9f9f9' }};">
                        <div
                            style="background-color: #e6e6e6; padding: 2px 6px; font-weight: bold; font-size: 9.5px; border-bottom: 1px solid #000; display: flex; justify-content: space-between;">
                            <span>Laporan Sumatif I / III (UH1)</span>
                        </div>
                        <div style="padding: 4px 8px; font-size: 9px; line-height: 1.3;">
                            <div>Tanggal : <strong>{{ $uh1Dates }}</strong></div>
                            <div style="margin-top: 2px;">Pesan Wali Kelas : <span style="font-style: italic;">Tingkatkan
                                    Prestasimu</span></div>
                            <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 8px;">
                                <div style="text-align: center; width: 45%;">
                                    Wali Kelas,<br>
                                    <span style="visibility: hidden;">Sign</span>
                                    <div style="margin-top: 15px; font-weight: bold; text-decoration: underline;">
                                        {{ $s->wali_kelas_nama ?? $user->nama }}
                                    </div>
                                </div>
                                <div style="text-align: center; width: 45%;">
                                    Mengetahui,<br>Orang Tua / Wali Siswa
                                    <div
                                        style="margin-top: 30px; border-top: 0.5px solid #aaa; width: 80px; margin-left: auto; margin-right: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Box Laporan Sumatif Tengah Semester I / II -->
                    <div
                        style="border: 1px solid #000; margin-bottom: 6px; border-radius: 4px; overflow: hidden; background-color: {{ $isUTSActive ? '#fff' : '#f9f9f9' }};">
                        <div
                            style="background-color: #e6e6e6; padding: 2px 6px; font-weight: bold; font-size: 9.5px; border-bottom: 1px solid #000; display: flex; justify-content: space-between;">
                            <span>Laporan Sumatif Tengah Semester I / II (UTS)</span>
                        </div>
                        <div style="padding: 4px 8px; font-size: 9px; line-height: 1.3;">
                            <div>Tanggal : <strong>{{ $utsDates }}</strong></div>
                            <div style="margin-top: 2px;">Pesan Wali Kelas : <span style="font-style: italic;">Tingkatkan
                                    Prestasimu</span></div>
                            <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 8px;">
                                <div style="text-align: center; width: 45%;">
                                    Wali Kelas,<br>
                                    <span style="visibility: hidden;">Sign</span>
                                    <div style="margin-top: 15px; font-weight: bold; text-decoration: underline;">
                                        {{ $s->wali_kelas_nama ?? $user->nama }}
                                    </div>
                                </div>
                                <div style="text-align: center; width: 45%;">
                                    Mengetahui,<br>Orang Tua / Wali Siswa
                                    <div
                                        style="margin-top: 30px; border-top: 0.5px solid #aaa; width: 80px; margin-left: auto; margin-right: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Box Laporan Sumatif II / IV -->
                    <div
                        style="border: 1px solid #000; border-radius: 4px; overflow: hidden; background-color: {{ $isUH2Active ? '#fff' : '#f9f9f9' }};">
                        <div
                            style="background-color: #e6e6e6; padding: 2px 6px; font-weight: bold; font-size: 9.5px; border-bottom: 1px solid #000; display: flex; justify-content: space-between;">
                            <span>Laporan Sumatif II / IV (UH2)</span>
                        </div>
                        <div style="padding: 4px 8px; font-size: 9px; line-height: 1.3;">
                            <div>Tanggal : <strong>{{ $uh2Dates }}</strong></div>
                            <div style="margin-top: 2px;">Pesan Wali Kelas : <span style="font-style: italic;">Tingkatkan
                                    Prestasimu</span></div>
                            <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 8px;">
                                <div style="text-align: center; width: 45%;">
                                    Wali Kelas,<br>
                                    <span style="visibility: hidden;">Sign</span>
                                    <div style="margin-top: 15px; font-weight: bold; text-decoration: underline;">
                                        {{ $s->wali_kelas_nama ?? $user->nama }}
                                    </div>
                                </div>
                                <div style="text-align: center; width: 45%;">
                                    Mengetahui,<br>Orang Tua / Wali Siswa
                                    <div
                                        style="margin-top: 30px; border-top: 0.5px solid #aaa; width: 80px; margin-left: auto; margin-right: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else


            <!-- PAGE 1 -->
            <div class="page">
                <!-- HEADER -->
                <div class="header-section">
                    <div class="header-row">
                        <div class="header-col header-col-left">
                            <div class="header-item">
                                <div class="header-label">Nama Peserta Didik</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">{{ $s->nama }}</div>
                            </div>
                            <div class="header-item">
                                <div class="header-label">NIS/NISN</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">{{ $s->nis ?: '-' }}/{{ $s->nisn ?: '-' }}</div>
                            </div>
                            <div class="header-item">
                                <div class="header-label">Nama Sekolah</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">SMAS W.R.SUPRATMAN 2</div>
                            </div>
                            <div class="header-item">
                                <div class="header-label">Alamat</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">Jln. Brigjend Zein Hamid No. 33 Medan</div>
                            </div>
                        </div>
                        <div class="header-col header-col-right">
                            <div class="header-item">
                                <div class="header-label">Kelas</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">{{ $kelas }}</div>
                            </div>
                            <div class="header-item">
                                <div class="header-label">Fase</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">E</div>
                            </div>
                            <div class="header-item">
                                <div class="header-label">Semester</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">{{ $activeSemester == 'Ganjil' ? '1' : '2' }}</div>
                            </div>
                            <div class="header-item">
                                <div class="header-label">Tahun Pelajaran</div>
                                <div class="header-colon">:</div>
                                <div class="header-value">{{ $tahun }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TITLE -->
                <div class="page-title">LAPORAN HASIL BELAJAR PESERTA DIDIK</div>

                <!-- MATA PELAJARAN TABLE -->
                <table class="grades-table">
                    <colgroup>
                        <col style="width: 30px;">
                        <col style="width: 200px;">
                        <col style="width: 80px;">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Mata Pelajaran</th>
                            <th class="col-nilai">{{ $nilaiHeader }}</th>
                            <th>Capaian Kompetensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="section-header" style="text-align: center;">Kelompok Mata Pelajaran Umum</td>
                        </tr>
                        @foreach($page1Subjects as $idx => $subj)
                            @php
                                $g = $sGrades[$subj] ?? null;
                                $score = $g ? $g->score : null;
                                $desk = $g ? ($g->nilai_deskriptif ?? '') : '';
                            @endphp
                            <tr>
                                <td class="col-no">{{ $idx + 1 }}</td>
                                <td><strong>{{ $subj }}</strong></td>
                                <td class="col-nilai">{{ $score ?? '-' }}</td>
                                <td>{{ $desk ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- KKTP TABLE -->
                <div style="page-break-inside: avoid; break-inside: avoid; margin-top: 15px;">
                    <div style="margin-bottom: 8px; font-weight: bold; font-size: 11px;">KKTP (Kriteria Ketercapaian Tujuan Pembelajaran)</div>
                    <table style="width: 50%; font-weight: bold;">
                        <tbody>
                            <tr>
                                <td class="kktp-label">0-60</td>
                                <td class="kktp-desc">= Perlu Bimbingan</td>
                            </tr>
                            <tr>
                                <td class="kktp-label">61-70</td>
                                <td class="kktp-desc">= Cukup</td>
                            </tr>
                            <tr>
                                <td class="kktp-label">71-80</td>
                                <td class="kktp-desc">= Baik</td>
                            </tr>
                            <tr>
                                <td class="kktp-label">81-100</td>
                                <td class="kktp-desc">= Sangat Baik</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGE 2 (jika ada mata pelajaran lanjutan) -->
            @if(count($page2Subjects) > 0)
                <div class="page">
                    <!-- HEADER -->
                    <div class="header-section">
                        <div class="header-row">
                            <div class="header-col header-col-left">
                                <div class="header-item">
                                    <div class="header-label">Nama Peserta Didik</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">{{ $s->nama }}</div>
                                </div>
                                <div class="header-item">
                                    <div class="header-label">NIS/NISN</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">{{ $s->nis ?: '-' }}/{{ $s->nisn ?: '-' }}</div>
                                </div>
                                <div class="header-item">
                                    <div class="header-label">Nama Sekolah</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">SMAS W.R.SUPRATMAN 2</div>
                                </div>
                                <div class="header-item">
                                    <div class="header-label">Alamat</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">Jln. Brigjend Zein Hamid No. 33 Medan</div>
                                </div>
                            </div>
                            <div class="header-col header-col-right">
                                <div class="header-item">
                                    <div class="header-label">Kelas</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">{{ $kelas }}</div>
                                </div>
                                <div class="header-item">
                                    <div class="header-label">Fase</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">E</div>
                                </div>
                                <div class="header-item">
                                    <div class="header-label">Semester</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">{{ $activeSemester == 'Ganjil' ? '1' : '2' }}</div>
                                </div>
                                <div class="header-item">
                                    <div class="header-label">Tahun Pelajaran</div>
                                    <div class="header-colon">:</div>
                                    <div class="header-value">{{ $tahun }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TITLE -->
                    <div class="page-title">LAPORAN HASIL BELAJAR PESERTA DIDIK (Lanjutan)</div>

                    <!-- MATA PELAJARAN TABLE (Page 2) -->
                    <table class="grades-table">
                        <colgroup>
                            <col style="width: 30px;">
                            <col style="width: 200px;">
                            <col style="width: 80px;">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="col-no">No</th>
                                <th>Mata Pelajaran</th>
                                <th class="col-nilai">{{ $nilaiHeader }}</th>
                                <th>Capaian Kompetensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="section-header" style="text-align: center;">Kelompok Mata Pelajaran Umum</td>
                            </tr>
                            @foreach($page2Subjects as $idx => $subj)
                                @php
                                    $g = $sGrades[$subj] ?? null;
                                    $score = $g ? $g->score : null;
                                    $desk = $g ? ($g->nilai_deskriptif ?? '') : '';
                                @endphp
                                <tr>
                                    <td class="col-no">{{ $page1Count + $idx + 1 }}</td>
                                    <td><strong>{{ $subj }}</strong></td>
                                    <td class="col-nilai">{{ $score ?? '-' }}</td>
                                    <td>{{ $desk ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- EKSTRAKURIKULER TABLE -->
                    @if(strtolower($jenis) === 'uas')
                        <table class="grades-table" style="margin-bottom: 10px;">
                            <thead>
                                <tr>
                                    <th class="col-no">No</th>
                                    <th>Kegiatan Ekstrakurikuler</th>
                                    <th class="col-predikat">Predikat</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($sEkskul) > 0)
                                    @foreach($sEkskul as $idx => $ekskul)
                                        <tr>
                                            <td class="col-no">{{ $idx + 1 }}</td>
                                            <td><strong>{{ $ekskul->nama }}</strong></td>
                                            <td class="col-predikat">{{ $ekskul->predikat ?? '-' }}</td>
                                            <td>{{ $ekskul->keterangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                    @for($i = count($sEkskul); $i < 3; $i++)
                                        <tr>
                                            <td class="col-no">{{ $i + 1 }}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endfor
                                @else
                                    <tr>
                                        <td class="col-no" style="text-align: center;">1</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="col-no" style="text-align: center;">2</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="col-no" style="text-align: center;">3</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    @endif

                    <!-- KETIDAKHADIRAN & NILAI LAB -->
                    <div class="flex-container">
                        <div class="flex-col">
                            <div class="flex-col-title">Ketidakhadiran</div>
                            <table class="grades-table" style="font-weight: bold;">
                                <tbody>
                                    <tr>
                                        <td style="width: 100px;">Sakit</td>
                                        <td>: {{ $sakit }}</td>
                                    </tr>
                                    <tr>
                                        <td>Izin</td>
                                        <td>: {{ $izin }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tanpa Keterangan</td>
                                        <td>: {{ $alpha }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        @if(strtolower($jenis) === 'uas')
                            <div class="flex-col">
                                <div class="flex-col-title">Nilai Laboratorium</div>
                                <table class="grades-table" style="font-weight: bold;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 100px;">Fisika</td>
                                            <td>: {{ $sLab['FISIKA'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Biologi</td>
                                            <td>: {{ $sLab['BIOLOGI'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kimia</td>
                                            <td>: {{ $sLab['KIMIA'] ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- KEPRIBADIAN -->
                    @php
                        $kepAspects = ['Kelakuan', 'Kerajinan', 'Kerapian', 'Kedisiplinan'];
                        $kepPeriods = ['kepribadian_uh1' => 'UH1', 'kepribadian_uts' => 'UTS', 'kepribadian_uh2' => 'UH2'];
                    @endphp
                    <div style="margin-top: 6px;">
                        <div class="flex-col-title">Kepribadian</div>
                        <table class="grades-table" style="font-weight: bold; font-size: 10px; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Aspek</th>
                                    @if(strtolower($jenis) === 'uas')
                                        @foreach($kepPeriods as $kpKey => $kpLabel)
                                            <th style="width: 40px; text-align: center;">{{ $kpLabel }}</th>
                                        @endforeach
                                    @else
                                        <th style="width: 60px; text-align: center;">Nilai</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kepAspects as $kepAspect)
                                <tr>
                                    <td>{{ $kepAspect }}</td>
                                    @if(strtolower($jenis) === 'uas')
                                        @foreach($kepPeriods as $kpKey => $kpLabel)
                                            <td style="text-align: center;">{{ $sKepribadian[$kpKey][$kepAspect] ?? '-' }}</td>
                                        @endforeach
                                    @else
                                        <td style="text-align: center;">{{ $sKepribadian[$kepPeriodForJenis][$kepAspect] ?? '-' }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- CATATAN WALI KELAS -->
                    <div class="catatan-title">Catatan Wali Kelas</div>
                    <div class="catatan-box">
                        <div class="catatan-isi">Tingkatkan Prestasimu</div>
                    </div>

                    <!-- SIGNATURE -->
                    @php
                        $months = [
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember'
                        ];
                        $dateStr = date('j') . ' ' . ($months[(int) date('n')] ?? date('F')) . ' ' . date('Y');
                    @endphp
                    <div style="font-size: 11px; font-family: 'Times New Roman', Times, serif; margin-top: 10px;">
                        <!-- Row 1: Orang Tua/Wali (left) | Date + Wali Kelas (right) -->
                        <div style="display: flex; justify-content: space-between;">
                            <div style="text-align: left;">
                                <div>Mengetahui,</div>
                                <div>Orang Tua/Wali</div>
                            </div>
                            <div style="text-align: right;">
                                <div>Medan, {{ $dateStr }}</div>
                                <div>Wali Kelas,</div>
                            </div>
                        </div>

                        <!-- Row 2: Signature line (left) | Wali Kelas name (right) -->
                        <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                            <div style="text-align: left;">
                                <div style="border-bottom: 1px solid #000; width: 180px;">&nbsp;</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: bold; text-decoration: underline;">
                                    {{ $s->wali_kelas_nama ?? $user->nama }}
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Kepala Sekolah (centered below) -->
                        <div style="text-align: center; margin-top: 15px;">
                            <div>Mengetahui,</div>
                            <div>Kepala Sekolah</div>
                            <div style="height: 40px; display: flex; align-items: center; justify-content: center; position: relative;">
                                <img src="{{ asset('assets/img/ttd-kepsek.png') }}" onerror="this.style.display='none'"
                                    style="max-height: 45px; position: absolute; bottom: 0; z-index: 0;">
                            </div>
                            <div style="font-weight: bold; text-decoration: underline; margin-top: 3px;">
                                Pinondang Situmorang, S.S.,S.Pd.,M.M.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

    @empty
        <div class="page" style="display: flex; align-items: center; justify-content: center; text-align: center;">
            <div style="font-size: 16px;">Tidak ada data siswa yang dipilih.</div>
        </div>
    @endforelse

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 500);
        });
    </script>
</body>

</html>