@extends('layouts.app')
@section('page_title', 'Jadwal Kelas')
@section('styles')
<style>
    .container { max-width:1380px; margin:2rem auto; padding:0 1.5rem; }
    .card { background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(12,68,124,.1); padding:2rem; }
    .card-header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #0C447C; padding-bottom:1rem; margin-bottom:1.5rem; }
    .card-title { font-size:1.4rem; font-weight:bold; color:#0C447C; }
    .kelas-badge { background:#0C447C; color:#fff; padding:4px 14px; border-radius:20px; font-size:.9rem; font-weight:600; }
    .today-label { font-size:.82rem; color:#EF9F27; font-weight:600; display:flex; align-items:center; gap:5px; }
    .timetable-wrapper { overflow-x:auto; }
    .timetable { width:100%; border-collapse:collapse; min-width:850px; font-size:.92rem; }
    .timetable th { background:#0C447C; color:#fff; padding:12px 10px; text-align:center; font-weight:700; letter-spacing:.04em; border:1px solid #dce8f5; white-space:nowrap; }
    .timetable th.th-today { background:#EF9F27; color:#fff; border-left:3px solid #EF9F27; border-right:3px solid #EF9F27; }
    .timetable td { border:1px solid #dce8f5; padding:12px 8px; text-align:center; vertical-align:middle; color:#1a3a5a; }
    .timetable td.td-jam { font-size:.85rem; color:#378ADD; white-space:nowrap; padding:12px 14px; font-weight:500; }
    .timetable td.td-les { font-weight:700; color:#0C447C; width:40px; font-size:.95rem; }
    .timetable td.td-today { border-left:3px solid #EF9F27; border-right:3px solid #EF9F27; }
    .break-row td { background:#EAF2FB; text-align:center; font-style:italic; font-size:.85rem; color:#5a8ab0; padding:8px; letter-spacing:.08em; border:1px solid #dce8f5; }
    .mapel-cell { border-radius:6px; padding:10px 12px; font-weight:600; font-size:.88rem; display:inline-block; width:100%; color:#1a2a3a; }
    .empty-cell { color:#bbb; font-size:.9rem; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Jadwal Pelajaran Mingguan</div>
            <div style="display:flex;align-items:center;gap:12px;">
                @if($todayHari)
                <div class="today-label">▶ Hari ini: {{ $todayHari }}</div>
                @endif
                <span class="kelas-badge">KELAS: {{ $myKelas }}</span>
            </div>
        </div>

        <div class="timetable-wrapper">
            <table class="timetable">
                <thead>
                    <tr>
                        <th style="width:95px;">JAM</th>
                        <th style="width:42px;">LES</th>
                        @foreach($hari as $h)
                        <th class="{{ $h === $todayHari ? 'th-today' : '' }}">{{ strtoupper($h) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($les as $slot)
                    @if(isset($slot['break']))
                    <tr class="break-row"><td colspan="7">{{ $slot['break'] }}</td></tr>
                    @else
                    @php $jamDisplay = substr($slot['mulai'],0,5).'-'.substr($slot['selesai'],0,5); @endphp
                    <tr>
                        <td class="td-jam">{{ $jamDisplay }}</td>
                        <td class="td-les">{{ $slot['no'] }}</td>
                        @foreach($hari as $h)
                        @php
                            $slotData = $jadwalMap[$h][$slot['mulai']] ?? null;
                            $mp = $slotData ? $slotData['mata_pelajaran'] : null;
                            $guru = $slotData ? $slotData['nama_guru'] : null;
                            $cfg = $mp ? ($mapelColor[$mp] ?? ['bg' => '#E6F1FB', 'border' => 'transparent', 'text' => '#1a2a3a']) : null;
                        @endphp
                        <td class="{{ $h === $todayHari ? 'td-today' : '' }}">
                            @if($mp)
                            <span class="mapel-cell" style="background:{{ $cfg['bg'] }}; border-left: 3px solid {{ $cfg['border'] }}; color: {{ $cfg['text'] }}; text-align: left; display: block; box-sizing: border-box;">
                                <div style="font-weight:700;">{{ $mp }}</div>
                                @if($guru)
                                <div style="font-size:0.75rem; opacity:0.85; margin-top:4px; font-weight:normal; border-top:1px dashed rgba(0,0,0,0.1); padding-top:4px;">
                                    👤 {{ $guru }}
                                </div>
                                @endif
                            </span>
                            @else
                            <span class="empty-cell">–</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
