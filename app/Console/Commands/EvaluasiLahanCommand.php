<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\KlasifikasiLahan;
use App\Models\PemeringkatanVikor;
use App\Models\LaporanEvaluasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EvaluasiLahanCommand extends Command
{
    protected $signature = 'evaluasi:lahan';
    protected $description = 'Hitung AHP, Klasifikasi, dan VIKOR secara otomatis';

    public function handle()
    {
        $this->info("=== MEMULAI PERHITUNGAN EVALUASI LAHAN ===");

        $this->hitungAHP();
        $this->info("✔ AHP selesai.");

        $this->hitungSkorTotal();
        $this->info("✔ Skor total alternatif selesai.");

        $this->hitungKlasifikasi();
        $this->info("✔ Klasifikasi lahan selesai.");

        $this->hitungVIKOR();
        $this->info("✔ VIKOR selesai.");

        // -----------------------------
        // SIMPAN LAPORAN
        // -----------------------------
        $this->simpanLaporan();
        $this->info("✔ Laporan evaluasi berhasil disimpan.");

        $this->info("=== SELESAI. SEMUA HASIL TERSIMPAN ===");

        return Command::SUCCESS;
    }

    // =================================================================
    // 1) AHP
    // =================================================================
    private function hitungAHP()
    {
        $kriteria = Kriteria::all();
        $n = count($kriteria);
        if ($n == 0) return;

        $matrix = [];
        foreach ($kriteria as $i => $ki) {
            foreach ($kriteria as $j => $kj) {

                if ($i == $j) {
                    $matrix[$i][$j] = 1;
                } else {
                    $pair = DB::table('ahp_matrices')->where([
                        'kriteria_1_id' => $ki->id,
                        'kriteria_2_id' => $kj->id
                    ])->first();

                    $matrix[$i][$j] = $pair ? $pair->nilai_perbandingan : 1;
                }
            }
        }

        // Normalisasi
        $colSum = [];
        for ($j = 0; $j < $n; $j++) {
            $colSum[$j] = array_sum(array_column($matrix, $j));
        }

        $norm = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $norm[$i][$j] = $matrix[$i][$j] / $colSum[$j];
            }
        }

        $bobot = [];
        for ($i = 0; $i < $n; $i++) {
            $bobot[$i] = array_sum($norm[$i]) / $n;
        }

        foreach ($kriteria as $i => $k) {
            $k->bobot = $bobot[$i];
            $k->save();
        }
    }

    // =================================================================
    // 2) Hitung Skor Alternatif
    // =================================================================
    private function hitungSkorTotal()
    {
        $alternatifs = AlternatifLahan::with('nilai')->get();
        $kriteria = Kriteria::all();

        foreach ($alternatifs as $alt) {
            $total = 0;

            foreach ($kriteria as $k) {
                $nilaiAlt = $alt->nilai->where('kriteria_id', $k->id)->first();
                if ($nilaiAlt) {
                    $total += $nilaiAlt->nilai * $k->bobot;
                }
            }

            $alt->nilai_total = $total;
            $alt->save();
        }
    }

    // =================================================================
    // 3) Klasifikasi
    // =================================================================
    private function hitungKlasifikasi()
    {
        $alternatifs = AlternatifLahan::all();
        $batas = \App\Models\BatasKesesuaian::first();

        foreach ($alternatifs as $a) {
            $skor = $a->nilai_total;

            if ($skor >= $batas->batas_s1) $kelas = 'S1';
            elseif ($skor >= $batas->batas_s2) $kelas = 'S2';
            elseif ($skor >= $batas->batas_s3) $kelas = 'S3';
            else $kelas = 'N';

            KlasifikasiLahan::updateOrCreate(
                ['alternatif_id' => $a->id],
                [
                    'skor_normalisasi' => $skor,
                    'kelas_kesesuaian' => $kelas
                ]
            );
        }
    }

    // =================================================================
    // 4) VIKOR
    // =================================================================
    private function hitungVIKOR()
    {
        $kriteria = Kriteria::all();
        $alternatifs = AlternatifLahan::with('nilai')->get();

        $S = []; $R = []; $Q = [];

        foreach ($alternatifs as $a) {

            $sum = 0;
            $max = 0;

            foreach ($kriteria as $k) {
                $nilai = $a->nilai->where('kriteria_id', $k->id)->first()->nilai ?? 0;

                $best = 5;
                $worst = 1;

                $temp = $k->bobot * (($best - $nilai) / ($best - $worst));
                $sum += $temp;
                if ($temp > $max) $max = $temp;
            }

            $S[$a->id] = $sum;
            $R[$a->id] = $max;
        }

        $Smin = min($S); 
        $Smax = max($S);
        $Rmin = min($R); 
        $Rmax = max($R);

        $v = 0.5;

        foreach ($alternatifs as $a) {

            $S_div = ($Smax - $Smin) == 0 ? 0 : ($S[$a->id] - $Smin) / ($Smax - $Smin);
            $R_div = ($Rmax - $Rmin) == 0 ? 0 : ($R[$a->id] - $Rmin) / ($Rmax - $Rmin);

            $Q[$a->id] = $v * $S_div + (1 - $v) * $R_div;
        }

        asort($Q);

        $ranking = 1;
        foreach ($Q as $altId => $q) {
            PemeringkatanVikor::updateOrCreate(
                ['alternatif_id' => $altId],
                [
                    'v_value'       => $R[$altId],
                    'q_value'       => $q,
                    'hasil_ranking' => $ranking++
                ]
            );
        }
    }

    // =================================================================
    // 5) SIMPAN LAPORAN
    // =================================================================
   private function simpanLaporan()
{
    $this->info("📄 Menyimpan laporan evaluasi...");

    /* ========================================================
     * 1. AMBIL DATA KLASIFIKASI & RANKING
     * ====================================================== */
    $klasifikasi = KlasifikasiLahan::with('alternatif')->get();
    $ranking = PemeringkatanVikor::with('alternatif')->orderBy('hasil_ranking')->get();

    $hasil_klasifikasi = $klasifikasi->map(function($k) {
        return [
            'lokasi'      => $k->alternatif->lokasi ?? '-',
            'nilai_total' => $k->skor_normalisasi,
            'kelas'       => $k->kelas_kesesuaian
        ];
    })->toArray();

    $hasil_ranking = $ranking->map(function($r) {
        return [
            'lokasi'  => $r->alternatif->lokasi ?? '-',
            'ranking' => $r->hasil_ranking,
            'q_value' => $r->q_value
        ];
    })->toArray();

    /* ========================================================
     * 2. GENERATE PDF LAPORAN
     * ====================================================== */
    $pdfView = view('admin.laporan.pdf-template', [
        'klasifikasi' => $hasil_klasifikasi,
        'ranking'     => $hasil_ranking,
        'tanggal'     => now()->format('d-m-Y H:i'),
    ])->render();

    $pdf = Pdf::loadHTML($pdfView);

    $pdfName = 'laporan_evaluasi_' . time() . '.pdf';
    Storage::put("public/laporan/{$pdfName}", $pdf->output());

    $path_pdf = "storage/laporan/{$pdfName}";
    $this->info("✔ PDF berhasil dibuat: $path_pdf");


    /* ========================================================
     * 3. BENTUK GEOJSON UNTUK POLYGON
     * ====================================================== */
    $alternatifs = AlternatifLahan::with('klasifikasi')->get();
    $features = [];

    foreach ($alternatifs as $a) {
        if (!$a->geometry) {
            continue;
        }

        try {
            $geom = json_decode($a->geometry, true);

            // kurangi vertex agar tidak melebihi limit URL
            if (isset($geom['coordinates'][0]) && is_array($geom['coordinates'][0])) {
                $coords = $geom['coordinates'][0];

                $step = max(1, intval(count($coords) / 50));
                $coords = array_values(array_filter($coords, fn($v, $i) => $i % $step === 0, ARRAY_FILTER_USE_BOTH));

                // pastikan polygon tertutup
                if ($coords[0] != end($coords)) {
                    $coords[] = $coords[0];
                }

                $geom['coordinates'][0] = $coords;
            }

            $features[] = [
                "type" => "Feature",
                "geometry" => $geom,
                "properties" => [
                    "kelas" => $a->klasifikasi->kelas_kesesuaian ?? "N"
                ]
            ];
        } catch (\Exception $e) {
            continue;
        }
    }

    $geojson = json_encode([
        "type" => "FeatureCollection",
        "features" => $features
    ]);

    $encoded = $this->encodeGeoJsonForMapbox($geojson);


    /* ========================================================
     * 4. AMBIL GAMBAR PETA DARI MAPBOX STATIC API
     * ====================================================== */
    $token = env('MAPBOX_TOKEN');

$imgName = "peta_eval_" . time() . ".png";
$saveDir = storage_path("app/public/laporan");
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);
$savePath = "$saveDir/$imgName";

$center = "106.15,-6.3,9";
$size   = "1280x900";

// build URL - jika $encoded kosong maka kita tidak menambahkan geojson(...)
$geoSegment = $encoded ? "geojson({$encoded})/" : "";
$staticUrl = "https://api.mapbox.com/styles/v1/mapbox/satellite-v9/static/{$geoSegment}{$center}/{$size}?access_token={$token}";

$this->info("Mengambil peta: ".\Str::limit($staticUrl, 200) );
$this->info("URL length: " . strlen($staticUrl));

// fallback ke URL tanpa overlay jika URL terlalu panjang
if (strlen($staticUrl) > 7800) {
    $this->warn("URL terlalu panjang untuk Mapbox. Menggunakan peta dasar (tanpa polygon).");
    $geoSegment = "";
    $staticUrl = "https://api.mapbox.com/styles/v1/mapbox/satellite-v9/static/{$center}/{$size}?access_token={$token}";
}

try {
    // lakukan HTTP GET
    $resp = Http::withOptions(['verify' => false])->get($staticUrl);

    $status = $resp->status();
    $contentType = $resp->header('Content-Type', '');

    $this->info("Mapbox response: HTTP {$status}; Content-Type: {$contentType}");

    if ($status === 200 && str_contains($contentType, 'image/png')) {
        file_put_contents($savePath, $resp->body());
        $path_peta = "storage/laporan/{$imgName}";
        $this->info("✔ Peta berhasil diunduh: {$path_peta}");
    } else {
        // simpan body respons ke file debug agar Anda bisa melihat HTML/error JSON
        $debugName = 'mapbox_debug_' . time() . '.txt';
        $debugPath = storage_path("app/public/laporan/{$debugName}");
        file_put_contents($debugPath, "HTTP {$status}\n\n" . $resp->body());

        $this->warn("Mapbox tidak mengembalikan PNG (HTTP {$status}). Debug disimpan: storage/laporan/{$debugName}");

        // coba fallback: peta dasar tanpa overlay
        $fallbackUrl = "https://api.mapbox.com/styles/v1/mapbox/satellite-v9/static/{$center}/{$size}?access_token={$token}";
        $this->info("Mencoba fallback: ".\Str::limit($fallbackUrl,200));
        $resp2 = Http::withOptions(['verify' => false])->get($fallbackUrl);

        if ($resp2->status() === 200 && str_contains($resp2->header('Content-Type',''), 'image/png')) {
            file_put_contents($savePath, $resp2->body());
            $path_peta = "storage/laporan/{$imgName}";
            $this->info("✔ Fallback peta dasar berhasil diunduh: {$path_peta}");
        } else {
            // fallback gagal juga: buat placeholder PNG berisi pesan error pendek
            $errSnippet = substr($resp->body(), 0, 800);
            $msg = "Mapbox failed HTTP {$status}. See debug file: {$debugName}";
            $this->error($msg);

            // buat placeholder PNG dengan GD berisi pesan singkat
            $w = 1280; $h = 900;
            $img = imagecreatetruecolor($w, $h);
            $bg = imagecolorallocate($img, 255, 255, 255);
            $red = imagecolorallocate($img, 200, 0, 0);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefill($img, 0, 0, $bg);
            imagestring($img, 5, 20, 20, "MAPBOX ERROR (HTTP {$status})", $red);
            imagestring($img, 3, 20, 60, substr($errSnippet, 0, 200), $black);
            imagepng($img, $savePath);
            imagedestroy($img);

            $path_peta = "storage/laporan/{$imgName}";
            $this->warn("Placeholder peta dibuat: {$path_peta}");
        }
    }
} catch (\Exception $e) {
    // tangani error koneksi / exception lain
    $this->error("Exception saat memanggil Mapbox: " . $e->getMessage());

    // simpan exception ke file debug
    $dbg = 'mapbox_exception_' . time() . '.txt';
    file_put_contents(storage_path("app/public/laporan/{$dbg}"), $e->getMessage());

    // buat placeholder PNG berisi pesan exception
    $w = 1280; $h = 900;
    $img = imagecreatetruecolor($w, $h);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $red = imagecolorallocate($img, 200, 0, 0);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 20, 20, "MAPBOX EXCEPTION", $red);
    imagestring($img, 3, 20, 60, $e->getMessage(), $red);
    imagepng($img, $savePath);
    imagedestroy($img);

    $path_peta = "storage/laporan/{$imgName}";
}


    /* ========================================================
     * 5. SIMPAN KE DATABASE
     * ====================================================== */
    LaporanEvaluasi::create([
        'tanggal'           => now(),
        'hasil_klasifikasi' => json_encode($hasil_klasifikasi),
        'hasil_ranking'     => json_encode($hasil_ranking),
        'path_pdf'          => $path_pdf,
        'path_peta'         => $path_peta,
        'status_draft'      => 1
    ]);

    $this->info("🎉 Laporan lengkap berhasil disimpan.");
}

/**
 * Encode GeoJSON supaya aman dipakai untuk Mapbox Static API.
 * Menggunakan URL-safe Base64.
 */
private function encodeGeoJsonForMapbox($geojson)
{
    if (!$geojson || $geojson === '{"type":"FeatureCollection","features":[]}') {
        return "";
    }

    $base64 = base64_encode($geojson);

    // URL-safe Base64
    return rtrim(strtr($base64, '+/', '-_'), '=');
}



}
