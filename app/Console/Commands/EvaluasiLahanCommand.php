<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\KlasifikasiLahan;
use App\Models\PemeringkatanVikor;
use App\Models\LaporanEvaluasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Services\SFAHPService;

class EvaluasiLahanCommand extends Command
{
    protected $signature = 'evaluasi:lahan';
    protected $description = 'Hitung Evaluasi Lahan (SF-AHP Multi-Expert → Klasifikasi → VIKOR → PDF → Peta)';

    public function handle()
    {
        $this->info("=== MEMULAI PERHITUNGAN EVALUASI LAHAN ===");

        $this->ambilBobotSFAHP(); // pakai multi-expert SFAHPService
        $this->info("✔ Bobot SF-AHP (multi-expert) di-load dan disimpan.");

        $this->hitungSkorTotal();
        $this->info("✔ Skor total alternatif selesai.");

        $this->hitungKlasifikasi();
        $this->info("✔ Klasifikasi lahan selesai.");

        $this->hitungVIKOR();
        $this->info("✔ VIKOR selesai.");

        $this->simpanLaporan();
        $this->info("✔ Laporan evaluasi berhasil disimpan.");

        $this->info("=== SELESAI. SEMUA HASIL TERSIMPAN ===");

        return Command::SUCCESS;
    }

    /* =======================================================
     * 1. AMBIL & SIMPAN BOBOT SF-AHP MULTI-EXPERT
     * ======================================================= */
    private function ambilBobotSFAHP()
    {
        /** @var SFAHPService $svc */
        $svc = app(SFAHPService::class);

        // 1) Kriteria utama (parent)
        $parents = Kriteria::whereNull('parent_id')->get()->values(); // ensure indexed

        if ($parents->count() > 0) {
            $aggParents = $svc->aggregateAndCompute($parents);

            // weights array index sesuai order $parents->values()
            $parentWeights = $aggParents['weights'] ?? [];

            // simpan bobot ke tabel kriteria
            $svc->saveWeightsToKriteria($parents, $parentWeights);
        }

        // 2) Sub-kriteria: hitung lokal & global per parent
        $global = [];
        $local = [];

        foreach ($parents as $pIndex => $parent) {
            $subs = Kriteria::where('parent_id', $parent->id)->get()->values();
            if ($subs->count() < 2) {
                // kalau tak ada atau hanya 1 sub, set bobot default (jika belum)
                if ($subs->count() === 1) {
                    $single = $subs->first();
                    // set lokal = 1, global = parent weight
                    $local[$single->id] = 1.0;
                    $global[$single->id] = $parentWeights[$pIndex] ?? 0;
                }
                continue;
            }

            $aggSubs = $svc->aggregateAndCompute($subs);

            $subWeights = $aggSubs['weights'] ?? [];

            foreach ($subs as $sIndex => $s) {
                $local[$s->id] = $subWeights[$sIndex] ?? 0;
                $global[$s->id] = ($parentWeights[$pIndex] ?? 0) * ($subWeights[$sIndex] ?? 0);
            }
        }

        // simpan bobot sub-kriteria (local → bobot, global → bobot_global)
        $svc->saveSubCriteriaWeights($local, $global);

        // Safety fallback: pastikan semua kriteria punya bobot minimal
        foreach (Kriteria::all() as $k) {
            if (is_null($k->bobot)) {
                $k->bobot = 0;
                $k->save();
            }
            if (is_null($k->bobot_global)) {
                $k->bobot_global = $k->bobot;
                $k->save();
            }
        }
    }

    /* =======================================================
     * 2. HITUNG SKOR ALTERNATIF
     * ======================================================= */
    private function hitungSkorTotal()
    {
        $alternatifs = AlternatifLahan::with('nilai')->get();
        // gunakan bobot_global untuk agregasi jika sub-kriteria digunakan,
        // namun jika Anda sebelumnya menyimpan bobot ke 'bobot' kolom, gunakan itu.
        // Saya pakai bobot_global jika tersedia, fallback ke bobot.
        $kriteria = Kriteria::all();

        foreach ($alternatifs as $alt) {
            $total = 0;

            foreach ($kriteria as $k) {
                $nilaiAlt = $alt->nilai->where('kriteria_id', $k->id)->first();
                if ($nilaiAlt) {
                    $bobotDipakai = $k->bobot_global ?? $k->bobot ?? 0;
                    $total += ($nilaiAlt->nilai ?? 0) * $bobotDipakai;
                }
            }

            //$alt->nilai_total = $total;
            $alt->save();
        }
    }

    /* =======================================================
     * 3. KLASIFIKASI LAHAN
     * ======================================================= */
    private function hitungKlasifikasi()
    {
        $alternatifs = AlternatifLahan::all();
        $batas = \App\Models\BatasKesesuaian::first();

        foreach ($alternatifs as $a) {
            $skor = $a->nilai_total ?? 0;

            if ($skor >= ($batas->batas_s1 ?? PHP_INT_MAX)) $kelas = 'S1';
            elseif ($skor >= ($batas->batas_s2 ?? PHP_INT_MAX)) $kelas = 'S2';
            elseif ($skor >= ($batas->batas_s3 ?? PHP_INT_MAX)) $kelas = 'S3';
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

    /* =======================================================
     * 4. METODE VIKOR
     * ======================================================= */
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

                // Anda bisa sesuaikan best/worst per kriteria, di sini dipakai contoh 5/1
                $best = 5;
                $worst = 1;

                $bobotDipakai = $k->bobot_global ?? $k->bobot ?? 0;

                $temp = $bobotDipakai * (($best - $nilai) / max(1e-12, ($best - $worst)));
                $sum += $temp;
                if ($temp > $max) $max = $temp;
            }

            $S[$a->id] = $sum;
            $R[$a->id] = $max;
        }

        if (empty($S) || empty($R)) return;

        $Smin = min($S);  $Smax = max($S);
        $Rmin = min($R);  $Rmax = max($R);

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
                    'v_value'       => $R[$altId] ?? 0,
                    'q_value'       => $q,
                    'hasil_ranking' => $ranking++
                ]
            );
        }
    }

    /* =======================================================
     * 5. SIMPAN LAPORAN (PDF + GEOJSON MAPBOX)
     * ======================================================= */
    private function simpanLaporan()
    {
        /* === Ambil data klasifikasi & ranking === */
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

        /* === Generate PDF === */
        $pdfView = view('admin.laporan.pdf-template', [
            'klasifikasi' => $hasil_klasifikasi,
            'ranking'     => $hasil_ranking,
            'tanggal'     => now()->format('d-m-Y H:i'),
        ])->render();

        $pdf = Pdf::loadHTML($pdfView);

        $pdfName = 'laporan_evaluasi_' . time() . '.pdf';
        Storage::put("public/laporan/{$pdfName}", $pdf->output());
        $path_pdf = "storage/laporan/{$pdfName}";

        /* === Buat GeoJSON === */
        $alternatifs = AlternatifLahan::with('klasifikasi')->get();
        $features = [];

        foreach ($alternatifs as $a) {
            if (!$a->geometry) continue;

            $geom = json_decode($a->geometry, true);

            // compress coords
            if (isset($geom['coordinates'][0])) {
                $coords = $geom['coordinates'][0];
                $step = max(1, intval(count($coords) / 50));
                $coords = array_values(array_filter($coords, fn($v,$i) => $i % $step === 0, ARRAY_FILTER_USE_BOTH));

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
        }

        $geojson = json_encode([
            "type" => "FeatureCollection",
            "features" => $features
        ]);

        $encoded = $this->encodeGeoJsonForMapbox($geojson);

        /* === Ambil gambar Mapbox === */
        $token = env('MAPBOX_TOKEN');

        $imgName = "peta_eval_" . time() . ".png";
        $saveDir = storage_path("app/public/laporan");
        if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);
        $savePath = "$saveDir/$imgName";

        $center = "106.15,-6.3,9";
        $size   = "1280x900";

        $geoSegment = $encoded ? "geojson({$encoded})/" : "";
        $staticUrl = "https://api.mapbox.com/styles/v1/mapbox/satellite-v9/static/{$geoSegment}{$center}/{$size}?access_token={$token}";

        $resp = Http::withOptions(['verify'=>false])->get($staticUrl);

        if ($resp->status() === 200 && str_contains($resp->header('Content-Type',''), 'image/png')) {
            file_put_contents($savePath, $resp->body());
        } else {
            // fallback: peta tanpa overlay
            $fallback = "https://api.mapbox.com/styles/v1/mapbox/satellite-v9/static/{$center}/{$size}?access_token={$token}";
            $resp2 = Http::withOptions(['verify'=>false])->get($fallback);

            file_put_contents($savePath, $resp2->body());
        }

        $path_peta = "storage/laporan/{$imgName}";

        /* === Simpan Laporan === */
        LaporanEvaluasi::create([
            'tanggal'           => now(),
            'hasil_klasifikasi' => json_encode($hasil_klasifikasi),
            'hasil_ranking'     => json_encode($hasil_ranking),
            'path_pdf'          => $path_pdf,
            'path_peta'         => $path_peta,
            'status_draft'      => 1
        ]);
    }

    private function encodeGeoJsonForMapbox($geojson)
    {
        if (!$geojson || $geojson === '{"type":"FeatureCollection","features":[]}') {
            return "";
        }

        $base64 = base64_encode($geojson);
        return rtrim(strtr($base64, '+/', '-_'), '=');
    }
}
