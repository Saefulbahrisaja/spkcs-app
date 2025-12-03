@extends('layouts.app')
@section('content')
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Daftar Kriteria</li>
</ol>
<div class="card mb-4">
    <div class="card-header">
        <a href="{{ route('admin.kriteria.create') }}" class="btn btn-success">Tambah Kriteria</a>
    </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Bobot</th>
                    <th>Aksi</th>
                </thead>
                <tbody>
                @foreach($kriteria as $k)
                    <tr>
                        <td>{{ $k->nama_kriteria }}</td>
                        <td>{{ $k->tipe }}</td>
                        <td>{{ $k->bobot }}</td>
                        <td>
                            <a class="btn btn-warning btn-sm" href="{{ route('admin.kriteria.edit',$k->id) }}">Edit</a> |
                            <form action="{{ route('admin.kriteria.destroy',$k->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
</div>

@endsection
