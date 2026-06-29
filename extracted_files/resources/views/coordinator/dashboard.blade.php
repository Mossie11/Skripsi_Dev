@extends('layouts.app')

@section('page_title', 'Dashboard Koordinator')

@section('styles')
<style>
    .greeting { font-size: 2rem; font-weight: bold; color: #0C447C; margin-bottom: .3rem; text-transform: uppercase; }
    .subtitle  { color: #6a9bc0; margin-bottom: 2rem; font-size: 1rem; }

    .section-title {
        font-size: 1.1rem; font-weight: bold; color: #0C447C; margin-bottom: 1rem;
        padding-bottom: .5rem; border-left: 4px solid #EF9F27; padding-left: .8rem;
    }

    /* Juara Umum Cards */
    .juara-grid { display: flex; gap: 16px; margin-bottom: 2.5rem; flex-wrap: wrap; }
    .juara-card {
        flex: 1; min-width: 220px; background: #fff; border: 1px solid #dce8f5;
        border-radius: 10px; padding: 1.5rem; text-align: center;
        transition: transform .2s, box-shadow .2s;
    }
    .juara-card:hover { transform: translateY(-4px); box-shadow: 0 6px 20px rgba(12,68,124,.12); }
    .juara-icon { font-size: 2.5rem; margin-bottom: 10px; }
    .juara-tingkat { color: #EF9F27; font-weight: bold; font-size: 1rem; margin-bottom: 10px; text-transform: uppercase; }
    .juara-nama { font-size: 1.2rem; font-weight: bold; color: #0C447C; cursor: pointer; text-decoration: underline; }
    .juara-kelas-label { color: #6a9bc0; margin-top: 5px; font-size: .9rem; }
    .score-badge { display: inline-block; background: #4ecf9a; color: #fff; padding: 5px 15px; border-radius: 20px; font-weight: bold; margin-top: 12px; }

    /* Juara Kelas Cards */
    .kelas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 2rem; }
    .kelas-card {
        background: #fff; border: 1px solid #dce8f5; border-radius: 10px; padding: 1.2rem;
        transition: transform .2s, border-color .2s;
    }
    .kelas-card:hover { transform: translateY(-3px); border-color: #EF9F27; }
    .kelas-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .kelas-card-name { font-size: 1rem; font-weight: bold; color: #0C447C; cursor: pointer; text-decoration: underline; }
    .kelas-card-footer { display: flex; justify-content: space-between; border-top: 1px solid #f0f0f0; padding-top: 10px; margin-top: 10px; }
</style>
@endsection

@section('content')
<div class="greeting">HI, {{ strtoupper(auth()->user()->nama) }}</div>
<div class="subtitle">Dashboard Koordinator</div>

{{-- Juara Umum --}}
<div class="section-title">🏆 Juara Umum (Per Tingkatan)</div>
<div class="juara-grid">
    @foreach(['X', 'XI', 'XII'] as $t)
    <div class="juara-card">
        <div class="juara-icon">🥇</div>
        <div class="juara-tingkat">Tingkat {{ $t }}</div>
        @if(isset($juara_umum[$t]))
            <div class="juara-nama">{{ $juara_umum[$t]->nama }}</div>
            <div class="juara-kelas-label">Kelas {{ $juara_umum[$t]->kelas }}</div>
            <div><span class="score-badge">⭐ {{ $juara_umum[$t]->avg_score_rounded }}</span></div>
        @else
            <div style="color:#999; margin-top:15px; font-size:.9rem;">Belum ada data nilai</div>
        @endif
    </div>
    @endforeach
</div>

{{-- Juara Kelas --}}
<div class="section-title">🏅 Juara Kelas</div>
@if(empty($juara_kelas))
    <p style="color:#999;">Belum ada data juara kelas.</p>
@else
<div class="kelas-grid">
    @foreach($juara_kelas as $kls => $j)
    <div class="kelas-card">
        <div class="kelas-card-header">
            <span style="font-weight:bold; color:#0C447C;">Kelas {{ $kls }}</span>
            <span>🥇</span>
        </div>
        @if($j)
        <div class="kelas-card-name">{{ $j->nama }}</div>
        <div class="kelas-card-footer">
            <span style="color:#888; font-size:.85rem;">Rata-rata</span>
            <span style="font-weight:bold; color:#4ecf9a;">{{ $j->avg_score_rounded }}</span>
        </div>
        @else
        <div class="kelas-card-name" style="color:#ccc; text-decoration:none; cursor:default;">-</div>
        <div class="kelas-card-footer">
            <span style="color:#888; font-size:.85rem;">Rata-rata</span>
            <span style="font-weight:bold; color:#ccc;">-</span>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif
@endsection
