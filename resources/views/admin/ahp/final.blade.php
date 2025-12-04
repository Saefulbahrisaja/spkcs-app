@extends('layouts.app')
@section('content')
<h4>Hasil Agregasi & Bobot</h4>

<table class="table table-bordered">
    <thead><tr><th>Kriteria</th><th>Bobot</th></tr></thead>
    <tbody>
    @foreach($items as $i => $item)
        <tr>
            <td>{{ $item->nama_kriteria }}</td>
            <td>{{ number_format($weights['eigenvector'][$i] ?? 0, 4) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h5>CI: {{ number_format($weights['CI'] ?? 0,4) }} — CR: {{ number_format($weights['CR'] ?? 0,4) }}</h5>
@endsection
