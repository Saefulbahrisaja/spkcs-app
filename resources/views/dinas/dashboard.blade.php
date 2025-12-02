@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Dashboard Dinas Pertanian</h1>

<p>Selamat datang, {{ auth()->user()->nama }}</p>

<div class="mt-4">
    <a href="{{ route('dinas.evaluasi.index') }}" 
       class="bg-blue-600 px-4 py-2 text-white rounded">Lihat Hasil Evaluasi</a>
</div>
@endsection
