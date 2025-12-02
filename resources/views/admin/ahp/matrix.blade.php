@extends('layouts.app')

@section('content')

<h1 class="font-bold text-xl mb-4">Matriks Perbandingan AHP</h1>

<form method="POST" action="{{ route('admin.ahp.matrix.save') }}">
    @csrf

    <div class="overflow-auto bg-white rounded shadow p-4">
        <table class="table-auto border-collapse w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2 text-left">Kriteria</th>
                    @foreach($kriteria as $k)
                        <th class="border p-2 text-center">{{ $k->nama_kriteria }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
            @foreach($kriteria as $i => $k1)
                <tr>
                    <td class="border p-2 font-semibold bg-gray-50">{{ $k1->nama_kriteria }}</td>

                    @foreach($kriteria as $j => $k2)
                        <td class="border p-2 text-center">

                            {{-- Diagonal selalu 1 --}}
                            @if($i == $j)
                                <span class="font-bold text-blue-600">1</span>

                            {{-- Bagian atas matrix → input --}}
                            @elseif($i < $j)
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="1" max="9"
                                    name="matrix[{{ $k1->id }}][{{ $k2->id }}]"
                                    class="border p-1 w-20 text-center rounded"
                                    value="{{ old('matrix.'.$k1->id.'.'.$k2->id, $values[$k1->id][$k2->id] ?? '') }}"
                                >

                            {{-- Bagian bawah matrix → auto reciprocal --}}
                            @else
                                @php
                                    $val = $values[$k2->id][$k1->id] ?? null;
                                    $reciprocal = $val ? round(1 / $val, 4) : '';
                                @endphp

                                <input 
                                    type="text" 
                                    class="border p-1 w-20 text-center bg-gray-100 text-gray-500 rounded"
                                    value="{{ $reciprocal }}"
                                    readonly
                                >
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <button class="bg-green-600 text-white px-5 py-2 rounded mt-4 hover:bg-green-700">
        Simpan Matriks
    </button>
</form>

@endsection
