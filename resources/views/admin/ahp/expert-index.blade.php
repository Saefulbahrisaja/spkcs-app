@extends('layouts.app')
@section('content')
<h4>Pakar AHP</h4>

<form method="POST" action="{{ route('admin.ahp.experts.store') }}" class="mb-3">
    @csrf
    <div class="row g-2">
        <div class="col-auto"><input name="name" class="form-control" placeholder="Nama Pakar" required></div>
        <div class="col-auto"><input name="weight" class="form-control" placeholder="bobot (opsional)"></div>
        <div class="col-auto"><button class="btn btn-primary">Tambah</button></div>
    </div>
</form>

<table class="table">
    <thead><tr><th>Nama</th><th>Weight</th><th>Aksi</th></tr></thead>
    <tbody>
        @foreach($experts as $ex)
        <tr>
            <td>{{ $ex->name }}</td>
            <td>{{ $ex->weight }}</td>
            <td>
                <a class="btn btn-sm btn-secondary" href="{{ route('admin.ahp.experts.matrix', $ex->id) }}">Input Matrix</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<form method="POST" action="{{ route('admin.ahp.aggregate') }}">
    @csrf
    <button class="btn btn-success">Aggregate (SWGM) & Hitung Bobot</button>
</form>

@endsection
