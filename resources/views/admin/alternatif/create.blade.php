@extends('layouts.app')

@section('content')
<h1 class="text-xl font-bold mb-4">Input Nilai Alternatif (Grid Mode)</h1>

<form method="POST" action="{{ route('admin.alternatif.nilai.simpan') }}">
    @csrf

    <table class="table-auto w-full bg-white shadow border">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2 border">Alternatif</th>
                @foreach($kriteria as $k)
                    <th class="p-2 border text-center">{{ $k->nama_kriteria }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach($alternatifs as $alt)
            <tr>
                <td class="p-2 border font-semibold">{{ $alt->lokasi }}</td>

                @foreach($kriteria as $k)
                <td class="p-2 border text-center">
                    <input type="number"
                           name="nilai[{{ $alt->id }}][{{ $k->id }}]"
                           value="{{ $existing[$alt->id][$k->id] ?? '' }}"
                           class="border p-1 w-20 text-center"
                           min="1" max="5" required>
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <button class="bg-blue-600 text-white px-4 py-2 rounded mt-4">Simpan Semua Nilai</button>
</form>
@endsection
