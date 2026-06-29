@extends('layouts.app')
@section('page_title', 'Manage Bobot Nilai')
@section('styles')@include('coordinator.partials.manage-styles')@endsection
@section('content')
    <div class="container-manage">
        <div class="header">
            <h2><i class='bx bx-slider-alt'></i> Bobot Nilai</h2>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%; padding-left: 3.5rem!important; text-align: left;">Tahun Ajaran</th>
                        <th style="width: 10%; text-align: center;">Tugas 1</th>
                        <th style="width: 10%; text-align: center;">UH 1</th>
                        <th style="width: 10%; text-align: center;">Tugas 2</th>
                        <th style="width: 10%; text-align: center;">UH 2</th>
                        <th style="width: 10%; text-align: center;">UTS</th>
                        <th style="width: 10%; text-align: center;">UAS</th>
                        <th style="width: 10%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tahunList as $t)
                        @php
                            $bobot = $bobotList->get($t->id);
                        @endphp
                        <tr>
                            <td style="padding-left: 3.5rem!important; text-align: left;">{{ $t->nama }}
                                {!! $t->is_active ? '<span style="color:#4ecf9a;font-size:0.8rem; margin-left: 5px;">(Aktif)</span>' : '' !!}
                            </td>

                            <td style="text-align: center;">{{ $bobot ? $bobot->tugas1 . '%' : '10%' }}</td>
                            <td style="text-align: center;">{{ $bobot ? $bobot->uh1 . '%' : '15%' }}</td>
                            <td style="text-align: center;">{{ $bobot ? $bobot->tugas2 . '%' : '10%' }}</td>
                            <td style="text-align: center;">{{ $bobot ? $bobot->uh2 . '%' : '15%' }}</td>
                            <td style="text-align: center;">{{ $bobot ? $bobot->uts . '%' : '20%' }}</td>
                            <td style="text-align: center;">{{ $bobot ? $bobot->uas . '%' : '30%' }}</td>
                            <td style="text-align: center;">
                                <button class="btn btn-edit"
                                    onclick="openBobotModal({{ $t->id }}, '{{ $t->nama }}', {{ $bobot ? $bobot->tugas1 : 10 }}, {{ $bobot ? $bobot->uh1 : 15 }}, {{ $bobot ? $bobot->tugas2 : 10 }}, {{ $bobot ? $bobot->uh2 : 15 }}, {{ $bobot ? $bobot->uts : 20 }}, {{ $bobot ? $bobot->uas : 30 }})"
                                    style="padding:.4rem .8rem;font-size:.85rem;background:#EF9F27;color:#fff;">Edit
                                    Bobot</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2rem;color:#7baada;">Belum ada data tahun ajaran
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="bobotModal">
        <div class="create-modal-content" style="max-width:500px;">
            <button class="modal-close" onclick="closeModal('bobotModal')">×</button>
            <h2 style="margin-bottom:20px;">Edit Bobot Nilai - <span id="modalTahunNama"></span></h2>
            <form method="POST" action="{{ route('coordinator.manage-bobot.action') }}" onsubmit="return validateTotal()">
                @csrf
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="tahun_ajaran_id" id="modalTahunId" value="">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group"><label>Tugas 1 (%)</label><input type="number" step="0.1" name="tugas1"
                            id="in_tugas1" min="0" max="100" required></div>
                    <div class="form-group"><label>UH 1 (%)</label><input type="number" step="0.1" name="uh1" id="in_uh1"
                            min="0" max="100" required></div>
                    <div class="form-group"><label>Tugas 2 (%)</label><input type="number" step="0.1" name="tugas2"
                            id="in_tugas2" min="0" max="100" required></div>
                    <div class="form-group"><label>UH 2 (%)</label><input type="number" step="0.1" name="uh2" id="in_uh2"
                            min="0" max="100" required></div>
                    <div class="form-group"><label>UTS (%)</label><input type="number" step="0.1" name="uts" id="in_uts"
                            min="0" max="100" required></div>
                    <div class="form-group"><label>UAS (%)</label><input type="number" step="0.1" name="uas" id="in_uas"
                            min="0" max="100" required></div>
                </div>

                <div style="margin-top:20px; padding:10px; background:#f0f4f8; border-radius:4px; text-align:center;">
                    <strong>Total Bobot: <span id="totalBobotText">100</span>%</strong>
                    <div id="bobotError" style="color:red; font-size:0.85rem; display:none; margin-top:5px;">Total bobot
                        harus 100%</div>
                </div>

                <button type="submit" class="submit-btn" style="margin-top:20px;">Simpan Bobot</button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @include('coordinator.partials.manage-scripts')
    <script>
        function openBobotModal(id, nama, t1, u1, t2, u2, uts, uas) {
            document.getElementById('modalTahunId').value = id;
            document.getElementById('modalTahunNama').innerText = nama;
            document.getElementById('in_tugas1').value = t1;
            document.getElementById('in_uh1').value = u1;
            document.getElementById('in_tugas2').value = t2;
            document.getElementById('in_uh2').value = u2;
            document.getElementById('in_uts').value = uts;
            document.getElementById('in_uas').value = uas;
            openModal('bobotModal');
            updateTotal();
        }

        const inputs = ['in_tugas1', 'in_uh1', 'in_tugas2', 'in_uh2', 'in_uts', 'in_uas'];
        inputs.forEach(id => {
            document.getElementById(id).addEventListener('input', updateTotal);
        });

        function updateTotal() {
            let total = 0;
            inputs.forEach(id => {
                total += parseFloat(document.getElementById(id).value || 0);
            });
            total = Math.round(total * 10) / 10; // handle float precision
            document.getElementById('totalBobotText').innerText = total;
            if (total !== 100) {
                document.getElementById('totalBobotText').style.color = 'red';
                document.getElementById('bobotError').style.display = 'block';
            } else {
                document.getElementById('totalBobotText').style.color = 'green';
                document.getElementById('bobotError').style.display = 'none';
            }
        }

        function validateTotal() {
            let total = 0;
            inputs.forEach(id => {
                total += parseFloat(document.getElementById(id).value || 0);
            });
            total = Math.round(total * 10) / 10;
            if (total !== 100) {
                alert('Total bobot harus tepat 100%. Saat ini total adalah ' + total + '%.');
                return false;
            }
            return true;
        }
    </script>
@endsection