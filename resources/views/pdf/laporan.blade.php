<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "Helvetica", sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        .box { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 6px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<h2>LAPORAN EVALUASI KESESUAIAN LAHAN PADI SAWAH<br>PROVINSI BANTEN</h2>

<p><strong>Tanggal Cetak:</strong> {{ date('d-m-Y') }}</p>

<div class="box">
    <h3>S1 – Sangat Sesuai</h3>
    <p><strong>Jumlah Lokasi:</strong> {{ $S1->count() }}</p>
    <p><strong>Total Luas:</strong> {{ number_format($totalS1,2) }} ha</p>

    @if($S1->count())
        <table>
            <tr><th>No</th><th>Nama Wilayah</th></tr>
            @foreach($S1 as $i => $w)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $w->lokasi }}</td>
            </tr>
            @endforeach
        </table>
    @else
        <p><i>Tidak ada wilayah S1</i></p>
    @endif
</div>

<div class="box">
    <h3>S2 – Cukup Sesuai</h3>
    <p><strong>Jumlah Lokasi:</strong> {{ $S2->count() }}</p>
    <p><strong>Total Luas:</strong> {{ number_format($totalS2,2) }} ha</p>

    @if($S2->count())
        <table>
            <tr><th>No</th><th>Nama Wilayah</th></tr>
            @foreach($S2 as $i => $w)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $w->lokasi }}</td>
            </tr>
            @endforeach
        </table>
    @else
        <p><i>Tidak ada wilayah S2</i></p>
    @endif
</div>

<div class="box">
    <h3>S3 – Marginal</h3>
    <p><strong>Jumlah Lokasi:</strong> {{ $S3->count() }}</p>
    <p><strong>Total Luas:</strong> {{ number_format($totalS3,2) }} ha</p>

    @if($S3->count())
        <table>
            <tr><th>No</th><th>Nama Wilayah</th></tr>
            @foreach($S3 as $i => $w)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $w->lokasi }}</td>
            </tr>
            @endforeach
        </table>
    @else
        <p><i>Tidak ada wilayah S3</i></p>
    @endif
</div>

<div class="box">
    <h3>N – Tidak Sesuai</h3>
    <p><strong>Jumlah Lokasi:</strong> {{ $N->count() }}</p>
    <p><strong>Total Luas:</strong> {{ number_format($totalN,2) }} ha</p>

    @if($N->count())
        <table>
            <tr><th>No</th><th>Nama Wilayah</th></tr>
            @foreach($N as $i => $w)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $w->lokasi }}</td>
            </tr>
            @endforeach
        </table>
    @else
        <p><i>Tidak ada wilayah N</i></p>
    @endif
</div>

</body>
</html>
