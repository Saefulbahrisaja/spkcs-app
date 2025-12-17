@extends('layouts.app')

@section('content')
@php
    $locked = $wilayah->status_validasi === 'disetujui';
@endphp
<div class="container">

    <h4 class="mb-3">
        Evaluasi Wilayah:
        <span class="text-primary">{{ $wilayah->lokasi }}</span>
        @if($locked)
    <span class="badge bg-success ms-2">
        🔒 Terkunci (Disetujui)
    </span>
@endif
    </h4>

    {{-- ================= INFORMASI WILAYAH ================= --}}
    <div class="card mb-4">
        <div class="card-body">

            {{-- Kelas Kesesuaian --}}
            <p>
                <strong>Kelas Kesesuaian:</strong>
                @php
                    $kelas = $wilayah->klasifikasi->kelas_kesesuaian ?? null;
                @endphp

                @if($kelas === 'S1')
                    <span class="badge bg-success">S1 — Sangat Sesuai</span>
                @elseif($kelas === 'S2')
                    <span class="badge bg-warning text-dark">S2 — Cukup Sesuai</span>
                @elseif($kelas === 'S3')
                    <span class="badge bg-orange text-dark">S3 — Sesuai Marginal</span>
                @elseif($kelas === 'N')
                    <span class="badge bg-danger">N — Tidak Sesuai</span>
                @else
                    <span class="badge bg-secondary">Belum diklasifikasi</span>
                @endif
            </p>

            {{-- ================= NILAI KRITERIA ================= --}}
            <h6 class="mt-3">Nilai Kriteria</h6>

            <ul class="list-group list-group-flush">
                @forelse($wilayah->nilaiAlternatif as $n)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $n->atribut_nama }}</span>
                        <strong>
                            {{ $n->nilai_input ?? $n->nilai ?? '-' }}
                        </strong>
                    </li>
                @empty
                    <li class="list-group-item text-muted">
                        Data kriteria belum tersedia.
                    </li>
                @endforelse
            </ul>

        </div>
    </div>

    {{-- ================= FORM VALIDASI DINAS ================= --}}
    <form method="POST"
          action="{{ route('dinas.evaluasi.validasi', $wilayah->id) }}"
          class="card shadow-sm">
        @csrf
        <div class="card-body">

            <h6 class="mb-3">Validasi oleh Dinas</h6>

            {{-- STATUS VALIDASI --}}
            <div class="mb-3">
                <label class="form-label">Status Validasi</label>
                <select name="status_validasi" class="form-select {{ $locked ? 'disabled' : '' }} " required>
                    <option value="">-- Pilih --</option>
                    <option value="disetujui"
                        {{ $wilayah->status_validasi === 'disetujui' ? 'selected' : '' }}>
                        Disetujui
                    </option>
                    <option value="perlu_revisi"
                        {{ $wilayah->status_validasi === 'perlu_revisi' ? 'selected' : '' }}>
                        Perlu Revisi
                    </option>
                </select>
            </div>

            {{-- REKOMENDASI DINAS --}}
            <div class="mb-3">
                <label class="form-label">Rekomendasi Dinas</label>
                <textarea name="rekomendasi"
                          rows="4"
                          class="form-control  {{ $locked ? 'disabled' : '' }}>{{ old('rekomendasi', $wilayah->rekomendasi_dinas) }}"
                          placeholder="Contoh: Direkomendasikan untuk padi sawah irigasi teknis">{{ old('rekomendasi', $wilayah->rekomendasi_dinas) }}</textarea>
            </div>

        </div>

        <div class="card-footer text-end">
           @if(!$locked)
            <button class="btn btn-success">
                💾 Simpan Evaluasi
            </button>
        @else
            <div class="alert alert-success mb-0">
                ✔ Data telah <strong>disetujui</strong> dan terkunci.
            </div>
        @endif
            <a href="{{ route('dinas.evaluasi') }}"
               class="btn btn-secondary ms-2">
                Kembali
            </a>
        </div>

    </form>

</div>
@endsection
