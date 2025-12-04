@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Hasil Agregasi AHP Multi-Pakar</li>
</ol>

<div class="card">
    <div class="card-header">
        <h5 class="fw-bold">Bobot Akhir (Agregasi)</h5>
    </div>

    <div class="card-body">

        {{-- =======================
             TABEL BOBOT AKHIR
        ======================== --}}
        <h5>Bobot Kriteria</h5>

        <table class="table table-bordered table-striped w-50">
            <thead class="table-light">
                <tr>
                    <th>Kriteria</th>
                    <th>Bobot Akhir</th>
                </tr>
            </thead>

            <tbody>
                @foreach($bobotKriteria as $item)
                <tr>
                    <td>{{ $item['nama'] }}</td>
                    <td><strong>{{ number_format($item['bobot'], 4) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        @foreach($subBobots as $parentName => $subs)
            <h5 class="mt-4">Sub-Kriteria – {{ $parentName }}</h5>

            <table class="table table-bordered w-50">
                <thead>
                    <tr>
                        <th>Sub-Kriteria</th>
                        <th>Bobot Akhir</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($subs as $s)
                    <tr>
                        <td>{{ $s['nama'] }}</td>
                        <td><strong>{{ number_format($s['bobot'], 4) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        @endforeach

        <hr>

        {{-- =======================
             INFO KONSISTENSI
        ======================== --}}

        <h5 class="mt-4">Konsistensi Matriks Agregasi</h5>

        <table class="table table-bordered w-50">
            <tr><th>λ maks</th><td>{{ number_format($lambda, 4) }}</td></tr>
            <tr><th>CI</th><td>{{ number_format($CI, 4) }}</td></tr>
            <tr><th>CR</th><td>{{ number_format($CR, 4) }}</td></tr>
        </table>

        @if($CR <= 0.1)
            <div class="alert alert-success w-50">
                Matriks konsisten (CR < 0.1)
            </div>
        @else
            <div class="alert alert-danger w-50">
                Matriks tidak konsisten (CR ≥ 0.1)
            </div>
        @endif

    </div>
</div>

@endsection
