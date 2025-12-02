@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Dashboard Admin</h1>

<div class="grid grid-cols-3 gap-4">

    <div class="bg-white shadow p-4">
        <h2 class="font-bold">Jumlah Kriteria</h2>
        <p class="text-3xl">{{ $kriteria ?? 0 }}</p>
    </div>

    <div class="bg-white shadow p-4">
        <h2 class="font-bold">Alternatif Lahan</h2>
        <p class="text-3xl">{{ $alternatif ?? 0 }}</p>
    </div>

    <div class="bg-white shadow p-4">
        <h2 class="font-bold">Laporan</h2>
        <p class="text-3xl">{{ $laporan ?? 0 }}</p>
    </div>

</div>
@endsection
