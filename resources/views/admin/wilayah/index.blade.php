@extends('layouts.app')

@section('content')

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Daftar Lokasi & Nilai Alternatif</li>
</ol>

<div class="card mb-4">
    <div class="card-header">
        <a href="{{ route('admin.wilayah.create') }}" class="btn btn-success btn-sm">Tambah Lokasi</a>
        <a href="{{ route('admin.alternatif.index') }}" class="btn btn-primary btn-sm">Input Nilai Alternatif</a>
        <a href="{{ route('admin.evaluasi.run') }}"class="btn btn-primary btn-sm"><i class="fas fa-play-circle"></i> Jalankan Evaluasi Lahan</a>
    </div>

    <div class="card-body">

        <!-- NAVIGATION TAB -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="lokasi-tab" data-bs-toggle="tab" 
                        data-bs-target="#lokasi" type="button" role="tab">
                    Daftar Lokasi
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nilai-tab" data-bs-toggle="tab" 
                        data-bs-target="#nilai" type="button" role="tab">
                    Daftar Nilai Alternatif
                </button>
            </li>

        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content border p-3" id="myTabContent">

            <!-- ================= TAB 1 : DAFTAR LOKASI ================= -->
            <div class="tab-pane fade show active" id="lokasi" role="tabpanel">

                <table class="table table-bordered table-striped" id="datatablesSimple">
                    <thead class="table-secondary">
                        <tr>
                            <th>Lokasi</th>
                            <th>Lat/Long</th>
                            <th>Nilai Total</th>
                            <th style="width:120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                        <tr>
                            <td>{{ $item->lokasi }}</td>
                            <td>
                                <a href="https://maps.google.com/?q={{ $item->lat }},{{ $item->lng }}" 
                                   target="_blank" class="text-primary">
                                   {{ $item->lat }}, {{ $item->lng }}
                                </a>
                            </td>
                            <td>{{ $item->nilai_total }}</td>
                            <td>
                                <form action="{{ route('admin.wilayah.destroy', $item->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                        onclick="return confirm('Hapus lokasi?')"
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

            <!-- ================= TAB 2 : NILAI ALTERNATIF ================= -->
            <div class="tab-pane fade" id="nilai" role="tabpanel">

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-sm text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Alternatif</th>
                                @foreach($kriteria as $k)
                                    <th>{{ $k->nama_kriteria }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $alt)
                                <tr>
                                    <td class="fw-semibold text-start">{{ $alt->lokasi }}</td>
                                    @foreach($kriteria as $k)
                                        @php
                                            $nilai = $alt->nilai->where('kriteria_id', $k->id)->first();
                                        @endphp
                                        <td>{{ $nilai->nilai ?? '-' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        </div><!-- end tab content -->

    </div><!-- end card-body -->
</div><!-- end card -->

@endsection
