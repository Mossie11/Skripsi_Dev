@extends('layouts.app')
@section('page_title','Manage Periode Nilai')
@section('styles')
@include('coordinator.partials.manage-styles')
<style>
    .periode-form-row { background: #f9fbfd; border-bottom: 2px solid #e0e8f0; }
    .periode-form-row td { padding: 1.5rem 2rem; }
    .form-section-title { font-size: 1.1rem; color: #0C447C; border-bottom: 2px solid #E6F1FB; padding-bottom: 10px; margin-bottom: 15px; font-weight: 700; }
    .semester-tabs { display: flex; gap: 0; margin-bottom: 20px; }
    .semester-tab { padding: 10px 24px; cursor: pointer; font-weight: 600; font-size: 0.95rem; border: 2px solid #dce8f5; border-bottom: none; border-radius: 8px 8px 0 0; background: #f0f5fa; color: #7baada; transition: all 0.2s; }
    .semester-tab.active { background: #fff; color: #0C447C; border-color: #378ADD; border-bottom-color: #fff; position: relative; z-index: 1; }
    .semester-content { border: 2px solid #378ADD; border-radius: 0 8px 8px 8px; padding: 20px; background: #fff; margin-top: -2px; }
    .semester-content[style*="display: none"] { border-color: #dce8f5; }
    .periode-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .periode-card { background: #f9fbfd; border: 1px solid #dce8f5; border-radius: 8px; padding: 15px; }
    .periode-card h4 { margin-bottom: 10px; color: #378ADD; font-size: 0.95rem; }
    .periode-card .form-group { margin-bottom: 10px; }
    .periode-card .form-group label { font-size: 0.85rem; color: #666; margin-bottom: 4px; display: block; }
    .periode-card .form-group input[type="date"] { width: 100%; padding: 6px 10px; border: 1px solid #aac5e0; border-radius: 4px; background: #E6F1FB; color: #0C447C; font-size: 0.9rem; }
    .status-badge { font-size: 0.8rem; padding: 2px 10px; border-radius: 12px; display: inline-block; }
    .status-set { background: #d4edda; color: #155724; }
    .status-unset { background: #f0f0f0; color: #999; }
</style>
@endsection
@section('content')
<div class="container-manage">
    <div class="header">
        <h2><i class='bx bx-time'></i> Periode Penginputan Nilai</h2>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 25%; padding-left: 3.5rem!important; text-align: left;">Tahun Ajaran</th>
                    <th style="width: 20%; text-align: center;">Semester Ganjil</th>
                    <th style="width: 20%; text-align: center;">Semester Genap</th>
                    <th style="width: 15%; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tahunList as $t)
                @php
                    $periodeGanjil = $periodeList[$t->id . '-1'] ?? null;
                    $periodeGenap = $periodeList[$t->id . '-2'] ?? null;
                    $hasGanjil = $periodeGanjil && ($periodeGanjil->uh1_start || $periodeGanjil->uh2_start || $periodeGanjil->uts_start || $periodeGanjil->uas_start);
                    $hasGenap = $periodeGenap && ($periodeGenap->uh1_start || $periodeGenap->uh2_start || $periodeGenap->uts_start || $periodeGenap->uas_start);

                    $parts = explode('/', $t->nama);
                    $startYear = isset($parts[0]) ? (int)$parts[0] : (int)date('Y');
                    $endYear = isset($parts[1]) ? (int)$parts[1] : $startYear + 1;
                    
                    $defaultGanjil = [
                        'uh1_start' => "{$startYear}-07-01",
                        'uh1_end'   => "{$startYear}-08-31",
                        'uts_start' => "{$startYear}-09-01",
                        'uts_end'   => "{$startYear}-09-30",
                        'uh2_start' => "{$startYear}-10-01",
                        'uh2_end'   => "{$startYear}-11-30",
                        'uas_start' => "{$startYear}-12-01",
                        'uas_end'   => "{$startYear}-12-31",
                    ];

                    $defaultGenap = [
                        'uh1_start' => "{$endYear}-01-01",
                        'uh1_end'   => "{$endYear}-02-28",
                        'uts_start' => "{$endYear}-03-01",
                        'uts_end'   => "{$endYear}-03-31",
                        'uh2_start' => "{$endYear}-04-01",
                        'uh2_end'   => "{$endYear}-05-31",
                        'uas_start' => "{$endYear}-06-01",
                        'uas_end'   => "{$endYear}-06-30",
                    ];
                @endphp
                <tr>
                    <td style="padding-left: 3.5rem!important; text-align: left;">{{ $t->nama }} {!! $t->is_active ? '<span style="color:#4ecf9a;font-size:0.8rem; margin-left: 5px;">(Aktif)</span>' : '' !!}</td>
                    <td style="text-align: center;">
                        @if($hasGanjil)
                            <span class="status-badge status-set">Telah Diatur</span>
                        @else
                            <span class="status-badge status-unset">Belum Diatur</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($hasGenap)
                            <span class="status-badge status-set">Telah Diatur</span>
                        @else
                            <span class="status-badge status-unset">Belum Diatur</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <button class="btn btn-edit" onclick="toggleFormRow({{ $t->id }})" style="padding:.4rem .8rem;font-size:.85rem;background:#EF9F27;color:#fff; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">Set Periode <i class='bx bx-chevron-down'></i></button>
                    </td>
                </tr>
                <tr class="periode-form-row" id="form-row-{{ $t->id }}" style="display:none;">
                    <td colspan="4">
                        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dce8f5; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                            <h3 class="form-section-title">Set Periode Nilai - {{ $t->nama }}</h3>
                            
                            <!-- Semester Tabs -->
                            <div class="semester-tabs">
                                <div class="semester-tab active" onclick="switchSemesterTab({{ $t->id }}, '1', this)" id="tab-{{ $t->id }}-1">Semester Ganjil</div>
                                <div class="semester-tab" onclick="switchSemesterTab({{ $t->id }}, '2', this)" id="tab-{{ $t->id }}-2">Semester Genap</div>
                            </div>

                            <!-- Semester Ganjil Form -->
                            <div class="semester-content" id="semester-{{ $t->id }}-1">
                                <form method="POST" action="{{ route('coordinator.manage-periode.action') }}">
                                    @csrf
                                    <input type="hidden" name="tahun_ajaran_id" value="{{ $t->id }}">
                                    <input type="hidden" name="semester" value="1">
                                    
                                    <div class="periode-grid">
                                        <div class="periode-card">
                                            <h4><i class='bx bx-edit'></i> UH 1</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uh1_start" value="{{ $periodeGanjil ? $periodeGanjil->uh1_start : $defaultGanjil['uh1_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uh1_end" value="{{ $periodeGanjil ? $periodeGanjil->uh1_end : $defaultGanjil['uh1_end'] }}"></div>
                                        </div>
                                        <div class="periode-card">
                                            <h4><i class='bx bx-book-open'></i> UTS</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uts_start" value="{{ $periodeGanjil ? $periodeGanjil->uts_start : $defaultGanjil['uts_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uts_end" value="{{ $periodeGanjil ? $periodeGanjil->uts_end : $defaultGanjil['uts_end'] }}"></div>
                                        </div>
                                        <div class="periode-card">
                                            <h4><i class='bx bx-edit'></i> UH 2</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uh2_start" value="{{ $periodeGanjil ? $periodeGanjil->uh2_start : $defaultGanjil['uh2_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uh2_end" value="{{ $periodeGanjil ? $periodeGanjil->uh2_end : $defaultGanjil['uh2_end'] }}"></div>
                                        </div>
                                        <div class="periode-card">
                                            <h4><i class='bx bx-book-open'></i> Rapor Akhir</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uas_start" value="{{ $periodeGanjil ? $periodeGanjil->uas_start : $defaultGanjil['uas_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uas_end" value="{{ $periodeGanjil ? $periodeGanjil->uas_end : $defaultGanjil['uas_end'] }}"></div>
                                        </div>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                                        <div>
                                            <button type="button" class="btn" style="background:#EF9F27; color:#fff;" onclick="resetSemesterForm({{ $t->id }}, '1')">Reset</button>
                                            <button type="button" class="btn" style="background:#378ADD; color:#fff; margin-left:10px;" onclick="fillDefaultPeriod({{ $t->id }}, '1', '{{ $t->nama }}')">Set Default</button>
                                        </div>
                                        <div>
                                            <button type="button" class="btn" style="background:#e03535; color:#fff; margin-right:10px;" onclick="toggleFormRow({{ $t->id }})">Batal</button>
                                            <button type="submit" class="btn" style="background:linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); color:#fff;">Simpan Ganjil</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Semester Genap Form -->
                            <div class="semester-content" id="semester-{{ $t->id }}-2" style="display:none;">
                                <form method="POST" action="{{ route('coordinator.manage-periode.action') }}">
                                    @csrf
                                    <input type="hidden" name="tahun_ajaran_id" value="{{ $t->id }}">
                                    <input type="hidden" name="semester" value="2">
                                    
                                    <div class="periode-grid">
                                        <div class="periode-card">
                                            <h4><i class='bx bx-edit'></i> UH 1</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uh1_start" value="{{ $periodeGenap ? $periodeGenap->uh1_start : $defaultGenap['uh1_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uh1_end" value="{{ $periodeGenap ? $periodeGenap->uh1_end : $defaultGenap['uh1_end'] }}"></div>
                                        </div>
                                        <div class="periode-card">
                                            <h4><i class='bx bx-book-open'></i> UTS</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uts_start" value="{{ $periodeGenap ? $periodeGenap->uts_start : $defaultGenap['uts_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uts_end" value="{{ $periodeGenap ? $periodeGenap->uts_end : $defaultGenap['uts_end'] }}"></div>
                                        </div>
                                        <div class="periode-card">
                                            <h4><i class='bx bx-edit'></i> UH 2</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uh2_start" value="{{ $periodeGenap ? $periodeGenap->uh2_start : $defaultGenap['uh2_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uh2_end" value="{{ $periodeGenap ? $periodeGenap->uh2_end : $defaultGenap['uh2_end'] }}"></div>
                                        </div>
                                        <div class="periode-card">
                                            <h4><i class='bx bx-book-open'></i> Rapor Akhir</h4>
                                            <div class="form-group"><label>Mulai</label><input type="date" name="uas_start" value="{{ $periodeGenap ? $periodeGenap->uas_start : $defaultGenap['uas_start'] }}"></div>
                                            <div class="form-group"><label>Selesai</label><input type="date" name="uas_end" value="{{ $periodeGenap ? $periodeGenap->uas_end : $defaultGenap['uas_end'] }}"></div>
                                        </div>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                                        <div>
                                            <button type="button" class="btn" style="background:#EF9F27; color:#fff;" onclick="resetSemesterForm({{ $t->id }}, '2')">Reset</button>
                                            <button type="button" class="btn" style="background:#378ADD; color:#fff; margin-left:10px;" onclick="fillDefaultPeriod({{ $t->id }}, '2', '{{ $t->nama }}')">Set Default</button>
                                        </div>
                                        <div>
                                            <button type="button" class="btn" style="background:#e03535; color:#fff; margin-right:10px;" onclick="toggleFormRow({{ $t->id }})">Batal</button>
                                            <button type="submit" class="btn" style="background:linear-gradient(135deg, #0C447C 0%, #1a5a9a 100%); color:#fff;">Simpan Genap</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:2rem;color:#7baada;">Belum ada data tahun ajaran</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('scripts')
@include('coordinator.partials.manage-scripts')
<script>
    function toggleFormRow(id) {
        const row = document.getElementById('form-row-' + id);
        if (row.style.display === 'none') {
            document.querySelectorAll('.periode-form-row').forEach(r => r.style.display = 'none');
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    }

    function switchSemesterTab(tahunId, semester, tabEl) {
        // Hide all semester contents for this tahun
        document.getElementById('semester-' + tahunId + '-1').style.display = 'none';
        document.getElementById('semester-' + tahunId + '-2').style.display = 'none';
        
        // Remove active from tabs
        document.getElementById('tab-' + tahunId + '-1').classList.remove('active');
        document.getElementById('tab-' + tahunId + '-2').classList.remove('active');
        
        // Show selected semester and mark tab active
        document.getElementById('semester-' + tahunId + '-' + semester).style.display = 'block';
        tabEl.classList.add('active');
    }

    function resetSemesterForm(tahunId, semester) {
        if (!confirm('Apakah Anda yakin ingin mereset dan menghapus periode semester ini?')) return;
        const container = document.getElementById('semester-' + tahunId + '-' + semester);
        const inputs = container.querySelectorAll('input[type="date"]');
        inputs.forEach(input => input.value = '');
        container.querySelector('form').submit();
    }

    function fillDefaultPeriod(tahunId, semester, tahunNama) {
        const parts = tahunNama.split('/');
        const startYear = parseInt(parts[0]);
        const endYear = parseInt(parts[1]) || (startYear + 1);
        
        const container = document.getElementById('semester-' + tahunId + '-' + semester);
        
        if (semester === '1') {
            container.querySelector('input[name="uh1_start"]').value = `${startYear}-07-01`;
            container.querySelector('input[name="uh1_end"]').value = `${startYear}-08-31`;
            container.querySelector('input[name="uts_start"]').value = `${startYear}-09-01`;
            container.querySelector('input[name="uts_end"]').value = `${startYear}-09-30`;
            container.querySelector('input[name="uh2_start"]').value = `${startYear}-10-01`;
            container.querySelector('input[name="uh2_end"]').value = `${startYear}-11-30`;
            container.querySelector('input[name="uas_start"]').value = `${startYear}-12-01`;
            container.querySelector('input[name="uas_end"]').value = `${startYear}-12-31`;
        } else {
            container.querySelector('input[name="uh1_start"]').value = `${endYear}-01-01`;
            container.querySelector('input[name="uh1_end"]').value = `${endYear}-02-28`;
            container.querySelector('input[name="uts_start"]').value = `${endYear}-03-01`;
            container.querySelector('input[name="uts_end"]').value = `${endYear}-03-31`;
            container.querySelector('input[name="uh2_start"]').value = `${endYear}-04-01`;
            container.querySelector('input[name="uh2_end"]').value = `${endYear}-05-31`;
            container.querySelector('input[name="uas_start"]').value = `${endYear}-06-01`;
            container.querySelector('input[name="uas_end"]').value = `${endYear}-06-30`;
        }
    }
</script>
@endsection
