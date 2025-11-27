@extends('layouts.app')
@section('content')

<h1 class="text-xl font-bold mb-4">Hasil Ranking VIKOR</h1>

<form method="POST" action="{{ route('admin.vikor.proses') }}">
    @csrf
    <button class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
        Proses VIKOR
    </button>
</form>

<table class="table-auto w-full bg-white shadow">
    <thead>
        <tr class="bg-gray-200">
            <th>Alternatif</th>
            <th>Q Value</th>
            <th>Ranking</th>
        </tr>
    </thead>

    <tbody>
    @foreach($data as $d)
        <tr>
            <td>{{ $d->alternatif->lokasi }}</td>
            <td>{{ $d->q_value }}</td>
            <td>{{ $d->hasil_ranking }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

@endsection
