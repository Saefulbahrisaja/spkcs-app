@extends('layouts.app')

@section('content')


<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Manajemen Kriteria & AHP</li>
</ol>
<!-- ==========================
     NAVIGATION TABS
=========================== -->
<ul class="nav nav-tabs mb-4" id="ahpTab" role="tablist">

    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="kriteria-tab"
            data-bs-toggle="tab" data-bs-target="#kriteria"
            type="button" role="tab">
            Data Kriteria
        </button>
    </li>

    <li class="nav-item" role="presentation">
        <button class="nav-link" id="pakar-tab"
            data-bs-toggle="tab" data-bs-target="#pakar"
            type="button" role="tab">
            Data Pakar & Matrix
        </button>
    </li>

    <li class="nav-item" role="presentation">
        <button class="nav-link" id="hasil-tab"
            data-bs-toggle="tab" data-bs-target="#hasil"
            type="button" role="tab">
            Hasil Agregasi SF-AHP
        </button>
    </li>

</ul>

<div class="tab-content" id="myTabContent">

{{-- ===========================================
    TAB 1 — KRITERIA
============================================ --}}
<div class="tab-pane fade show active" id="kriteria" role="tabpanel">

    <h4 class="fw-bold mb-3">Daftar Kriteria</h4>

    <div class="accordion" id="accordionKriteria">

        @foreach($kriteria as $k)
            @if($k->parent_id === null)

            <div class="accordion-item mb-3">

                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold"
                        data-bs-toggle="collapse"
                        data-bs-target="#kr{{ $k->id }}">
                        {{ $k->nama_kriteria }}
                        <span class="badge bg-secondary ms-2">{{ $k->tipe }}</span>
                    </button>
                </h2>

                <div id="kr{{ $k->id }}" class="accordion-collapse collapse">
                    <div class="accordion-body">

                        <div class="mb-2">
                            <a href="{{ route('admin.kriteria.edit',$k->id) }}"
                               class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{ route('admin.kriteria.destroy',$k->id)}}"
                                method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                            <button class="btn btn-info btn-sm"
                                    onclick="openSubForm({{ $k->id }}, '{{ $k->nama_kriteria }}')">
                                + Sub Kriteria
                            </button>
                        </div>

                        {{-- LIST SUB KRITERIA --}}
                        @if($k->sub->count())
                            <ul class="list-group">
                                @foreach($k->sub as $s)
                                <li class="list-group-item d-flex justify-content-between">
                                    <div>
                                        ➤ {{ $s->nama_kriteria }}
                                        <span class="badge bg-secondary">{{ $s->tipe }}</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.kriteria.edit',$s->id) }}"
                                           class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('admin.kriteria.destroy',$s->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Belum ada sub-kriteria.</p>
                        @endif

                    </div>
                </div>

            </div>

            @endif
        @endforeach
    </div>

</div> <!-- END TAB KRITERIA -->


{{-- ===========================================
    TAB 2 — PAKAR
============================================ --}}
<div class="tab-pane fade" id="pakar" role="tabpanel">

    <h4 class="fw-bold mb-3">Daftar Pakar</h4>

    <form method="POST" action="{{ route('admin.ahp.experts.store') }}" class="mb-3">
        @csrf
        <div class="row g-2">
            <div class="col"><input name="name" class="form-control" placeholder="Nama Pakar" required></div>
            <div class="col"><input name="weight" class="form-control" placeholder="Bobot Pakar"></div>
            <div class="col-auto"><button class="btn btn-primary">Tambah</button></div>
        </div>
    </form>

    <table class="table table-striped mb-4">
        <thead><tr><th>Nama</th><th>Bobot</th><th>Aksi</th></tr></thead>
        <tbody>
            @foreach($experts as $ex)
            <tr>
                <td>{{ $ex->name }}</td>
                <td>
                @if($ex->has_matrix)
                    <span class="badge bg-success">Sudah Melakukan Pembobotan</span>
                @else
                    <span class="badge bg-danger">Belum Input</span>
                @endif
                </td>
                <td>
                    <a class="btn btn-sm btn-secondary"
                       href="{{ route('admin.ahp.experts.matrix', $ex->id) }}">
                        Input Matrix
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <form method="POST" action="{{ route('admin.ahp.aggregate') }}">
        @csrf
        <button class="btn btn-success btn-sm">Hitung Aggregate & Bobot</button>
    </form>

</div> <!-- END TAB PAKAR -->


{{-- ===========================================
    TAB 3 — HASIL
============================================ --}}
<div class="tab-pane fade" id="hasil" role="tabpanel">

    <h4 class="fw-bold mb-3">Aggregated Spherical Fuzzy Matrix (SWGM)</h4>
    {{-- AGGREGATED FUZZY MATRIX --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            Aggregated Spherical Fuzzy Matrix (SWGM)
        </div>
        <div class="card-body">
           @include('admin.ahp.partials.hasil_matrix')
        </div>
    </div>

    {{-- SCORE / WEIGHT --}}
    <div class="card mb-4">
        <div class="card-header bg-success text-white fw-bold">
            Bobot Kriteria
        </div>
        <div class="card-body">
             @include('admin.ahp.partials.hasil_bobot')
        </div>
    </div>

</div> <!-- END TAB HASIL -->

</div> 
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

                <input type="hidden" name="parent_id" id="parent_id">
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
</div><!-- tab-content END -->
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

function openSubForm(id, name) {
    document.getElementById('parent_id').value = id;
    document.getElementById('parent_name').value = name;

    let modal = new bootstrap.Modal(document.getElementById('subModal'));
    modal.show();
}
</script>
@endsection
