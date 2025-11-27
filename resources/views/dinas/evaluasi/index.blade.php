@extends('layouts.app')
@section('content')

<h1 class="font-bold text-xl mb-4">Review Hasil Evaluasi Kesesuaian Lahan</h1>

@include('components.evaluasi-table')

<a href="{{ route('dinas.peta.index') }}" class="mt-4 inline-block bg-green-600 text-white px-4 py-2 rounded">
    Lihat Peta Evaluasi
</a>

@endsection
