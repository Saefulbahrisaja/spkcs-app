@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ route('admin.wilayah.index') }}">Daftar Lokasi</a></li>
    <li class="breadcrumb-item active">Input Nilai Alternatif</li>
</ol>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <strong>Input Nilai Alternatif</strong>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('admin.alternatif.nilai.simpan') }}">
            @csrf

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Alternatif</th>
                            @foreach($kriteria as $k)
                                <th class="text-center">{{ $k->nama_kriteria }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($alternatifs as $alt)
                        <tr>
    <td class="fw-bold">{{ $alt->lokasi }}</td>

    @foreach($kriteria as $k)
    <td class="text-center">

        @php
            $selected = $existing[$alt->id][$k->id] ?? null;

            $labels = [
                1 => 'Sangat Buruk',
                2 => 'Buruk',
                3 => 'Cukup',
                4 => 'Baik',
                5 => 'Sangat Baik'
            ];
        @endphp

        <select name="nilai[{{ $alt->id }}][{{ $k->id }}]"
                class="form-select text-center nilai-select"
                data-selected="{{ $selected }}">
            <option value="">Pilih...</option>

            @foreach([1,2,3,4,5] as $n)
                <option value="{{ $n }}" 
                    {{ $selected == $n ? 'selected' : '' }}>
                    {{ $n }} — {{ $labels[$n] }}
                </option>
            @endforeach
        </select>

    </td>
    @endforeach

</tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn btn-success mt-3">
                <i class="fas fa-save"></i> Simpan Semua Nilai
            </button>

        </form>

    </div>
</div>

@endsection
