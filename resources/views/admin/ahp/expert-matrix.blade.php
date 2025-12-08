@extends('layouts.app')

@section('content')

<h4 class="fw-bold mb-3">Input kriteria untuk pakar: {{ $expert->name }}</h4>
<div class="accordion" id="sfAHPAccordion">


<!-- ======================================================
     A. MATRIX KRITERIA UTAMA
====================================================== -->
<div class="accordion-item mb-3">
    <h2 class="accordion-header">
        <button class="accordion-button fw-bold" data-bs-toggle="collapse"
                data-bs-target="#mainCriteria">
            Matriks Kriteria Utama
        </button>
    </h2>

    <div id="mainCriteria" class="accordion-collapse collapse show">
        <div class="accordion-body">

            <form action="{{ route('admin.ahp.experts.matrix.save', $expert->id) }}" method="POST">
                @csrf

                <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
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
                            <td class="text-start fw-bold">{{ $k1->nama_kriteria }}</td>

                            @foreach($parents as $j => $k2)
                            <td>

                                {{-- DIAGONAL --}}
                                @if($i == $j)
                                    <span class="text-primary fw-bold">(0.5,0.4,0.4)</span>
                                {{-- ATAS (editable) --}}
                                @elseif($i < $j)

                                    @php
                                        $current = $values[$k1->id][$k2->id] ?? '';
                                    @endphp

                                    <select class="form-select form-select-sm sfahp"
                                            name="matrix[{{ $k1->id }}][{{ $k2->id }}]"
                                            data-i="{{ $k1->id }}"
                                            data-j="{{ $k2->id }}">
                                        <option value="EI"  {{ $current=='EI'  ? 'selected':'' }}>Equal Importance</option>
                                        <option value="SMI" {{ $current=='SMI' ? 'selected':'' }}>Slightly more Important</option>
                                        <option value="HI"  {{ $current=='HI'  ? 'selected':'' }}>High Important</option>
                                        <option value="VHI" {{ $current=='VHI' ? 'selected':'' }}>Very high Important</option>
                                        <option value="AMI" {{ $current=='AMI' ? 'selected':'' }}>Absolute more Important</option>
                                    </select>

                                {{-- BAWAH (auto-reciprocal) --}}
                                @else
                                    @php
                                        $rev = $values[$k2->id][$k1->id] ?? null;
                                    @endphp

                                    <input type="text"
                                        readonly
                                        class="form-control text-center bg-light"
                                        id="{{ $k2->id }}-{{ $k1->id }}"
                                        value="{{ $rev ? '('.(is_array($rev) ? implode(',', $rev) : $rev).')' : '' }}">
                                @endif

                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                <button class="btn btn-primary mt-2">Simpan Matrix Kriteria</button>
            </form>

        </div>
    </div>
</div>




<!-- ======================================================
     B. MATRIX SUB-KRITERIA
====================================================== -->
@foreach($parents as $parent)
@if($parent->sub->count() > 1)

<div class="accordion-item mb-3">
    <h2 class="accordion-header">
        <button class="accordion-button fw-bold collapsed" data-bs-toggle="collapse"
                data-bs-target="#sub{{ $parent->id }}">
            Sub-Kriteria — {{ $parent->nama_kriteria }}
        </button>
    </h2>

    <div id="sub{{ $parent->id }}" class="accordion-collapse collapse">
        <div class="accordion-body">

            <form method="POST" action="{{ route('admin.ahp.experts.matrix.save', $expert->id) }}">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $parent->id }}">

                <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
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
                            <td class="text-start fw-bold">{{ $s1->nama_kriteria }}</td>

                            @foreach($parent->sub as $j => $s2)
                            <td>

                                {{-- diagonal --}}
                                @if($i == $j)
                                    <span class="text-primary fw-bold">(0.5,0.4,0.4)</span>

                                {{-- editable --}}
                                @elseif($i < $j)

                                    @php
                                        $current = $subValues[$parent->id][$s1->id][$s2->id] ?? '';
                                    @endphp

                                    <select class="form-select form-select-sm sfahp"
                                            name="submatrix[{{ $parent->id }}][{{ $s1->id }}][{{ $s2->id }}]"
                                            data-i="{{ $s1->id }}"
                                            data-j="{{ $s2->id }}">
                                        <option value="EI"  {{ $current=='EI'  ? 'selected':'' }}>Equal Importance</option>
                                        <option value="SMI" {{ $current=='SMI' ? 'selected':'' }}>Slightly more Important</option>
                                        <option value="HI"  {{ $current=='HI'  ? 'selected':'' }}>High Important</option>
                                        <option value="VHI" {{ $current=='VHI' ? 'selected':'' }}>Very high Important</option>
                                        <option value="AMI" {{ $current=='AMI' ? 'selected':'' }}>Absolute more Important</option>
                                    </select>

                                {{-- reciprocal --}}
                                @else
                                    @php
                                        $rev = $subValues[$parent->id][$s2->id][$s1->id] ?? null;
                                    @endphp

                                    <input type="text"
                                        readonly
                                        class="form-control text-center bg-light"
                                        id="rec-{{ $s2->id }}-{{ $s1->id }}"
                                       value="{{ $rev ? '('.(is_array($rev) ? implode(',', $rev) : $rev).')' : '' }}">
                                @endif

                            </td>
                            @endforeach

                        </tr>
                        @endforeach
                    </tbody>

                </table>
                </div>

                <button class="btn btn-success mt-2">Simpan Matrix Sub-Kriteria</button>

            </form>

        </div>
    </div>
</div>

@endif
@endforeach



</div>


<!-- ======================================================
     JAVASCRIPT AUTOSYNC RECIPROCAL
====================================================== -->
<script>

const fuzzyMap = {
    'E': 1,
    'SI': 3,
    'I': 5,
    'VI': 7,
    'EI': 9
};

const sfColor = {
    'E':'#d6eaff',
    'SI':'#c2f2d0',
    'I':'#a8ffc4',
    'VI':'#ffdc91',
    'EI':'#ffa8a8'
};


document.querySelectorAll('.sfahp').forEach(sel => {

    // Warna awal
    sel.style.backgroundColor = sfColor[sel.value] ?? 'white';

    sel.addEventListener('change', function(){

        const i = sel.dataset.i;
        const j = sel.dataset.j;
        const v = sel.value;

        sel.style.backgroundColor = sfColor[v] ?? 'white';

        if (!v || !i || !j) return;

        // reciprocal input
        const recipText = document.getElementById(`rec-${j}-${i}`);

        if (recipText) {
            recipText.value = `(${v})`;
        }
    });
});

</script>

@endsection
