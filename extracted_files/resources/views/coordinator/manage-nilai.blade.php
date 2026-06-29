@extends('layouts.app')
@section('page_title','Manage Nilai')
@section('styles')@include('coordinator.partials.manage-styles')
<style>
    .score-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.8rem; font-weight:bold; }
    .semester-select { padding:4px 10px; border-radius:20px; font-size:.85rem; font-weight:600; margin-left:10px; background:#f0f7ff; color:#0C447C; border:1px solid #0C447C; outline:none; cursor:pointer; }

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
    .tab-panel .table-wrapper { box-shadow: none; border: none; background: transparent; }
</style>
@endsection

@php
    $typeLabels = [
        'tugas1' => 'TUGAS1',
        'uh1' => 'UH1',
        'tugas2' => 'TUGAS2',
        'uh2' => 'UH2',
        'uts' => 'UTS',
        'uas' => 'UAS',
    ];
    $typeLabelsLong = [
        'tugas1' => 'TUGAS 1',
        'uh1' => 'UH 1',
        'tugas2' => 'TUGAS 2',
        'uh2' => 'UH 2',
        'uts' => 'UTS',
        'uas' => 'UAS',
    ];
@endphp

@section('content')
<div class="container-manage">
    <div class="header">
        <h2 style="display:flex; align-items:center;">
            <i class='bx bx-bar-chart-alt-2'></i> Manage Nilai Siswa
            <form id="semForm" method="GET" action="{{ route('coordinator.manage-nilai') }}" style="display:inline-block;">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <input type="hidden" name="filter_kelas" value="{{ $filterKelas }}">
                <input type="hidden" name="filter_mapel" value="{{ $filterMapel }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <select name="filter_semester" class="semester-select" onchange="document.getElementById('semForm').submit()">
                    <option value="1" {{ $activeSemester === '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $activeSemester === '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </form>
        </h2>
    </div>

    <div class="filters">
        <form class="search-box" method="GET" action="{{ route('coordinator.manage-nilai') }}">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <input type="hidden" name="filter_semester" value="{{ $activeSemester }}">
            <select name="filter_kelas" onchange="this.form.submit()" style="max-width:150px;">
                <option value="">All Kelas</option>
                @foreach($kelasList as $kelasId => $kelasNama)<option value="{{ $kelasId }}" {{ $filterKelas===$kelasId?'selected':'' }}>{{ $kelasNama }}</option>@endforeach
            </select>
            @if($activeTab === 'mapel')
            <select name="filter_mapel" onchange="this.form.submit()" style="max-width:250px;">
                <option value="">All Mapel</option>
                @foreach($mapelList as $mapelId => $mapelNama)<option value="{{ $mapelNama }}" {{ $filterMapel===$mapelNama?'selected':'' }}>{{ $mapelNama }}</option>@endforeach
            </select>
            @endif
            <input type="text" name="search" placeholder="Search nama siswa..." value="{{ $search }}">
            <button type="submit">🔍</button>
            @if($search||$filterKelas||$filterMapel)<a href="{{ route('coordinator.manage-nilai') }}?tab={{ $activeTab }}" class="btn btn-back" style="padding:.6rem;">Clear</a>@endif
        </form>
    </div>

    <!-- Tab Buttons -->
    <div class="tab-container">
        <button class="tab-btn {{ $activeTab === 'mapel' ? 'active' : '' }}" id="tabBtnMapel" onclick="switchTab('mapel')">📚 Rekap Nilai Mapel</button>
        <button class="tab-btn {{ $activeTab === 'ekskul' ? 'active' : '' }}" id="tabBtnEkskul" onclick="switchTab('ekskul')">🏆 Nilai Ekstrakurikuler</button>
        <button class="tab-btn {{ $activeTab === 'lab' ? 'active' : '' }}" id="tabBtnLab" onclick="switchTab('lab')">🔬 Nilai Laboratorium</button>
    </div>

    <!-- Tab 1: Nilai Mapel -->
    <div id="panelMapel" class="tab-panel {{ $activeTab === 'mapel' ? 'active' : '' }}">
        <div class="pagination" style="margin-top: 0; padding-top: 0;">
            <span>{{ $total > 0 ? ($offset+1).'-'.min($offset+count($pageData),$total) : '0' }} / {{ $total }}</span>
            <div class="page-nav">
                @php 
                    $qStrMapel = '&tab=mapel&filter_semester='.$activeSemester.($search?'&search='.urlencode($search):'').($filterKelas?'&filter_kelas='.urlencode($filterKelas):'').($filterMapel?'&filter_mapel='.urlencode($filterMapel):''); 
                @endphp
                <a href="?page=1{{ $qStrMapel }}" class="{{ $page<=1?'disabled':'' }}">«</a>
                <a href="?page={{ $page-1 }}{{ $qStrMapel }}" class="{{ $page<=1?'disabled':'' }}">‹</a>
                <span style="padding:.4rem;">{{ $page }} / {{ $totalPages }}</span>
                <a href="?page={{ $page+1 }}{{ $qStrMapel }}" class="{{ $page>=$totalPages?'disabled':'' }}">›</a>
                <a href="?page={{ $totalPages }}{{ $qStrMapel }}" class="{{ $page>=$totalPages?'disabled':'' }}">»</a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:1rem!important;width:auto;">Nama Siswa</th>
                        <th style="width: 60px;">Kelas</th>
                        <th style="width: 140px;">Mapel</th>
                        @foreach($assessmentTypes as $type)
                        <th style="width: 85px; text-align: center;">{{ $typeLabels[$type] ?? strtoupper($type) }}</th>
                        @endforeach
                        <th style="width: 85px; text-align: center;">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageData as $n)
                    <tr style="cursor: default;">
                        <td style="padding-left:1rem!important;font-weight:600;color:#0C447C;">{{ $n->siswa_nama }}</td>
                        <td>{{ $n->kelas }}</td>
                        <td style="white-space:normal;max-width:140px;font-size:.85rem;line-height:1.2;">{{ $n->nama_mapel ?? '-' }}</td>
                        @foreach($assessmentTypes as $type)
                        <td style="text-align: center;">{{ $n->$type ?? '—' }}</td>
                        @endforeach
                        <td style="font-weight:bold; text-align: center; color:{{ isset($n->nilai_akhir) && $n->nilai_akhir < 75 ? '#dc3545' : '#0C447C' }};">
                            {{ $n->nilai_akhir ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        @foreach($assessmentTypes as $type)
                        <td>&nbsp;</td>
                        @endforeach
                        <td>&nbsp;</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Nilai Ekskul -->
    <div id="panelEkskul" class="tab-panel {{ $activeTab === 'ekskul' ? 'active' : '' }}">
        <div class="pagination" style="margin-top: 0; padding-top: 0;">
            <span>{{ $ekskulTotal > 0 ? ($offset+1).'-'.min($offset+count($ekskulPageData),$ekskulTotal) : '0' }} / {{ $ekskulTotal }}</span>
            <div class="page-nav">
                @php 
                    $qStrEkskul = '&tab=ekskul&filter_semester='.$activeSemester.($search?'&search='.urlencode($search):'').($filterKelas?'&filter_kelas='.urlencode($filterKelas):'').($filterMapel?'&filter_mapel='.urlencode($filterMapel):''); 
                @endphp
                <a href="?page=1{{ $qStrEkskul }}" class="{{ $page<=1?'disabled':'' }}">«</a>
                <a href="?page={{ $page-1 }}{{ $qStrEkskul }}" class="{{ $page<=1?'disabled':'' }}">‹</a>
                <span style="padding:.4rem;">{{ $page }} / {{ $ekskulTotalPages }}</span>
                <a href="?page={{ $page+1 }}{{ $qStrEkskul }}" class="{{ $page>=$ekskulTotalPages?'disabled':'' }}">›</a>
                <a href="?page={{ $ekskulTotalPages }}{{ $qStrEkskul }}" class="{{ $page>=$ekskulTotalPages?'disabled':'' }}">»</a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:1rem!important; width:50px;">No</th>
                        <th style="min-width:200px; text-align:left;">Nama Siswa</th>
                        <th style="width:120px;">Kelas</th>
                        <th style="width:250px;">Nama Ekstrakurikuler</th>
                        <th style="width:150px;">Predikat</th>
                        <th style="min-width:300px; text-align:left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ekskulPageData as $idx => $s)
                    <tr>
                        <td style="padding-left:1rem!important;">{{ $offset + $idx + 1 }}</td>
                        <td style="text-align:left; font-weight:600; color:#0C447C;">{{ $s->siswa_nama }}</td>
                        <td>{{ $s->kelas }}</td>
                        <td style="color:#0C447C; font-weight:500;">
                            {{ $s->ekskul ?? '—' }}
                        </td>
                        <td style="font-weight:bold; color:#0C447C;">
                            {{ $s->nilai_ekskul ?? '—' }}
                        </td>
                        <td style="text-align:left; color:#1a2a3a;">
                            {{ $s->ekskul_keterangan ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:#7baada;">Tidak ada data siswa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Nilai Lab -->
    <div id="panelLab" class="tab-panel {{ $activeTab === 'lab' ? 'active' : '' }}">
        <div class="pagination" style="margin-top: 0; padding-top: 0;">
            <span>{{ $labTotal > 0 ? ($offset+1).'-'.min($offset+count($labPageData),$labTotal) : '0' }} / {{ $labTotal }}</span>
            <div class="page-nav">
                @php 
                    $qStrLab = '&tab=lab&filter_semester='.$activeSemester.($search?'&search='.urlencode($search):'').($filterKelas?'&filter_kelas='.urlencode($filterKelas):'').($filterMapel?'&filter_mapel='.urlencode($filterMapel):''); 
                @endphp
                <a href="?page=1{{ $qStrLab }}" class="{{ $page<=1?'disabled':'' }}">«</a>
                <a href="?page={{ $page-1 }}{{ $qStrLab }}" class="{{ $page<=1?'disabled':'' }}">‹</a>
                <span style="padding:.4rem;">{{ $page }} / {{ $labTotalPages }}</span>
                <a href="?page={{ $page+1 }}{{ $qStrLab }}" class="{{ $page>=$labTotalPages?'disabled':'' }}">›</a>
                <a href="?page={{ $labTotalPages }}{{ $qStrLab }}" class="{{ $page>=$labTotalPages?'disabled':'' }}">»</a>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:1rem!important; width:50px;">No</th>
                        <th style="min-width:200px; text-align:left;">Nama Siswa</th>
                        <th style="width:120px;">Kelas</th>
                        <th style="width:150px; text-align:center;">Lab Fisika</th>
                        <th style="width:150px; text-align:center;">Lab Kimia</th>
                        <th style="width:150px; text-align:center;">Lab Biologi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labPageData as $idx => $s)
                    <tr>
                        <td style="padding-left:1rem!important;">{{ $offset + $idx + 1 }}</td>
                        <td style="text-align:left; font-weight:600; color:#0C447C;">{{ $s->siswa_nama }}</td>
                        <td>{{ $s->kelas }}</td>
                        <td style="text-align:center; font-weight:bold; color:#0C447C;">{{ $s->fisika_lab ?? '—' }}</td>
                        <td style="text-align:center; font-weight:bold; color:#0C447C;">{{ $s->kimia_lab ?? '—' }}</td>
                        <td style="text-align:center; font-weight:bold; color:#0C447C;">{{ $s->biologi_lab ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:#7baada;">Tidak ada data siswa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('coordinator.partials.manage-scripts')
<script>
    // Tab switching
    function switchTab(tabId) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('tab', tabId);
        urlParams.set('page', '1'); // Reset to page 1 when switching tabs
        window.location.search = urlParams.toString();
    }
</script>
@endsection
