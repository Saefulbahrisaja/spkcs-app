<?php
namespace App\Http\Controllers;

use App\Models\AlternatifLahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GISController extends Controller
{
    /* ============================================================
       GEOJSON UTAMA — mendukung multi atribut + kelas sekaligus
    ============================================================ */
    public function geojson(Request $req)
    {
        $alternatifs = AlternatifLahan::with(['klasifikasi', 'vikor', 'nilai'])->get();
        $features = [];

        foreach ($alternatifs as $a) {

            /* ============================================================
                1. GEOMETRY
            ============================================================ */
            $geom = null;

            if ($a->geojson_path && Storage::disk('public')->exists($a->geojson_path)) {
                $file = Storage::disk('public')->get($a->geojson_path);
                $gj   = json_decode($file, true);

                if (($gj['type'] ?? null) === 'FeatureCollection') {
                    $geom = $gj['features'][0]['geometry'] ?? null;
                } else {
                    $geom = $gj['geometry'] ?? null;
                }
            }
            elseif ($a->lat && $a->lng) {
                $geom = [
                    "type" => "Point",
                    "coordinates" => [floatval($a->lng), floatval($a->lat)]
                ];
            }

            if (!$geom) continue;

            /* ============================================================
                2. BASE PROPERTIES (kelas + info umum)
            ============================================================ */
            $baseProps = [
                'alternatif_id'     => $a->id,
                'lokasi'            => $a->lokasi,
                'nilai_total'       => $a->nilai_total,
                'kelas_kesesuaian'  => optional($a->klasifikasi)->kelas_kesesuaian,
                'skor_normalisasi'  => optional($a->klasifikasi)->skor_normalisasi,
                'vikor_ranking'     => optional($a->vikor)->hasil_ranking,
                'vikor_q'           => optional($a->vikor)->q_value,
                'vikor_v'           => optional($a->vikor)->v_value,
            ];

            /* ============================================================
                2B. TAMBAHKAN SEMUA ATRIBUT (untuk mode kelas & detail panel)
            ============================================================ */
            $allAttrs = [];
            foreach ($a->nilai as $n) {
                $allAttrs[$n->atribut_nama] = $n->nilai;
                //$allAttrs[$n->atribut_nama . '_id'] = $n->kriteria_id;
            }

            // merge → semua atribut masuk ke properties
            $baseProps = array_merge($baseProps, $allAttrs);

            /* ============================================================
                3. FEATURE MODE KELAS (1 per wilayah)
            ============================================================ */
            $classFeature = [
                'type'       => 'Feature',
                'geometry'   => $geom,
                'properties' => array_merge($baseProps, [
                    'mode' => 'kelas'
                ])
            ];
            $features[] = $classFeature;

            /* ============================================================
                4. FEATURE MODE ATRIBUT (1 per atribut per wilayah)
            ============================================================ */
            foreach ($a->nilai as $attr) {
                $properties = array_merge($baseProps, [
                    'mode'        => 'atribut',
                    'atribut'     => $attr->atribut_nama,
                    'nilai'       => $attr->nilai,
                    'kriteria_id' => $attr->kriteria_id,
                ]);

                $features[] = [
                    'type'       => 'Feature',
                    'geometry'   => $geom,
                    'properties' => $properties
                ];
            }
        }

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features
        ]);
    }


    /* ============================================================
        LIST ATRIBUT DINAMIS
    ============================================================ */
    public function atribut()
    {
        return \App\Models\NilaiAlternatif::select('atribut_nama')
            ->distinct()
            ->orderBy('atribut_nama')
            ->pluck('atribut_nama');
    }


    /* ============================================================
        RINGKASAN LUAS PER KELAS
    ============================================================ */
    public function ringkasanLuas()
    {
        $alternatifs = AlternatifLahan::with(['klasifikasi'])->get();

        $result = [
            'S1' => [],
            'S2' => [],
            'S3' => [],
            'N'  => []
        ];

        foreach ($alternatifs as $alt) {

            $path = $alt->geojson_path;
            if (!$path || !Storage::disk('public')->exists($path)) continue;

            $content = Storage::disk('public')->get($path);
            $geojson = json_decode($content, true);
            if (!$geojson) continue;

            $luas = $this->hitungLuasGeoJSON($geojson);

            $kelas = optional($alt->klasifikasi)->kelas_kesesuaian ?? 'N';
            $lokasi = $alt->lokasi;

            if (!isset($result[$kelas][$lokasi])) {
                $result[$kelas][$lokasi] = 0;
            }
            $result[$kelas][$lokasi] += $luas;
        }

        return response()->json($result);
    }


    /* ============================================================
        HITUNG LUAS GEOJSON
    ============================================================ */

    private function hitungLuasGeoJSON($geojson)
    {
        if (($geojson['type'] ?? '') === 'FeatureCollection') {
            $total = 0;
            foreach ($geojson['features'] as $feature) {
                if (!isset($feature['geometry'])) continue;
                $total += $this->hitungLuasGeoJSON($feature['geometry']);
            }
            return $total;
        }

        if (($geojson['type'] ?? '') === 'Polygon') {
            return $this->luasPolygon($geojson['coordinates']);
        }

        if (($geojson['type'] ?? '') === 'MultiPolygon') {
            $sum = 0;
            foreach ($geojson['coordinates'] as $polygon) {
                $sum += $this->luasPolygon($polygon);
            }
            return $sum;
        }

        return 0;
    }


    private function luasPolygon($coords)
    {
        $points = $coords[0];
        $first = $points[0];
        $last  = end($points);

        if ($first !== $last) $points[] = $first;

        foreach ($points as &$p) {
            if (abs($p[0]) <= 90 && abs($p[1]) <= 180) {
                $p = [$p[1], $p[0]];
            }
        }

        $area = 0;
        $R = 6378137;

        for ($i = 0; $i < count($points) - 1; $i++) {
            $lon1 = deg2rad($points[$i][0]);
            $lat1 = deg2rad($points[$i][1]);
            $lon2 = deg2rad($points[$i+1][0]);
            $lat2 = deg2rad($points[$i+1][1]);

            $area += ($lon2 - $lon1) * (2 + sin($lat1) + sin($lat2));
        }

        return abs($area * $R * $R / 2);
    }
}
