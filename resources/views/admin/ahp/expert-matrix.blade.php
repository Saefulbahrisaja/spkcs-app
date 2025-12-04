@extends('layouts.app')
@section('content')

<h4 class="mb-4">Input Matrix AHP untuk Pakar: <strong>{{ $expert->name }}</strong></h4>


<div class="accordion" id="accordionExpert">

    <!-- =====================================================
         BAGIAN 1 — MATRIX KRITERIA UTAMA UNTUK PAKAR
    ====================================================== -->
    <div class="accordion-item mb-3">
        <h2 class="accordion-header" id="headingMain">
            <button class="accordion-button fw-bold" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseMain">
                Matriks Perbandingan Kriteria Utama
            </button>
        </h2>

        <div id="collapseMain" class="accordion-collapse collapse show">
            <div class="accordion-body">

                <form method="POST" action="{{ route('admin.ahp.experts.matrix.save', $expert->id) }}">
                    @csrf

                    <h5 class="fw-bold mb-3">Kriteria Utama</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Kriteria</th>
                                    @foreach($parents as $p)
                                        <th>{{ $p->nama_kriteria }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($parents as $i => $k1)
                                <tr>
                                    <td class="text-start fw-semibold">{{ $k1->nama_kriteria }}</td>

                                    @foreach($parents as $j => $k2)
                                        <td>
                                            @if($i == $j)
                                                <strong class="text-primary">1</strong>
                                            @elseif($i < $j)

                                                @php
                                                    // aman, tidak memunculkan undefined key
                                                    $val = optional(
                                                                optional($existing[$k1->id] ?? collect())
                                                                ->where('kriteria_2_id', $k2->id)
                                                                ->first()
                                                           )->nilai_perbandingan ?? '';
                                                @endphp

                                                <select class="form-select form-select-sm"
                                                    name="matrix[{{ $k1->id }}][{{ $k2->id }}]">
                                                    <option value="">-</option>
                                                    @foreach(range(1,9) as $num)
                                                        <option value="{{ $num }}"
                                                            {{ $val == $num ? 'selected' : '' }}>
                                                            {{ $num }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @else
                                                @php
                                                    $rv = optional(
                                                            optional($existing[$k2->id] ?? collect())
                                                                ->where('kriteria_2_id', $k1->id)
                                                                ->first()
                                                         )->nilai_perbandingan;

                                                    $rec = $rv ? round(1/$rv, 4) : '';
                                                @endphp
                                                <input type="text"
                                                    class="form-control form-control-sm text-center bg-light"
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
                        <i class="fas fa-save"></i> Simpan Matriks Kriteria Utama
                    </button>
                </form>

            </div>
        </div>
    </div>





    <!-- =====================================================
         BAGIAN 2 — MATRIX SUB-KRITERIA PER KRITERIA INDUK
    ====================================================== -->
    @foreach($parents as $parent)

        @if($parent->sub->count() > 1)

        <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="headingSub{{ $parent->id }}">
                <button class="accordion-button collapsed fw-bold" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseSub{{ $parent->id }}">
                    Matriks Sub-Kriteria — {{ $parent->nama_kriteria }}
                </button>
            </h2>

            <div id="collapseSub{{ $parent->id }}" class="accordion-collapse collapse">
                <div class="accordion-body">

                    <form method="POST"
                        action="{{ route('admin.ahp.submatrix.save', $expert->id) }}">
                        @csrf

                        <input type="hidden" name="parent_id" value="{{ $parent->id }}">

                        <h5 class="fw-bold mb-3">Sub-Kriteria: {{ $parent->nama_kriteria }}</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Sub-Kriteria</th>
                                        @foreach($parent->sub as $s)
                                            <th>{{ $s->nama_kriteria }}</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach($parent->sub as $i => $s1)
                                    <tr>
                                        <td class="text-start fw-semibold">
                                            {{ $s1->nama_kriteria }}
                                        </td>

                                        @foreach($parent->sub as $j => $s2)
                                        <td>

                                            @if($i == $j)
                                                <strong class="text-primary">1</strong>

                                            @elseif($i < $j)

                                                @php
                                                    $val = optional(
                                                                optional($existingSub[$parent->id][$s1->id] ?? collect())
                                                                    ->where('kriteria_2_id', $s2->id)
                                                                    ->first()
                                                           )->nilai_perbandingan ?? '';
                                                @endphp

                                                <select class="form-select form-select-sm"
                                                    name="submatrix[{{ $parent->id }}][{{ $s1->id }}][{{ $s2->id }}]">
                                                    <option value="">-</option>
                                                    @foreach(range(1,9) as $num)
                                                        <option value="{{ $num }}"
                                                            {{ $val == $num ? 'selected' : '' }}>
                                                            {{ $num }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @else

                                                @php
                                                    $rv = optional(
                                                            optional($existingSub[$parent->id][$s2->id] ?? collect())
                                                                ->where('kriteria_2_id', $s1->id)
                                                                ->first()
                                                         )->nilai_perbandingan;

                                                    $rec = $rv ? round(1/$rv, 4) : '';
                                                @endphp

                                                <input type="text"
                                                    class="form-control form-control-sm bg-light text-center"
                                                    readonly value="{{ $rec }}">
                                            @endif

                                        </td>
                                        @endforeach

                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                        <button class="btn btn-info mt-2">
                            <i class="fas fa-save"></i> Simpan Sub-Kriteria
                        </button>
                    </form>

                </div>
            </div>
        </div>

        @endif

    @endforeach

</div>

@endsection
