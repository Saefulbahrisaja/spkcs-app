<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
h2, h3 { text-align: center; margin: 0; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #000; padding: 6px; }
th { background: #f0f0f0; }
.section { margin-top: 20px; }
.signature { margin-top: 60px; text-align: right; }
</style>
</head>
<body>

<h2>LAPORAN HASIL EVALUASI</h2>
<h3>KESESUAIAN LAHAN PERTANIAN</h3>
<p style="text-align:center">
Dinas Pertanian Provinsi Banten<br>
Tanggal: {{ $tanggal }}
</p>
<hr>

@foreach($data as $d)
<div class="section">
    <strong>Nama Wilayah:</strong> {{ $d->lokasi }} <br>

    <strong>Kelas Kesesuaian:</strong>
    {{ $d->klasifikasi->kelas_kesesuaian ?? '-' }} <br>

    <strong>Skor Normalisasi:</strong>
    {{ isset($d->klasifikasi->skor_normalisasi) 
        ? number_format($d->klasifikasi->skor_normalisasi, 3) 
        : '-' }}

    <table>
        <thead>
            <tr>
                <th>Kriteria</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
        @foreach($d->nilaiAlternatif as $n)
            <tr>
                <td>{{ $n->atribut_nama }}</td>
                <td>{{ $n->nilai_input }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p><strong>Rekomendasi Sistem:</strong><br>
        @php $kelas = $d->klasifikasi->kelas_kesesuaian ?? null; @endphp

        @if($kelas === 'S1')
            Sangat direkomendasikan untuk pengembangan pertanian padi berkelanjutan.
        @elseif($kelas === 'S2')
            Direkomendasikan dengan pengelolaan lahan tambahan.
        @elseif($kelas === 'S3')
            Direkomendasikan terbatas dengan perbaikan signifikan.
        @elseif($kelas === 'N')
            Tidak direkomendasikan untuk pertanian padi.
        @else
            —
        @endif
    </p>

    <p><strong>Catatan Dinas:</strong><br>
        {{ $d->rekomendasi_dinas ?? '—' }}
    </p>
</div>
@endforeach

@if($kebijakan)
<hr>

<h3>Rekomendasi Kebijakan Dinas Pertanian</h3>

<table>
    <tr>
        <th width="30%">Tanggal Penetapan</th>
        <td>{{ \Carbon\Carbon::parse($kebijakan->tanggal)->format('d F Y') }}</td>
    </tr>
    <tr>
        <th>Wilayah Prioritas</th>
        <td>{{ $kebijakan->wilayah_prioritas }}</td>
    </tr>
    <tr>
        <th>Daftar Intervensi</th>
        <td>{!! nl2br(e($kebijakan->daftar_intervensi)) !!}</td>
    </tr>
    <tr>
        <th>Catatan</th>
        <td>{{ $kebijakan->catatan ?? '—' }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td><strong>{{ strtoupper($kebijakan->status) }}</strong></td>
    </tr>
</table>
@endif
@if(!$kebijakan)
<p><em>Rekomendasi kebijakan belum ditetapkan.</em></p>
@endif
<div class="signature">
    Pandeglang, {{ $tanggal }}<br><br><br>
    <strong>Kepala Dinas Pertanian</strong><br>
    Provinsi Banten
</div>

</body>
</html>
