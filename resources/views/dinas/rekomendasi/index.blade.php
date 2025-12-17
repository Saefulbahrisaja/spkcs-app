@extends('layouts.app')
@section('content')
<h1 class="font-bold text-xl mb-4">Review Rekomendasi Kesesuaian Lahan</h1>
<form method="POST">
    @csrf

    <label>Wilayah Prioritas</label>
    <textarea name="wilayah_prioritas" class="w-full border p-2"></textarea>

    <label class="mt-2 block">Daftar Intervensi</label>
    <textarea name="daftar_intervensi" class="w-full border p-2"></textarea>

    <label class="mt-2 block">Catatan</label>
    <textarea name="catatan" class="w-full border p-2"></textarea>

    <button class="bg-blue-600 text-white px-4 py-2 mt-3 rounded">
        Simpan Rekomendasi
    </button>
</form>
@endsection



