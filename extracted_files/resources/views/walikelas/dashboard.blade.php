@extends('layouts.app')

@section('page_title', 'Dashboard Wali Kelas')

@section('styles')
<style>
    .greeting { font-size: 2rem; font-weight: bold; color: #0C447C; margin-bottom: .3rem; text-transform: uppercase; }
    .subtitle { color: #6a9bc0; margin-bottom: 2rem; font-size: 1rem; }
    .cards-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.2rem; margin-bottom: 2.5rem; }
    .card { background: #ffffff; border: 1px solid #dce8f5; border-radius: 10px; padding: 1.2rem 1.5rem; }
    .card-label { color: #6a9bc0; font-size: .82rem; margin-bottom: .4rem; }
    .card-value { font-size: 1.6rem; font-weight: bold; color: #7ecb7e; }
    
    .section-title {
        font-size: 1.2rem; font-weight: 700; color: #1a3a5a; margin-bottom: 1.2rem;
        border-left: 4px solid #7ecb7e; padding-left: 1rem; display: flex; align-items: center; justify-content: space-between;
    }
    .table-wrap { background: #ffffff; border: 1px solid #7baada; border-radius: 10px; overflow-x: auto; margin-bottom:2rem; }
    table { width: 100%; border-collapse: collapse; min-width: 700px; }
    th { background: #EAF2FB; color: #378ADD; font-size: .85rem; padding: .9rem 1rem; text-align: left; border-bottom: 2px solid #9bbeeb; text-transform: uppercase; }
    td { padding: .9rem 1rem; border-bottom: 1px solid #c8d8ec; font-size: .95rem; vertical-align: middle; color: #1a3a5a; }
    
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .78rem; font-weight: 600; }
    .badge-l { background: #1a3a5c; color: #6ab3f5; }
    .badge-p { background: #3a1a3a; color: #f56ab3; }
    
    .badge-hadir { background: #e6f9ed; color: #2e8b57; border: 1px solid #c3e6cb; }
    .badge-sakit { background: #fff8e6; color: #b8860b; border: 1px solid #ffeeba; }
    .badge-izin  { background: #e6f2ff; color: #0056b3; border: 1px solid #b8daff; }
    .badge-alpha { background: #ffe6e6; color: #c82333; border: 1px solid #f5c6cb; }
    .badge-belum { background: #f2f2f2; color: #6c757d; border: 1px solid #dee2e6; }
</style>
@endsection

@section('content')
<div class="greeting">HI, {{ strtoupper(auth()->user()->nama) }}</div>
<div class="subtitle">Dashboard Wali Kelas {{ $myKelas }}</div>

<div class="cards-row">
    <div class="card">
        <div class="card-label">Total Siswa</div>
        <div class="card-value">{{ $totalSiswa }}</div>
    </div>
    <div class="card">
        <div class="card-label">Siswa Laki-laki</div>
        <div class="card-value" style="color:#6ab3f5;">{{ $totalLaki }}</div>
    </div>
    <div class="card">
        <div class="card-label">Siswa Perempuan</div>
        <div class="card-value" style="color:#f56ab3;">{{ $totalPerempuan }}</div>
    </div>
    <div class="card">
        <div class="card-label">Kehadiran Hari Ini</div>
        <div class="card-value" style="color:{{ $avgHadirClass >= 85 ? '#4ade80' : ($avgHadirClass >= 70 ? '#facc15' : '#f87171') }}">
            {{ $avgHadirClass }}%
        </div>
    </div>
</div>

<div class="section-title">Rekap Kehadiran Kelas {{ $myKelas }}</div>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Siswa</th>
                <th width="15%">NIS</th>
                <th width="15%">L/P</th>
                <th width="35%">Status Hari Ini</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswaList as $idx => $s)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td style="font-weight:600;">{{ $s->nama }}</td>
                <td>{{ $s->nis }}</td>
                <td>
                    <span class="badge {{ $s->jenis_kelamin === 'Laki-laki' ? 'badge-l' : 'badge-p' }}">
                        {{ $s->jenis_kelamin === 'Laki-laki' ? 'L' : 'P' }}
                    </span>
                </td>
                <td>
                    @php
                        $statusClass = 'badge-belum';
                        if ($s->status_kehadiran === 'Hadir') $statusClass = 'badge-hadir';
                        elseif ($s->status_kehadiran === 'Sakit') $statusClass = 'badge-sakit';
                        elseif ($s->status_kehadiran === 'Izin') $statusClass = 'badge-izin';
                        elseif ($s->status_kehadiran === 'Alpha') $statusClass = 'badge-alpha';
                    @endphp
                    <span class="badge {{ $statusClass }}" style="font-size: 0.85rem; padding: 5px 12px;">
                        {{ $s->status_kehadiran }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#378ADD;">Belum ada data siswa di kelas ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
