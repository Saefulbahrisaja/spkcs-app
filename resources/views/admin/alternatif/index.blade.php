@extends('layouts.app')
@section('content')

<h1 class="text-xl font-bold mb-4">Daftar Alternatif Lahan</h1>

<a href="{{ route('admin.alternatif.create') }}" 
   class="bg-blue-600 text-white px-3 py-2 rounded">Tambah Alternatif Lahan</a>

<table class="table-auto w-full mt-4 bg-white shadow">
    <thead>
        <tr class="bg-gray-200">
            <th>Lokasi</th>
            <th>Lat/Long</th>
            <th>Nilai</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
   
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <a href="">Edit</a> |
                <form action="" method="POST" class="inline">
                    
                    <button onclick="return confirm('Hapus?')">Hapus</button>
                </form>
            </td>
        </tr>
    
    </tbody>
</table>

@endsection
