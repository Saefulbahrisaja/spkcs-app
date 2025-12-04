@extends('layouts.app')
@section('content')

<p>Selamat datang, {{ auth()->user()->nama }}</p>

@endsection
