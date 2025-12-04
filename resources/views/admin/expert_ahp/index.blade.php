@extends('layouts.app')

@section('content')

<h3 class="mb-4">Manajemen Pakar AHP</h3>

<div class="card">
    <div class="card-header">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExpertModal">
            + Tambah Pakar
        </button>
    </div>

    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Pakar</th>
                    <th>Bobot Pakar</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($experts as $exp)
                    <tr>
                        <td>{{ $exp->name }}</td>
                        <td>{{ $exp->weight }}</td>
                        <td>
                            <a href="{{ route('expert.ahp.form', $exp->id) }}"
                                class="btn btn-sm btn-info">
                                Input Matrix AHP
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>




<!-- Modal Tambah Pakar -->
<div class="modal fade" id="addExpertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('expert.ahp.addExpert') }}">
                @csrf

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Pakar Baru</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label>Nama Pakar</label>
                    <input type="text" name="name" class="form-control" required>

                    <label class="mt-3">Bobot Pakar (Opsional)</label>
                    <input type="number" step="0.01" name="weight" class="form-control">

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection
