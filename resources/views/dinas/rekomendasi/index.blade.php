@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Rekomendasi Kebijakan Dinas</h4>

    <a href="{{ route('dinas.kebijakan.create') }}"
       class="btn btn-primary mb-3">+ Tambah Kebijakan</a>
 <a href="{{ route('dinas.evaluasi.pdf') }}"
       class="btn btn-danger mb-3">
        Download PDF Laporan Resmi Semua Wilayah
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Wilayah Prioritas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $d)
            <tr>
                <td>{{ $d->tanggal }}</td>
                <td>{{ $d->wilayah_prioritas }}</td>
                <td>
                    <span class="badge 
                        {{ $d->status == 'ditetapkan' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($d->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
