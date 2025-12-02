@extends('layouts.app')

@section('content')
<h1 class="text-xl font-bold mb-4">Tambah Poligon Wilayah</h1>

<form action="{{ route('admin.wilayah.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label>Nama Lokasi</label>
    <input type="text" name="lokasi" class="border p-2 w-full mb-3" required>

    <label>Upload GeoJSON</label>
    <input type="file" name="geojson" accept=".json,.geojson" class="border p-2 w-full mb-3">

    <button class="bg-blue-600 text-white px-4 py-2 rounded">
        Simpan Alternatif
    </button>
</form>
@endsection
