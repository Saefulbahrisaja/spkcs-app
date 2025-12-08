@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Manajemen Kriteria & AHP</li>
</ol>

<div class="card mb-4">
    <div class="card-header">
        <a href="{{ route('admin.kriteria.create') }}" class="btn btn-success btn-sm">Tambah Kriteria</a>
    </div>

    <div class="card-body">

        <!-- TAB MENU -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">

            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="kriteria-tab" data-bs-toggle="tab"
                        data-bs-target="#kriteria" type="button" role="tab">
                    Daftar Kriteria
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ahp-tab" data-bs-toggle="tab"
                        data-bs-target="#ahp" type="button" role="tab">
                    Matriks Perbandingan AHP
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bobot-tab" data-bs-toggle="tab"
                        data-bs-target="#bobot" type="button" role="tab">
                    Bobot AHP (Hasil)
                </button>
            </li>

        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content border p-3 bg-white" id="myTabContent">


            <!-- =============================================
                     TAB 1 – DAFTAR KRITERIA
            ============================================== -->
            <div class="tab-pane fade show active" id="kriteria" role="tabpanel">

    <div class="accordion" id="accordionKriteria">

        @foreach($kriteria as $k)
            @if($k->parent_id === null)

            <div class="accordion-item mb-2">
                <h2 class="accordion-header" id="heading{{ $k->id }}">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $k->id }}">
                        <strong>{{ $k->nama_kriteria }}</strong>
                        <span class="badge bg-secondary ms-2">{{ $k->tipe }}</span>
                    </button>
                </h2>

                <div id="collapse{{ $k->id }}" class="accordion-collapse collapse"
                     data-bs-parent="#accordionKriteria">
                    
                    <div class="accordion-body">

                        <!-- AKSI -->
                        <div class="mb-3">
                            <a href="{{ route('admin.kriteria.edit',$k->id) }}"
                               class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{ route('admin.kriteria.destroy',$k->id) }}"
                                  method="POST" 
                                  class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    Hapus
                                </button>
                            </form>

                            <button class="btn btn-info btn-sm"
                                    onclick="openSubForm({{ $k->id }}, '{{ $k->nama_kriteria }}')">
                                + Sub Kriteria
                            </button>
                        </div>

                        <!-- SUB KRITERIA LIST -->
                        @if($k->sub->count())
                            <ul class="list-group ms-3">
                                @foreach($k->sub as $s)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-angle-right me-2 text-primary"></i>
                                        {{ $s->nama_kriteria }}
                                        <span class="badge bg-secondary">{{ $s->tipe }}</span>
                                    </div>

                                    <div>
                                        <a href="{{ route('admin.kriteria.edit',$s->id) }}"
                                           class="btn btn-warning btn-sm">Edit</a>

                                        <form action="{{ route('admin.kriteria.destroy',$s->id) }}"
                                              class="d-inline"
                                              method="POST">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted ms-2">Belum ada sub-kriteria.</p>
                        @endif

                    </div>

                </div>
            </div>

            @endif
        @endforeach

    </div>

</div>
<!-- =============================================
     TAB 2 – AHP MATRIX INPUT (Combined)
============================================== -->
<div class="tab-pane fade" id="ahp" role="tabpanel">

    <h4 class="fw-bold mb-3">Matriks Perbandingan AHP (Kriteria & Sub-Kriteria)</h4>

    <div class="accordion" id="accordionAHP">

        <!-- =======================
             A. KRITERIA UTAMA
        ======================== -->
        <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="headingMainAHP">
                <button class="accordion-button fw-bold" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseMainAHP">
                    Matriks Perbandingan Kriteria Utama
                </button>
            </h2>

            <div id="collapseMainAHP" class="accordion-collapse collapse show">

                <div class="accordion-body">

                    <form method="POST" action="{{ route('admin.ahp.matrix.save') }}">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kriteria</th>
                                        @foreach($kriteria as $k)
                                            @if($k->parent_id === null)
                                                <th>{{ $k->nama_kriteria }}</th>
                                            @endif
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>

                                @php $parents = $kriteria->where("parent_id", null); @endphp

                                @foreach($parents as $i => $k1)

                                    <tr>
                                        <td class="text-start fw-semibold">{{ $k1->nama_kriteria }}</td>

                                        @foreach($parents as $j => $k2)
                                            <td>
                                                @if($i == $j)
                                                    <span class="fw-bold text-primary">1</span>

                                                @elseif($i < $j)

                                                    @php $val = $values[$k1->id][$k2->id] ?? null; @endphp

                                                    <select class="form-select form-select-sm ahp-select"
                                                            data-i="{{ $k1->id }}"
                                                            data-j="{{ $k2->id }}">
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

                                                    <input type="text"
                                                        class="form-control form-control-sm bg-light text-center"
                                                        readonly
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

                        <button class="btn btn-success mt-2">
                            <i class="fas fa-save"></i> Simpan Kriteria Utama
                        </button>

                    </form>

                </div>

            </div>
        </div>

        <!-- =======================
             B. SUB-KRITERIA (Per Parent)
        ======================== -->
        @foreach($parents as $parent)
            @if($parent->sub->count() > 1)

            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="headingParent{{ $parent->id }}">
                    <button class="accordion-button collapsed fw-bold" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseParent{{ $parent->id }}">
                        Matriks Sub-Kriteria – {{ $parent->nama_kriteria }}
                    </button>
                </h2>

                <div id="collapseParent{{ $parent->id }}" class="accordion-collapse collapse">
                    <div class="accordion-body">

                        <form method="POST" action="{{ route('admin.ahp.submatrix.save') }}">
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
                                            <td class="text-start fw-semibold">{{ $s1->nama_kriteria }}</td>

                                            @foreach($parent->sub as $j => $s2)

                                                <td>

                                                    @if($i == $j)
                                                        <span class="fw-bold text-primary">1</span>

                                                    @elseif($i < $j)

                                                        @php
                                                            $val = $subValues[$parent->id][$s1->id][$s2->id] ?? null;
                                                        @endphp

                                                        <select class="form-select form-select-sm ahp-sub-select"
                                                                data-i="{{ $s1->id }}"
                                                                data-j="{{ $s2->id }}"
                                                                data-parent="{{ $parent->id }}">
                                                            <option value="">Pilih…</option>

                                                            @foreach(range(1,9) as $num)
                                                                <option value="{{ $num }}"
                                                                    {{ $val == $num ? 'selected' : '' }}>
                                                                    {{ $num }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <input type="hidden"
                                                            name="matrix[{{ $parent->id }}][{{ $s1->id }}][{{ $s2->id }}]"
                                                            id="sub-{{ $s1->id }}-{{ $s2->id }}-{{ $parent->id }}"
                                                            value="{{ $val }}">

                                                    @else
                                                        @php
                                                            $val = $subValues[$parent->id][$s2->id][$s1->id] ?? null;
                                                            $rec = $val ? round(1 / $val, 4) : '';
                                                        @endphp

                                                        <input type="text"
                                                            class="form-control form-control-sm bg-light text-center"
                                                            readonly
                                                            id="sub-rec-{{ $s1->id }}-{{ $s2->id }}-{{ $parent->id }}"
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
                                <i class="fas fa-save"></i> Simpan Sub-Kriteria
                            </button>
                        </form>

                    </div>
                </div>

            </div>

            @endif
        @endforeach

    </div><!-- end accordion -->


    <!-- =================== HASIL PERHITUNGAN AHP =================== -->

    <h5 class="fw-bold mt-4">Hasil Perhitungan Konsistensi AHP</h5>

    <table class="table table-bordered w-50">
    <tr>
        <th>λ maks</th>
        <td>{{ number_format($hasil['kriteria']['lambda_max'], 4) }}</td>
    </tr>
    <tr>
        <th>Consistency Index (CI)</th>
        <td>{{ number_format($hasil['kriteria']['CI'], 4) }}</td>
    </tr>
    <tr>
        <th>Consistency Ratio (CR)</th>
        <td>{{ number_format($hasil['kriteria']['CR'], 4) }}</td>
    </tr>
</table>
@foreach($hasil['subkriteria'] as $parentId => $sub)
    <h5 class="mt-3">Sub-Kriteria dari ID {{ $parentId }}</h5>

    <table class="table table-bordered w-50">
        <tr>
            <th>λ maks</th>
            <td>{{ number_format($sub['lambda_max'], 4) }}</td>
        </tr>
        <tr>
            <th>CI</th>
            <td>{{ number_format($sub['CI'], 4) }}</td>
        </tr>
        <tr>
            <th>CR</th>
            <td>{{ number_format($sub['CR'], 4) }}</td>
        </tr>
    </table>
@endforeach

   @if($hasil['kriteria']['CR'] <= 0.1)
        <div class="alert alert-success w-50">
            Matriks **Konsisten**  (CR < 0.1)
        </div>
    @else
        <div class="alert alert-danger w-50">
            Matriks **Tidak Konsisten**  (CR ≥ 0.1)<br>
            Silakan perbaiki nilai perbandingan.
        </div>
    @endif
</div>
            <!-- =============================================
                     TAB 3 – AHP WEIGHTS
            ============================================== -->
            <div class="tab-pane fade" id="bobot" role="tabpanel">

                <h5 class="fw-bold mb-3">Bobot AHP (Hasil Perhitungan)</h5>

                <table class="table table-bordered table-striped w-50">
                    <thead class="table-light">
                        <tr>
                            <th>Kriteria</th>
                            <th>Bobot</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($kriteria as $k)
                        <tr>
                            <td>{{ $k->nama_kriteria }}</td>
                            <td><strong>{{ number_format($k->bobot, 4) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>

        </div><!-- end Tab Content -->

    </div><!-- end Card Body -->

</div><!-- end Card -->
<script>
// Gradasi merah → hijau
const ahpColors = {
    1: '#f56c6c',
    2: '#f1949b',
    3: '#f1b0b7',
    4: '#f5c6cb',
    5: '#ffe8a1',
    6: '#ffeeba',
    7: '#b1dfbb',
    8: '#c3e6cb',
    9: '#d4edda'
};

// Terapkan warna ke dropdown
function applyAhpColor(select) {
    const val = select.value;

    if (ahpColors[val]) {
        select.style.backgroundColor = ahpColors[val];
        select.style.fontWeight = "bold";
        select.style.color = "#000"; 
    } else {
        select.style.backgroundColor = "white";
        select.style.color = "#000";
    }
}

// Inisialisasi semua select AHP
document.querySelectorAll('.ahp-select').forEach(select => {
    applyAhpColor(select);

    select.addEventListener('change', function () {
        applyAhpColor(this);

        // update hidden input
        let i = this.dataset.i;
        let j = this.dataset.j;
        document.getElementById(`input-${i}-${j}`).value = this.value;

        // update reciprocal cell
        if (this.value) {
            let reciprocal = (1 / this.value).toFixed(4);
            let recCell = document.getElementById(`rec-${j}-${i}`);
            if (recCell) recCell.value = reciprocal;
        }
    });
});
</script>
<!-- =======================
     MODAL INPUT SUB-KRITERIA
========================== -->
<div class="modal fade" id="subModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
        <form method="POST" action="{{ route('admin.kriteria.store') }}">
            @csrf

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Sub-Kriteria</h5>
                <button type="button" class="btn-close" 
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <input type="text" name="parent_id" id="parent_id">
                <div class="mb-3">
                    <label class="form-label">Kriteria Induk</label>
                    <input type="text" id="parent_name" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Sub-Kriteria</label>
                    <input type="text" name="nama_kriteria" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipe</label>
                    <select class="form-select" name="tipe" required>
                        <option value="benefit">Benefit</option>
                        <option value="cost">Cost</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button class="btn btn-primary">
                    Simpan
                </button>
            </div>

        </form>

    </div>
  </div>
</div>
<script>
function openSubForm(id, name) {
    document.getElementById('parent_id').value = id;
    document.getElementById('parent_name').value = name;

    let modal = new bootstrap.Modal(document.getElementById('subModal'));
    modal.show();
}
</script>

@endsection
