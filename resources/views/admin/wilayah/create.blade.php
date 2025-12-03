@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ route('admin.wilayah.index') }}">Daftar Lokasi</a></li>
    <li class="breadcrumb-item active">Tambah Poligon Wilayah</li>
</ol>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <strong>Form Tambah Wilayah</strong>
    </div>

    <div class="card-body">

        <form action="{{ route('admin.wilayah.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- NAMA LOKASI --}}
            <div class="mb-3">
                <label class="form-label">Nama Lokasi</label>
                <input type="text" name="lokasi" class="form-control" 
                       placeholder="Masukkan nama lokasi" required>
            </div>

            {{-- UPLOAD GEOJSON --}}
            <div class="mb-3">
                <label class="form-label">Upload GeoJSON Polygon</label>
                <input type="file" name="geojson" class="form-control" 
                       accept=".json,.geojson" required>

                <small class="text-muted">
                    File harus berupa <strong>.geojson</strong> atau <strong>.json</strong> berisi objek Polygon/MultiPolygon.
                </small>
            </div>

            {{-- TOMBOL --}}
            <button class="btn btn-success">
                <i class="fas fa-save"></i> Simpan Wilayah
            </button>
            <a href="{{ route('admin.wilayah.index') }}" class="btn btn-secondary ms-2">
                Batal
            </a>

        </form>

    </div>
</div>

@endsection
