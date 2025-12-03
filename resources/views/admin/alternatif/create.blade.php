@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ route('admin.wilayah.index') }}">Daftar Lokasi</a></li>
    <li class="breadcrumb-item active">Input Nilai Alternatif</li>
</ol>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <strong>Input Nilai Alternatif (Grid Mode)</strong>
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
                                <input type="number"
                                       name="nilai[{{ $alt->id }}][{{ $k->id }}]"
                                       value="{{ $existing[$alt->id][$k->id] ?? '' }}"
                                       class="form-control text-center"
                                       min="1" max="5" required>
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
