@extends('layouts.app')
@section('page_title','Manage Tahun Ajaran')
@section('styles')@include('coordinator.partials.manage-styles')@endsection
@section('content')
<div class="container-manage">
    <div class="header">
        <h2><i class='bx bx-calendar-star'></i> Tahun Ajaran</h2>
        <div class="actions">
            <button class="btn btn-create" onclick="openModal('createModal')">+ CREATE</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th style="padding-left:1rem!important;width:auto;">Tahun Ajaran</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($list as $t)
                <tr>
                    <td style="padding-left:1rem!important;">{{ $t->nama }}</td>
                    <td>
                        @if($t->is_active)
                            <span style="background:#4ecf9a;color:#fff;padding:3px 12px;border-radius:20px;font-size:.85rem;">Aktif</span>
                        @else
                            <span style="background:#dce8f5;color:#0C447C;padding:3px 12px;border-radius:20px;font-size:.85rem;">Tidak Aktif</span>
                        @endif
                    </td>
                    <td style="display:flex;gap:8px;">
                        @if(!$t->is_active)
                        <form method="POST" action="{{ route('coordinator.manage-tahun.action') }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="set_active">
                            <input type="hidden" name="id" value="{{ $t->id }}">
                            <button type="submit" class="btn btn-create" style="padding:.4rem .8rem;font-size:.85rem;">Set Aktif</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('coordinator.manage-tahun.action') }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="{{ $t->id }}">
                            <button type="submit" class="btn" style="background:#e03535;color:#fff;padding:.4rem .8rem;font-size:.85rem;" onclick="return confirm('Hapus tahun ajaran ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;padding:2rem;color:#7baada;">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="createModal">
    <div class="create-modal-content" style="max-width:400px;">
        <button class="modal-close" onclick="closeModal('createModal')">×</button>
        <h2 style="margin-bottom:20px;">Tambah Tahun Ajaran</h2>
        <form method="POST" action="{{ route('coordinator.manage-tahun.action') }}">
            @csrf
            <input type="hidden" name="action" value="create">
            <div class="form-group" style="margin-bottom:15px;"><label>Nama Tahun Ajaran *</label><input type="text" name="nama" placeholder="e.g. 2025/2026" required></div>
            <p style="font-size:.85rem;color:#888;margin-bottom:20px;"><i class='bx bx-info-circle'></i> Semester Ganjil & Genap akan otomatis tersedia untuk setiap tahun ajaran.</p>
            <button type="submit" class="submit-btn" onclick="return confirm('Tambah tahun ajaran dan jadikan aktif?')">Tambah & Aktifkan</button>
        </form>
    </div>
</div>
@endsection
@section('scripts')
@include('coordinator.partials.manage-scripts')
<script>initManageTable && null;</script>
@endsection
