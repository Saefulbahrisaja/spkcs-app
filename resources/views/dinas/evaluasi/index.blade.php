@extends('layouts.app')

@section('content')
<div class="container">

    <h4 class="mb-4">Evaluasi Kesesuaian Lahan (Dinas)</h4>

   
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Wilayah</th>
                <th>Kelas</th>
                <th>Status Validasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data as $i => $w)
            <tr>
                <td>{{ $i + 1 }}</td>

                <td>{{ $w->lokasi }}</td>

                {{-- ================= KELAS KESESUAIAN ================= --}}
                <td>
                    @php
                        $kelas = $w->klasifikasi->kelas_kesesuaian ?? null;
                    @endphp

                    @if($kelas === 'S1')
                        <span class="badge bg-success">S1 [Sangat Sesuai]</span>
                    @elseif($kelas === 'S2')
                        <span class="badge bg-primary text-dark">S2 [Cukup Sesuai]</span>
                    @elseif($kelas === 'S3')
                        <span class="badge bg-warning ">S3 [Marginal]</span>
                    @elseif($kelas === 'N')
                        <span class="badge bg-danger">N [Tidak Sesuai]</span>
                    @else
                        <span class="badge bg-secondary">-</span>
                    @endif
                </td>

                {{-- ================= STATUS VALIDASI ================= --}}
                <td>
                    <td>
                        @if($w->status_validasi === 'disetujui')
                            <span class="badge bg-success">Disetujui 🔒</span>
                        @elseif($w->status_validasi === 'perlu_revisi')
                            <span class="badge bg-warning text-dark">Perlu Revisi</span>
                        @else
                            <span class="badge bg-secondary">Belum</span>
                        @endif
                    </td>
                </td>

                {{-- ================= AKSI ================= --}}
                <td>
                    <a href="{{ route('dinas.evaluasi.show', $w->id) }}"
                       class="btn btn-sm btn-primary">
                        Evaluasi
                    </a>

                    <a href="{{ route('dinas.evaluasi.wilayah.pdf', $w->id) }}"
                       class="btn btn-sm btn-danger">
                        Cetak PDF
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endsection
