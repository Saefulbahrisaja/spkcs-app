<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Evaluasi Lahan</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .sub-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: -4px;
        }

        .section-title {
            margin-top: 20px;
            font-weight: bold;
            font-size: 15px;
            padding: 5px 0;
            border-bottom: 1px solid #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table th, table td {
            border: 1px solid #666;
            padding: 6px;
        }

        table th {
            font-weight: bold;
            background: #efefef;
        }

        .footer {
            margin-top: 20px;
            font-size: 11px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 6px;
        }

        .page-break {
            page-break-after: always;
        }

        .center {
            text-align: center;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            color: #fff;
            font-size: 11px;
        }
        .S1 { background: #00aa00; }
        .S2 { background: #d4d40d; color:#000; }
        .S3 { background: #ff8800; }
        .N  { background: #cc0000; }

    </style>

</head>
<body>

    <!-- ========================= HEADER ========================= -->
    <div class="header">
        <div style="font-size:20px; font-weight:bold;">BANTEN – SPABILITY</div>
        <div class="sub-title">Sistem Evaluasi Kesesuaian Lahan Padi Sawah</div>

        <div style="font-size:12px;">
            Tanggal Laporan: <strong>{{ $tanggal }}</strong>
        </div>
    </div>

    <!-- ========================= BAGIAN 1: KLASIFIKASI ========================= -->
    <div class="section-title">1. Hasil Klasifikasi Kesesuaian Lahan</div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th>Nama Lokasi</th>
                <th style="width:20%">Nilai Total</th>
                <th style="width:18%">Kelas</th>
            </tr>
        </thead>
        <tbody>
        @foreach($klasifikasi as $i => $row)
            <tr>
                <td class="center">{{ $i+1 }}</td>
                <td>{{ $row['lokasi'] }}</td>
                <td class="center">{{ number_format($row['nilai_total'], 4) }}</td>
                <td class="center">
                    <span class="badge {{ $row['kelas'] }}">
                        {{ $row['kelas'] }}
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ========================= BAGIAN 2: RANKING VIKOR ========================= -->
    <div class="section-title">2. Hasil Pemeringkatan Metode VIKOR</div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th>Nama Lokasi</th>
                <th style="width:20%">Nilai Q</th>
                <th style="width:20%">Ranking</th>
            </tr>
        </thead>
        <tbody>
        @foreach($ranking as $i => $row)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $row['lokasi'] }}</td>
                <td class="center">{{ number_format($row['q_value'], 4) }}</td>
                <td class="center"><strong>{{ $row['ranking'] }}</strong></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>
    <!-- ========================= BAGIAN 3: PETA HASIL ========================= -->
    <div class="section-title">3. Peta Hasil Evaluasi</div>

    @if(isset($path_peta))
        <div class="center" style="margin-top:15px;">
            <img src="{{ public_path($path_peta) }}" style="width:100%; border:1px solid #333;">
        </div>
    @else
        <p class="center"><i>Peta tidak tersedia.</i></p>
    @endif


    <!-- ========================= FOOTER ========================= -->
    <div class="footer">
        Laporan ini dihasilkan otomatis oleh sistem Banten-SPABILITY — {{ now()->format('Y') }}
    </div>

</body>
</html>
