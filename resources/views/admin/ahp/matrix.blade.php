@extends('layouts.app')
@section('content')

<h1 class="font-bold text-xl mb-4">Matriks Perbandingan AHP</h1>

<form method="POST">
    @csrf

    <table class="table-auto bg-white shadow">
        <thead>
            <tr>
                <th>Kriteria</th>
                @foreach($kriteria as $k)
                    <th>{{ $k->nama_kriteria }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
        @foreach($kriteria as $i => $k1)
            <tr>
                <td>{{ $k1->nama_kriteria }}</td>
                @foreach($kriteria as $j => $k2)
                    <td>
                        @if($i == $j)
                            1
                        @else
                            <input type="number" step="0.01" 
                                   name="matrix[{{ $k1->id }}][{{ $k2->id }}]"
                                   class="border p-1 w-20"
                                   value="{{ $values[$k1->id][$k2->id] ?? '' }}">
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>

    <button class="bg-green-600 text-white px-4 py-2 rounded mt-4">
        Simpan Matriks
    </button>
</form>

@endsection
