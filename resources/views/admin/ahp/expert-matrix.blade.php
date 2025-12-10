@extends('layouts.app')

@section('content')
@php
$sfScale = [
    'EI'  => [0.5, 0.4, 0.4],
    'SMI' => [0.6, 0.4, 0.3],
    'HI'  => [0.7, 0.3, 0.2],
    'VHI' => [0.8, 0.2, 0.1],
    'AMI' => [0.9, 0.1, 0.0],
];

function sfClosestLabel($tuple, $sfScale) {
    if (!is_array($tuple)) return 'EI';
    
    $best = 'EI';
    $minD = 999;

    foreach ($sfScale as $label => $t) {
        $d = sqrt(
            pow($tuple[0] - $t[0], 2) +
            pow($tuple[1] - $t[1], 2) +
            pow($tuple[2] - $t[2], 2)
        );
        
        if ($d < $minD) {
            $minD = $d;
            $best = $label;
        }
    }
    
    return $best;
}
@endphp

<h4 class="fw-bold mb-3">Input kriteria untuk pakar: {{ $expert->name }}</h4>

<a href="{{ route('admin.ahp.experts') }}#pakar" class="btn btn-secondary mb-3">
    ← Kembali ke Tab Pakar
</a>

<div class="accordion" id="sfAHPAccordion">
    <!-- Main Criteria Matrix -->
    <div class="accordion-item mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button fw-bold" data-bs-toggle="collapse" data-bs-target="#mainCriteria">
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
                                        @if($i == $j)
                                            <!-- Diagonal -->
                                            <span class="text-primary fw-bold">(0.5,0.4,0.4)</span>
                                        @elseif($i < $j)
                                            <!-- Editable Upper Triangle -->
                                            @php
                                                $tuple = $values[$k1->id][$k2->id] ?? null;

                                                if (is_array($tuple) && isset($tuple['mu'])) {
                                                    $tuple = [$tuple['mu'], $tuple['nu'], $tuple['pi']];
                                                }
                                                if (!$tuple) $tuple = $sfScale['EI'];

                                                $detected = sfClosestLabel($tuple, $sfScale);
                                            @endphp

                                            <input type="hidden" id="tuple-{{ $k1->id }}-{{ $k2->id }}" 
                                                   value="({{ number_format($tuple[0], 2) }}, {{ number_format($tuple[1], 2) }}, {{ number_format($tuple[2], 2) }})">

                                            <select class="form-select form-select-sm sfahp" 
                                                    name="matrix[{{ $k1->id }}][{{ $k2->id }}]"
                                                    data-i="{{ $k1->id }}" data-j="{{ $k2->id }}"
                                                    id="sel-{{ $k1->id }}-{{ $k2->id }}">
                                                @foreach($sfScale as $label => $t)
                                                    <option value="{{ $label }}" {{ $detected == $label ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <!-- Reciprocal Lower Triangle -->
                                            <input type="text" readonly class="form-control text-center bg-light reciprocal-cell"
                                                   id="rec-{{ $k1->id }}-{{ $k2->id }}" value="">
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

    <!-- Sub-Criteria Matrices -->
    @foreach($parents as $parent)
    @if($parent->sub->count() > 1)
    <div class="accordion-item mb-3">
        <h2 class="accordion-header">
            <button class="accordion-button fw-bold collapsed" data-bs-toggle="collapse" data-bs-target="#sub{{ $parent->id }}">
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
                                        @if($i == $j)
                                            <!-- Diagonal -->
                                            <span class="text-primary fw-bold">(0.5,0.4,0.4)</span>
                                        @elseif($i < $j)
                                            <!-- Editable Upper Triangle -->
                                            @php
                                                $tuple = $subValues[$parent->id][$s1->id][$s2->id] ?? null;

                                                if (is_array($tuple) && isset($tuple['mu'])) {
                                                    $tuple = [$tuple['mu'], $tuple['nu'], $tuple['pi']];
                                                }
                                                if (!$tuple) $tuple = $sfScale['EI'];

                                                $detected = sfClosestLabel($tuple, $sfScale);
                                            @endphp

                                            <input type="hidden" id="tuple-{{ $s1->id }}-{{ $s2->id }}" 
                                                   value="({{ number_format($tuple[0], 2) }}, {{ number_format($tuple[1], 2) }}, {{ number_format($tuple[2], 2) }})">

                                            <select class="form-select form-select-sm sfahp" 
                                                    name="submatrix[{{ $parent->id }}][{{ $s1->id }}][{{ $s2->id }}]"
                                                    data-i="{{ $s1->id }}" data-j="{{ $s2->id }}"
                                                    id="sel-{{ $s1->id }}-{{ $s2->id }}">
                                                @foreach($sfScale as $label => $t)
                                                    <option value="{{ $label }}" {{ $detected == $label ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <!-- Reciprocal Lower Triangle -->
                                            <input type="text" readonly class="form-control text-center bg-light reciprocal-cell"
                                                   id="rec-{{ $s1->id }}-{{ $s2->id }}" value="">
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

<script>
/* Spherical Fuzzy Scale (Gundogdu & Kahraman 2019) */
const sfScale = {
    'EI': [0.50, 0.40, 0.40],
    'SMI': [0.60, 0.40, 0.30],
    'HI': [0.70, 0.30, 0.20],
    'VHI': [0.80, 0.20, 0.10],
    'AMI': [0.90, 0.10, 0.00],
};

const sfColor = {
    'EI': '#d6eaff',
    'SMI': '#c8f7d2',
    'HI': '#b0ffcf',
    'VHI': '#ffe6a4',
    'AMI': '#ffb5b5'
};

/* Reciprocal Spherical Fuzzy: (μ', ν', π') = (ν, μ, π) */
function reciprocalSF(tuple) {
    return [tuple[1], tuple[0], tuple[2]];
}

/* Format tuple display */
function fmt(t) {
    return '(' + t.map(v => Number(v).toFixed(2)).join(', ') + ')';
}

/* Handle select change: update tuple and reciprocal */
document.querySelectorAll('.sfahp').forEach(sel => {
    sel.style.backgroundColor = sfColor[sel.value] ?? 'white';

    sel.addEventListener('change', function() {
        const i = this.dataset.i;
        const j = this.dataset.j;
        const label = this.value;

        sel.style.backgroundColor = sfColor[label] ?? 'white';

        const tuple = sfScale[label];
        if (!tuple) return;

        const topTupleEl = document.getElementById(`tuple-${i}-${j}`);
        if (topTupleEl) {
            topTupleEl.value = fmt(tuple);
        }

        const reciprocal = reciprocalSF(tuple);
        const recipEl = document.getElementById(`rec-${j}-${i}`);
        if (recipEl) {
            recipEl.value = fmt(reciprocal);
        }
    });
});

/* Initialize on page load */
window.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.sfahp').forEach(sel => {
        const i = sel.dataset.i;
        const j = sel.dataset.j;
        const label = sel.value;
        const tuple = sfScale[label];

        if (!tuple) return;

        const topTupleEl = document.getElementById(`tuple-${i}-${j}`);
        if (topTupleEl) {
            topTupleEl.value = fmt(tuple);
        }

        const reciprocal = reciprocalSF(tuple);
        const recipEl = document.getElementById(`rec-${j}-${i}`);
        if (recipEl) {
            recipEl.value = fmt(reciprocal);
        }
    });
});
</script>

@endsection
