@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">AHP Multi-Pakar</li>
</ol>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Daftar Pakar</h5>
        <a href="{{ route('admin.pakar.create') }}" class="btn btn-success btn-sm">+ Tambah Pakar</a>
    </div>

    <div class="card-body">
        @if($pakar->count()==0)
            <p class="text-muted">Belum ada pakar.</p>
        @endif

        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Nama Pakar</th>
                    <th>Bobot</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($pakar as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->weight }}</td>
                    <td>
                        <a href="{{ route('admin.ahp.form', $p->id) }}" 
                            class="btn btn-primary btn-sm">
                            Input Matriks
                        </a>

                        <a href="{{ route('admin.pakar.edit', $p->id) }}" 
                            class="btn btn-warning btn-sm">Edit</a>

                        <form action="{{ route('admin.pakar.delete',$p->id) }}"
                              class="d-inline" method="POST">
                              @csrf @method('DELETE')
                              <button onclick="return confirm('Hapus pakar?')" 
                                      class="btn btn-danger btn-sm">
                                Hapus
                              </button>
                        </form>

                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>

@endsection
