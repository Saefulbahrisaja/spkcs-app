@extends('layouts.app')

@section('content')
<a href="{{ route('admin.evaluasi.run') }}"class="btn btn-primary btn-sm"><i class="fas fa-play-circle"></i> Evaluasi Lahan</a>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Laporan Evaluasi Kesesuaian Lahan</li>
</ol>

<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <strong>Hasil Evaluasi Lahan</strong>
    </div>

    <div class="card-body">

        <table class="table table-striped table-bordered align-middle" id="datatablesSimple">
            <thead class="table-secondary">
                <tr>
                    <th width="60">ID</th>
                    <th>Tanggal Proses</th>
                    <th>Hasil Klasifikasi</th>
                    <th>Hasil Ranking</th>
                    <th>File PDF</th>
                    <th>Peta</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($laporan as $row)
                <tr>
                    <td class="fw-bold">{{ $row->id }}</td>

                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y H:i') }}</td>

                    <td>
                        <div class="small text-muted">({{ strlen($row->hasil_klasifikasi) }} karakter)</div>
                        <code class="small">{{ Str::limit($row->hasil_klasifikasi, 40) }}</code>
                    </td>

                    <td>
                        <div class="small text-muted">({{ strlen($row->hasil_ranking) }} karakter)</div>
                        <code class="small">{{ Str::limit($row->hasil_ranking, 40) }}</code>
                    </td>

                    <!-- PDF -->
                    <td class="text-center">
                        @if($row->path_pdf)
                            <a href="{{ asset($row->path_pdf) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-danger">
                                Lihat PDF
                            </a>
                        @else
                            <span class="text-muted">Tidak ada</span>
                        @endif
                    </td>

                    <!-- Peta -->
                    <td class="text-center">
                        @if($row->path_peta)
                            <a href="{{ asset($row->path_peta) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-success">
                                Lihat Peta
                            </a>
                        @else
                            <span class="text-muted">Tidak ada</span>
                        @endif
                    </td>

                    <td>
                        @if($row->status_draft)
                            <span class="badge bg-warning text-dark">Draft</span>
                        @else
                            <span class="badge bg-success">Final</span>
                        @endif
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada laporan evaluasi.
                        <br>
                                 </td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>
</div>

@endsection
