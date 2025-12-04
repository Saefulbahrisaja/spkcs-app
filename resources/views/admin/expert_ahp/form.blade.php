@extends('layouts.app')

@section('content')

<h3 class="mb-3">
    Input Matriks AHP - Pakar:
    <span class="text-primary">{{ $expert->name }}</span>
</h3>

<form method="POST" action="{{ route('expert.ahp.save', $expert->id) }}">
    @csrf

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
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
                    <td class="text-start fw-semibold">{{ $k1->nama_kriteria }}</td>

                    @foreach($kriteria as $j => $k2)
                        <td>

                            @if($i == $j)
                                <strong class="text-primary">1</strong>

                            @elseif($i < $j)

                                @php $val = $values[$k1->id][$k2->id] ?? null; @endphp

                                <select class="form-select form-select-sm"
                                        name="matrix[{{ $k1->id }}][{{ $k2->id }}]">
                                    <option value="">Pilih…</option>
                                    @foreach(range(1,9) as $num)
                                        <option value="{{ $num }}"
                                            {{ $num == $val ? 'selected' : '' }}>
                                            {{ $num }}
                                        </option>
                                    @endforeach
                                </select>

                            @else

                                @php
                                    $v = $values[$k2->id][$k1->id] ?? null;
                                    $rec = $v ? round(1 / $v, 4) : '';
                                @endphp

                                <input type="text" class="form-control form-control-sm bg-light text-center"
                                       readonly value="{{ $rec }}">

                            @endif

                        </td>
                    @endforeach

                </tr>
            @endforeach

            </tbody>

        </table>
    </div>

    <button class="btn btn-primary mt-3">
        <i class="fas fa-save"></i> Simpan Matriks Pakar
    </button>

</form>

@endsection
