@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ route('admin.kriteria.index') }}">Daftar Kriteria</a></li>
    <li class="breadcrumb-item active">Matriks Perbandingan AHP</li>
</ol>

<div class="card mb-4 shadow">
    <div class="card-header bg-primary text-white">
        <strong>Matriks Perbandingan Kriteria (AHP)</strong>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('admin.ahp.matrix.save') }}">
            @csrf

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-start">Kriteria</th>
                            @foreach($kriteria as $k)
                                <th>{{ $k->nama_kriteria }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($kriteria as $i => $k1)
                        <tr>
                            {{-- Nama Kriteria --}}
                            <td class="fw-bold text-start">{{ $k1->nama_kriteria }}</td>

                            @foreach($kriteria as $j => $k2)
                                <td>

                                    {{-- Diagonal selalu 1 --}}
                                    @if($i == $j)
                                        <span class="fw-bold text-primary">1</span>

                                    {{-- Bagian atas matrix → input --}}
                                    @elseif($i < $j)
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="1" max="9"
                                            name="matrix[{{ $k1->id }}][{{ $k2->id }}]"
                                            class="form-control text-center"
                                            value="{{ old('matrix.'.$k1->id.'.'.$k2->id, $values[$k1->id][$k2->id] ?? '') }}"
                                        >

                                    {{-- Bagian bawah → otomatis reciprocal --}}
                                    @else
                                        @php
                                            $val = $values[$k2->id][$k1->id] ?? null;
                                            $reciprocal = $val ? round(1 / $val, 4) : '';
                                        @endphp

                                        <input 
                                            type="text" 
                                            class="form-control text-center bg-light text-muted"
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

            <div class="mt-4 d-flex gap-3">
                <button class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Matriks
                </button>

                
            </div>

        </form>
    </div>
</div>

@endsection
