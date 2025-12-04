@extends('layouts.app')

@section('content')
<h1 class="text-xl font-bold mb-4">Daftar Nilai Alternatif</h1>

<div class="bg-white shadow rounded p-4">
    <table class="w-full table-auto border text-sm">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="p-2 border">Alternatif</th>
                @foreach($kriteria as $k)
                    <th class="p-2 border">{{ $k->nama_kriteria }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($alternatifs as $alt)
            <tr>
                <td class="p-2 border font-semibold">{{ $alt->lokasi }}</td>
                @foreach($kriteria as $k)
                    @php
                        $nilai = $alt->nilai->where('kriteria_id', $k->id)->first();
                    @endphp
                    <td class="p-2 border text-center">
                        {{ $nilai->nilai ?? '-' }}
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
