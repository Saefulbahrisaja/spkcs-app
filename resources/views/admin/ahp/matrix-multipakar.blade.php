@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        AHP Multi-Pakar
    </li>
    <li class="breadcrumb-item active">
        Input Matriks – {{ $expert->name }}
    </li>
</ol>

<div class="card mb-4">

    <div class="card-header">
        <h5 class="mb-0">
            Matriks Perbandingan AHP untuk Pakar: 
            <strong>{{ $expert->name }}</strong>
        </h5>
    </div>

    <div class="card-body">

        {{-- ========================
            A. KRITERIA UTAMA
        ========================== --}}
        <h5 class="fw-bold">1. Matriks Perbandingan Kriteria Utama</h5>

        <form method="POST" action="{{ route('admin.ahp.matrix.save', $expert->id) }}">
            @csrf

            <div class="table-responsive mt-2">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kriteria</th>
                            @foreach($kriteria->where('parent_id',null) as $k)
                                <th>{{ $k->nama_kriteria }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @php
                        $parents = $kriteria->where('parent_id',null);
                        @endphp

                        @foreach($parents as $i => $k1)
                            <tr>
                                <td class="text-start fw-semibold">{{ $k1->nama_kriteria }}</td>

                                @foreach($parents as $j => $k2)
                                <td>

                                    @if($i == $j)
                                        <span class="fw-bold text-primary">1</span>

                                    @elseif($i < $j)
                                        @php
                                            $val = $values[$k1->id][$k2->id] ?? null;
                                        @endphp

                                        <select 
                                            class="form-select form-select-sm ahp-select"
                                            data-i="{{ $k1->id }}"
                                            data-j="{{ $k2->id }}"
                                        >
                                            <option value="">Pilih…</option>

                                            @foreach(range(1,9) as $num)
                                                <option value="{{ $num }}"
                                                    {{ $val == $num ? 'selected' : '' }}>
                                                    {{ $num }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input type="hidden"
                                            name="matrix[{{ $k1->id }}][{{ $k2->id }}]"
                                            id="input-{{ $k1->id }}-{{ $k2->id }}"
                                            value="{{ $val }}">

                                    @else
                                        @php
                                            $val = $values[$k2->id][$k1->id] ?? null;
                                            $rec = $val ? round(1/$val,4) : '';
                                        @endphp
                                        <input type="text" readonly
                                            class="form-control form-control-sm bg-light text-center"
                                            id="rec-{{ $k1->id }}-{{ $k2->id }}"
                                            value="{{ $rec }}">
                                    @endif

                                </td>
                                @endforeach

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn btn-success mt-3">
                Simpan Matriks Kriteria
            </button>
        </form>



        {{-- ========================
            B. SUB-KRITERIA
        ========================== --}}
        <hr class="my-4">
        <h5 class="fw-bold">2. Matriks Sub-Kriteria</h5>

        @foreach($parents as $parent)
            @if($parent->sub->count() > 1)
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <strong>{{ $parent->nama_kriteria }}</strong>
                </div>
                <div class="card-body">

                    <form method="POST" action="{{ route('admin.ahp.matrix.save', $expert->id) }}">
                        @csrf

                        <input type="hidden" name="parent_id" value="{{ $parent->id }}">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center">
                                <thead class="table-light">
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
                                        <td class="text-start fw-semibold">{{ $s1->nama_kriteria }}</td>

                                        @foreach($parent->sub as $j => $s2)
                                        <td>

                                            @if($i == $j)
                                                1
                                            @elseif($i < $j)

                                                @php
                                                    $val = $values[$s1->id][$s2->id] ?? null;
                                                @endphp

                                                <select 
                                                    class="form-select form-select-sm ahp-select-sub"
                                                    data-i="{{ $s1->id }}"
                                                    data-j="{{ $s2->id }}"
                                                >
                                                    <option value="">Pilih…</option>
                                                    @foreach(range(1,9) as $num)
                                                        <option value="{{ $num }}"
                                                            {{ $val == $num ? 'selected' : '' }}>
                                                            {{ $num }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <input type="hidden"
                                                    name="matrix[{{ $s1->id }}][{{ $s2->id }}]"
                                                    id="sub-{{ $s1->id }}-{{ $s2->id }}"
                                                    value="{{ $val }}">

                                            @else
                                                @php
                                                    $val = $values[$s2->id][$s1->id] ?? null;
                                                    $rec = $val ? round(1/$val,4) : '';
                                                @endphp

                                                <input type="text" readonly
                                                    class="form-control form-control-sm bg-light text-center"
                                                    id="sub-rec-{{ $s1->id }}-{{ $s2->id }}"
                                                    value="{{ $rec }}">
                                            @endif

                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        <button class="btn btn-primary mt-2">
                            Simpan Sub-Kriteria ({{ $parent->nama_kriteria }})
                        </button>
                    </form>

                </div>
            </div>
            @endif
        @endforeach

    </div> <!-- card-body -->

</div> <!-- card -->

<script>
/* ===========================================
   Warna dropdown nilai AHP
=============================================*/
const ahpColors = {
    1:'#f56c6c',2:'#f1949b',3:'#f1b0b7',4:'#f5c6cb',5:'#ffe8a1',
    6:'#ffeeba',7:'#b1dfbb',8:'#c3e6cb',9:'#d4edda'
};

function applyAhpColor(select){
    let v = select.value;
    select.style.background = ahpColors[v] ?? "white";
}

/* Event untuk matriks utama */
document.querySelectorAll('.ahp-select').forEach(sel=>{
    applyAhpColor(sel);
    sel.addEventListener('change', function(){
        applyAhpColor(this);
        let i = this.dataset.i;
        let j = this.dataset.j;
        document.getElementById(`input-${i}-${j}`).value = this.value;
        let rec = document.getElementById(`rec-${j}-${i}`);
        if(rec) rec.value = (1/this.value).toFixed(4);
    });
});

/* Event untuk sub-kriteria */
document.querySelectorAll('.ahp-select-sub').forEach(sel=>{
    applyAhpColor(sel);
    sel.addEventListener('change', function(){
        applyAhpColor(this);
        let i = this.dataset.i;
        let j = this.dataset.j;
        document.getElementById(`sub-${i}-${j}`).value = this.value;
        let rec = document.getElementById(`sub-rec-${j}-${i}`);
        if(rec) rec.value = (1/this.value).toFixed(4);
    });
});
</script>

@endsection
