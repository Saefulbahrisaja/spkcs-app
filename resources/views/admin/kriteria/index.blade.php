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

                <table class="table table-bordered table-striped mt-3" id="datatablesSimple">
                    <thead class="table-secondary">
                        <tr>
                            <th>Nama Kriteria</th>
                            <th>Tipe</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($kriteria as $k)
                        <tr>
                            <td>{{ $k->nama_kriteria }}</td>
                            <td>{{ $k->tipe }}</td>
                            
                            <td>
                                <a class="btn btn-warning btn-sm" 
                                   href="{{ route('admin.kriteria.edit',$k->id) }}">
                                    Edit
                                </a>

                                <form action="{{ route('admin.kriteria.destroy',$k->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus Kriteria?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>
            <!-- =============================================
                     TAB 2 – AHP MATRIX INPUT
            ============================================== -->
            <div class="tab-pane fade" id="ahp" role="tabpanel">

    <h5 class="fw-bold mb-3">Matriks Perbandingan Berpasangan (AHP)</h5>

    <form method="POST" action="{{ route('admin.ahp.matrix.save') }}">
        @csrf

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center" id="ahpTable">
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
                        <td class="fw-semibold text-start">{{ $k1->nama_kriteria }}</td>

                        @foreach($kriteria as $j => $k2)
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
                                        data-selected="{{ $val }}"
                                    >
                                        <option value="">Pilih…</option>

                                        @foreach([1,2,3,4,5,6,7,8,9] as $num)
                                            <option value="{{ $num }}"
                                                {{ $val == $num ? 'selected' : '' }}>
                                                {{ $num }} –
                                                @switch($num)
                                                    @case(1) Sama penting @break
                                                    @case(2) Antara 1 & 3 @break
                                                    @case(3) Sedikit lebih penting @break
                                                    @case(4) Antara 3 & 5 @break
                                                    @case(5) Lebih penting @break
                                                    @case(6) Antara 5 & 7 @break
                                                    @case(7) Jauh lebih penting @break
                                                    @case(8) Antara 7 & 9 @break
                                                    @case(9) Sangat-sangat penting @break
                                                @endswitch
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
                                        $reciprocal = $val ? round(1/$val, 4) : '';
                                    @endphp

                                    <input type="text" readonly
                                        class="form-control form-control-sm text-center bg-light reciprocal"
                                        data-i="{{ $k1->id }}"
                                        data-j="{{ $k2->id }}"
                                        id="rec-{{ $k1->id }}-{{ $k2->id }}"
                                        value="{{ $reciprocal }}">
                                @endif

                            </td>
                        @endforeach

                    </tr>

                @endforeach

                </tbody>
            </table>
        </div>

        <button class="btn btn-success mt-3">
            <i class="fas fa-save"></i> Simpan Matriks AHP
        </button>
    </form>
<hr>

    <!-- =================== HASIL PERHITUNGAN AHP =================== -->

    <h5 class="fw-bold mt-4">Hasil Perhitungan Konsistensi AHP</h5>

    <table class="table table-bordered w-50">
        <tr>
            <th>λ maks</th>
            <td>{{ number_format($hasil['lambda_max'], 4) }}</td>
        </tr>
        <tr>
            <th>Consistency Index (CI)</th>
            <td>{{ number_format($hasil['CI'], 4) }}</td>
        </tr>
        <tr>
            <th>Consistency Ratio (CR)</th>
            <td>{{ number_format($hasil['CR'], 4) }}</td>
        </tr>
    </table>

    @if($hasil['CR'] <= 0.1)
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


@endsection
