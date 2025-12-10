@extends('layouts.app')

@section('content')


<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Manajemen Kriteria & AHP</li>
</ol>

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
<div class="text-center mt-4">
    <form action="{{ route('admin.pipeline.run') }}" method="POST">
        @csrf
        <button class="btn btn-success btn-lg fw-bold">
            🚀 Jalankan Seluruh Evaluasi Lahan (Pipeline)
        </button>
    </form>
</div>


<div class="tab-content" id="myTabContent">

{{-- ===========================================
    TAB 1 — KRITERIA
============================================ --}}
<div class="tab-pane fade show active" id="kriteria" role="tabpanel">
    <h4 class="fw-bold mb-3">Daftar Kriteria</h4>
    <div class="mb-3 text-end">
        <button class="btn btn-primary" onclick="openKriteriaModal()">
            + Kriteria Baru
        </button>
    </div>
    <ul class="list-group">
@foreach($kriteria as $root)
    @if($root->parent_id == null)

        <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $root->nama_kriteria }}</strong>
                    <span class="badge bg-info">{{ $root->tipe }}</span>
                </div>

                <div>
                    <button class="btn btn-warning btn-sm"
                            onclick="openEditModal({{ $root->id }}, '{{ $root->nama_kriteria }}', '{{ $root->tipe }}')">
                        Edit
                    </button>

                    <form action="{{ route('admin.kriteria.destroy', $root->id) }}"
                          method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm"
                                onclick="return confirm('Hapus kriteria ini?')">
                            Hapus
                        </button>
                    </form>

                    <button class="btn btn-info btn-sm"
                            onclick="openSubForm({{ $root->id }}, '{{ $root->nama_kriteria }}')">
                        + Sub
                    </button>
                </div>
            </div>

            {{-- SUB KRITERIA --}}
            @if($root->sub->count())
                <ul class="mt-3 ms-4 list-group">
                    @foreach($root->sub as $child)
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                ↳ {{ $child->nama_kriteria }}
                                <span class="badge bg-secondary">{{ $child->tipe }}</span>
                            </div>
                            <div>
                               
                                <form action="{{ route('admin.kriteria.destroy', $child->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus sub-kriteria ini?')">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>

    @endif
@endforeach
</ul>




</div> <!-- END TAB KRITERIA -->


{{-- ===========================================
    TAB 2 — PAKAR
============================================ --}}
<div class="tab-pane fade" id="pakar" role="tabpanel">

    <h4 class="fw-bold mb-3">Daftar Pakar</h4>

    <form method="POST" action="{{ route('admin.ahp.experts.store') }}" class="mb-3">
    @csrf
    <div class="row g-1">
        <div class="col">
            <input name="name" class="form-control" placeholder="Nama Pakar" required>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Tambah</button>
        </div>
    </div>
</form>

<table class="table table-striped mb-4">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Status Matrix</th>
            <th width="200">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($experts as $ex)
        <tr>
            <td>{{ $ex->name }}</td>
            <td>
                @if($ex->has_matrix)
                    <span class="badge bg-success">Sudah Input</span>
                @else
                    <span class="badge bg-danger">Belum Input</span>
                @endif
            </td>
            <td>

                <!-- Button Edit -->
                <button class="btn btn-warning btn-sm"
                        onclick="openEditExpert({{ $ex->id }}, '{{ $ex->name }}')">
                    Edit
                </button>

                <!-- Button Delete -->
                <form action="{{ route('admin.ahp.experts.destroy', $ex->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">Hapus</button>
                </form>

                <!-- Input Matrix -->
                <a class="btn btn-secondary btn-sm"
                   href="{{ route('admin.ahp.experts.matrix', $ex->id) }}">
                    Matrix
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="parent_id" id="parent_id">

                <div class="mb-3">
                    <label class="form-label">Kriteria Induk</label>
                    <input type="text" id="parent_name" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sub Kriteria</label>
                    <select name="nama_kriteria" class="form-control" required>
                        <option value="">-- Pilih Sub Kriteria --</option>
                        @foreach($subKriteria as $sub)
                            <option value="{{ $sub }}">{{ $sub }}</option>
                        @endforeach
                    </select>
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
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>

        </form>

    </div>
  </div>
</div>

<!-- ===========================
     MODAL INPUT KRITERIA UTAMA
=========================== -->
<div class="modal fade" id="kriteriaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
        <form method="POST" action="{{ route('admin.kriteria.store') }}">
            @csrf
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Tambah Kriteria Utama</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="parent_id">
                <div class="mb-3">
                    <label class="form-label">Nama Kriteria</label>
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
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success">Simpan</button>
            </div>

        </form>
    </div>
  </div>
</div>

<!-- ===========================
     MODAL EDIT KRITERIA
=========================== -->
<div class="modal fade" id="editKriteriaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Kriteria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id" name="id">
                <div class="mb-3">
                    <label class="form-label">Nama Kriteria</label>
                    <input type="text" class="form-control" id="edit_nama" name="nama_kriteria" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipe</label>
                    <select class="form-select" id="edit_tipe" name="tipe">
                        <option value="benefit">Benefit</option>
                        <option value="cost">Cost</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
  </div>
</div>
<!-- ===========================
     MODAL EDIT PAKAR
=========================== -->
<div class="modal fade" id="expertModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

        <form method="POST" id="expertEditForm">
            @csrf
            @method('PUT')

            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">Edit Pakar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Nama Pakar</label>
                    <input type="text" name="name" id="expert_name" class="form-control" required>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-warning">Update</button>
            </div>

        </form>

    </div>
  </div>
</div>

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

function openKriteriaModal() {
    let modalEl = document.getElementById('kriteriaModal');
    let modal = new bootstrap.Modal(modalEl);
    modal.show();

    setTimeout(() => {
        modalEl.querySelector('input[name="nama_kriteria"]').focus();
    }, 300);
}

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

function openEditModal(id, nama, tipe) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_tipe').value = tipe;

    // set form action
    document.getElementById('editForm').action = "/admin/kriteria/" + id;

    let modal = new bootstrap.Modal(document.getElementById('editKriteriaModal'));
    modal.show();
}
function openEditExpert(id, name) {
    let modal = new bootstrap.Modal(document.getElementById('expertModal'));

    document.getElementById('expert_name').value = name;

    // Set action form
    document.getElementById('expertEditForm').action =
        "/admin/ahp/experts/" + id;

    modal.show();
}

document.addEventListener("DOMContentLoaded", function () {
    const hash = window.location.hash;

    if (hash === "#pakar") {
        let pakarTab = document.querySelector('button[data-bs-target="#pakar"]');
        if (pakarTab) {
            let tab = new bootstrap.Tab(pakarTab);
            tab.show();
        }
    }

    if (hash === "#hasil") {
        let hasilTab = document.querySelector('button[data-bs-target="#hasil"]');
        if (hasilTab) {
            let tab = new bootstrap.Tab(hasilTab);
            tab.show();
        }
    }
});

</script>
@if(session('open_tab'))
<script>
document.addEventListener("DOMContentLoaded", function () {
    let tab = "{{ session('open_tab') }}";
    if (tab) {
        let trigger = document.querySelector(`[data-bs-target="#${tab}"]`);
        if (trigger) {
            let tabObj = new bootstrap.Tab(trigger);
            tabObj.show();
        }
    }
});
</script>

@endif
@endsection
