@extends('layouts.app')

@section('content')
<h1 class="text-xl font-bold mb-4">Pengaturan Batas Kesesuaian Lahan</h1>

<form method="POST" action="{{ route('admin.batas.update') }}">
    @csrf

    <div class="mb-2">
        <label>Batas S1 (≥ nilai_total)</label>
        <input type="number" step="0.01" name="batas_s1" class="border p-1 w-32"
               value="{{ $batas->batas_s1 }}">
    </div>

    <div class="mb-2">
        <label>Batas S2 (≥ nilai_total)</label>
        <input type="number" step="0.01" name="batas_s2" class="border p-1 w-32"
               value="{{ $batas->batas_s2 }}">
    </div>

    <div class="mb-2">
        <label>Batas S3 (≥ nilai_total)</label>
        <input type="number" step="0.01" name="batas_s3" class="border p-1 w-32"
               value="{{ $batas->batas_s3 }}">
    </div>

    <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
</form>
@endsection
