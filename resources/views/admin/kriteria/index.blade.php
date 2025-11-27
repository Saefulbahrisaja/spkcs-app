@extends('layouts.app')
@section('content')

<h1 class="text-xl font-bold mb-4">Daftar Kriteria</h1>

<a href="{{ route('admin.kriteria.create') }}" 
   class="bg-blue-600 text-white px-3 py-2 rounded">Tambah Kriteria</a>

<table class="table-auto w-full mt-4 bg-white shadow">
    <thead>
        <tr class="bg-gray-200">
            <th>Nama</th>
            <th>Tipe</th>
            <th>Bobot</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    @foreach($kriteria as $k)
        <tr>
            <td>{{ $k->nama_kriteria }}</td>
            <td>{{ $k->tipe }}</td>
            <td>{{ $k->bobot }}</td>
            <td>
                <a href="{{ route('admin.kriteria.edit',$k->id) }}">Edit</a> |
                <form action="{{ route('admin.kriteria.destroy',$k->id) }}" method="POST" class="inline">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Hapus?')">Hapus</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

@endsection
