<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
h2,h3 { text-align:center; margin:0; }
table { width:100%; border-collapse: collapse; margin-top:10px; }
th,td { border:1px solid #000; padding:6px; }
th { background:#f2f2f2; }
.section { margin-top:15px; }
.signature { margin-top:60px; text-align:right; }
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

{{-- ================= DATA UTAMA ================= --}}
<div class="section">
    <strong>Nama Wilayah:</strong> {{ $w->lokasi }} <br>

    <strong>Kelas Kesesuaian:</strong>
    {{ $w->klasifikasi->kelas_kesesuaian ?? '-' }} <br>

    <strong>Skor Normalisasi:</strong>
    {{ isset($w->klasifikasi->skor_normalisasi)
        ? number_format($w->klasifikasi->skor_normalisasi, 3)
        : '-' }}
</div>

{{-- ================= TABEL ATRIBUT ================= --}}
<div class="section">
<table>
<thead>
<tr>
    <th>No</th>
    <th>Kriteria</th>
    <th>Nilai</th>
</tr>
</thead>
<tbody>
@forelse($w->nilaiAlternatif as $i => $n)
<tr>
    <td>{{ $i + 1 }}</td>
    <td>{{ $n->atribut_nama }}</td>
    <td>{{ $n->nilai_input }}</td>
</tr>
@empty
<tr>
    <td colspan="3" style="text-align:center">Data atribut tidak tersedia</td>
</tr>
@endforelse
</tbody>
</table>
</div>

{{-- ================= REKOMENDASI SISTEM ================= --}}
<div class="section">
<strong>Rekomendasi Sistem:</strong><br>

@php
    $kelas = $w->klasifikasi->kelas_kesesuaian ?? null;
@endphp

@if($kelas === 'S1')
Wilayah ini <strong>sangat direkomendasikan</strong> untuk pengembangan pertanian padi berkelanjutan.
@elseif($kelas === 'S2')
Wilayah ini <strong>direkomendasikan</strong> dengan pengelolaan dan perbaikan teknis tertentu.
@elseif($kelas === 'S3')
Wilayah ini <strong>direkomendasikan terbatas</strong> dan memerlukan perbaikan signifikan.
@elseif($kelas === 'N')
Wilayah ini <strong>tidak direkomendasikan</strong> untuk pertanian padi.
@else
Rekomendasi sistem belum tersedia.
@endif
</div>

{{-- ================= CATATAN DINAS ================= --}}
<div class="section">
<strong>Catatan Evaluasi Dinas:</strong><br>
{{ $w->rekomendasi_dinas ?? 'Belum ada catatan dari dinas.' }}
</div>

{{-- ================= TANDA TANGAN ================= --}}
<div class="signature">
Pandeglang, {{ $tanggal }}<br><br><br>
<strong>Kepala Dinas Pertanian</strong><br>
Provinsi Banten
</div>

</body>
</html>
