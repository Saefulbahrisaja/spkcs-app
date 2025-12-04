<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\AlternatifLahan;
use PDF;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua alternatif dengan relasi klasifikasi jika ada
        $alternatifs = AlternatifLahan::with('klasifikasi')->get();

        // Group menurut kelas_kesesuaian (ambil dari kolom atau relasi klasifikasi)
        $grouped = $alternatifs->groupBy(function($item) {
            // jika kolom kelas_kesesuaian langsung ada di model
            if (isset($item->kelas_kesesuaian) && $item->kelas_kesesuaian) {
                return $item->kelas_kesesuaian;
            }
            // kalau menggunakan relasi klasifikasi
            if ($item->klasifikasi && isset($item->klasifikasi->kelas_kesesuaian)) {
                return $item->klasifikasi->kelas_kesesuaian;
            }
            return 'N'; // default
        });

        $S1 = $grouped['S1'] ?? collect();
        $S2 = $grouped['S2'] ?? collect();
        $S3 = $grouped['S3'] ?? collect();
        $N  = $grouped['N']  ?? collect();

        // Hitung luas total (ha) per kelas, coba beberapa sumber:
        $totalS1 = $this->sumLuasCollection($S1);
        $totalS2 = $this->sumLuasCollection($S2);
        $totalS3 = $this->sumLuasCollection($S3);
        $totalN  = $this->sumLuasCollection($N);

        return view('welcome', compact(
            'S1','S2','S3','N',
            'totalS1','totalS2','totalS3','totalN'
        ));
    }

    /**
     * Sum luas untuk koleksi AlternatifLahan (kembalikan ha)
     */
    private function sumLuasCollection($collection)
    {
        $sum = 0;

        foreach ($collection as $a) {
            // 1) Jika ada kolom 'luas' (dalam m2)
            if (isset($a->luas) && is_numeric($a->luas)) {
                $sum += (float) $a->luas;
                continue;
            }

            // 2) Jika ada kolom 'geom' menyimpan GeoJSON string
            if (isset($a->geom) && $a->geom) {
                try {
                    $geo = json_decode($a->geom, true);
                    $sum += $this->hitungLuasGeoJSON($geo);
                    continue;
                } catch (\Throwable $e) {
                    // ignore, lanjut
                }
            }

            // 3) Jika ada kolom 'geojson_path' yang menunjuk file di storage
            if (isset($a->geojson_path) && $a->geojson_path && Storage::exists($a->geojson_path)) {
                try {
                    $content = Storage::get($a->geojson_path);
                    $geo = json_decode($content, true);
                    $sum += $this->hitungLuasGeoJSON($geo);
                    continue;
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // 4) jika tidak ada data luas, skip (anggap 0)
        }

        // kembalikan dalam hektar (jika sum di m2)
        return $sum / 10000;
    }

    /**
     * Menghitung luas polygon GeoJSON (mengembalikan m2)
     * Kopi sederhana (spherical approximation) — sesuai fungsi sebelumnya.
     */
    private function hitungLuasGeoJSON($geojson)
    {
        if (!is_array($geojson) || empty($geojson['type'])) return 0;

        // Jika featurecollection
        if ($geojson['type'] === 'FeatureCollection' && isset($geojson['features'])) {
            $total = 0;
            foreach ($geojson['features'] as $f) {
                if (isset($f['geometry'])) {
                    $total += $this->luasGeometry($f['geometry']);
                }
            }
            return $total;
        }

        // Jika geometry langsung
        if (isset($geojson['type']) && isset($geojson['coordinates'])) {
            return $this->luasGeometry($geojson);
        }

        return 0;
    }

    private function luasGeometry($geom)
    {
        if (!isset($geom['type'])) return 0;

        if ($geom['type'] === 'Polygon') {
            return $this->luasPolygon($geom['coordinates']);
        }
        if ($geom['type'] === 'MultiPolygon') {
            $total = 0;
            foreach ($geom['coordinates'] as $poly) {
                $total += $this->luasPolygon($poly);
            }
            return $total;
        }
        return 0;
    }

    private function luasPolygon($coords)
    {
        if (!isset($coords[0]) || count($coords[0]) < 3) return 0;

        $area = 0;
        $points = $coords[0];
        $radius = 6378137; // meter

        for ($i = 0; $i < count($points) - 1; $i++) {
            $lon1 = deg2rad($points[$i][0]);
            $lat1 = deg2rad($points[$i][1]);
            $lon2 = deg2rad($points[$i+1][0]);
            $lat2 = deg2rad($points[$i+1][1]);

            $area += ($lon2 - $lon1) * (2 + sin($lat1) + sin($lat2));
        }

        return abs($area * $radius * $radius / 2);
    }

 
    public function exportPDF()
    {
        // data sama seperti index
        $alternatifs = AlternatifLahan::with('klasifikasi')->get();
        $grouped = $alternatifs->groupBy(fn($item) =>
            $item->kelas_kesesuaian ?? ($item->klasifikasi->kelas_kesesuaian ?? 'N')
        );

        $S1 = $grouped['S1'] ?? collect();
        $S2 = $grouped['S2'] ?? collect();
        $S3 = $grouped['S3'] ?? collect();
        $N  = $grouped['N']  ?? collect();

        $totalS1 = $this->sumLuasCollection($S1);
        $totalS2 = $this->sumLuasCollection($S2);
        $totalS3 = $this->sumLuasCollection($S3);
        $totalN  = $this->sumLuasCollection($N);

        $pdf = PDF::loadView('pdf.laporan', compact(
            'S1','S2','S3','N',
            'totalS1','totalS2','totalS3','totalN'
        ));

        return $pdf->download("Laporan_Kesesuaian_Lahan_Padi_Sawah.pdf");
    }

}
