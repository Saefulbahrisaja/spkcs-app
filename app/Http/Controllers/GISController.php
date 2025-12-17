<?php
namespace App\Http\Controllers;

use App\Models\AlternatifLahan;
use App\Models\NilaiAlternatif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GISController extends Controller
{
    /**
     * Generate GeoJSON dengan multi atribut dan kelas
     */
    public function geojson(Request $request)
    {
        $alternatifs = AlternatifLahan::with(['klasifikasi', 'vikor', 'nilai'])->get();
        $features = [];

        foreach ($alternatifs as $alternatif) {
            // 1. GEOMETRY
            $geometry = $this->getGeometry($alternatif);
            if (!$geometry) {
                continue;
            }

            // 2. BASE PROPERTIES
            $baseProperties = $this->getBaseProperties($alternatif);

            // 2B. TAMBAHKAN SEMUA ATRIBUT
            foreach ($alternatif->nilai as $nilai) {
                $baseProperties[$nilai->atribut_nama] = $nilai->nilai_input;
            }

            // 3. FEATURE MODE KELAS
            $features[] = [
                'type'       => 'Feature',
                'geometry'   => $geometry,
                'properties' => array_merge($baseProperties, ['mode' => 'kelas'])
            ];

            // 4. FEATURE MODE ATRIBUT
            foreach ($alternatif->nilai as $nilai) {
                $features[] = [
                    'type'       => 'Feature',
                    'geometry'   => $geometry,
                    'properties' => $baseProperties
                ];
            }
        }

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features
        ]);
    }

    /**
     * Ambil geometry dari alternatif
     */
    private function getGeometry($alternatif)
    {
        if ($alternatif->geojson_path && Storage::disk('public')->exists($alternatif->geojson_path)) {
            $file = Storage::disk('public')->get($alternatif->geojson_path);
            $geojson = json_decode($file, true);

            if (($geojson['type'] ?? null) === 'FeatureCollection') {
                return $geojson['features'][0]['geometry'] ?? null;
            }
            return $geojson['geometry'] ?? null;
        }

        if ($alternatif->lat && $alternatif->lng) {
            return [
                'type'        => 'Point',
                'coordinates' => [floatval($alternatif->lng), floatval($alternatif->lat)]
            ];
        }

        return null;
    }

    /**
     * Ambil base properties dari alternatif
     */
    private function getBaseProperties($alternatif)
    {
        return [
            'alternatif_id'     => $alternatif->id,
            'lokasi'            => $alternatif->lokasi,
            'nilai_total'       => $alternatif->nilai_total,
            'kelas_kesesuaian'  => optional($alternatif->klasifikasi)->kelas_kesesuaian,
            'skor_normalisasi'  => optional($alternatif->klasifikasi)->skor_normalisasi,
            'vikor_ranking'     => optional($alternatif->vikor)->hasil_ranking,
            'vikor_q'           => optional($alternatif->vikor)->q_value,
            'vikor_v'           => optional($alternatif->vikor)->v_value,
        ];
    }

    /**
     * List atribut dinamis
     */
    public function atribut()
    {
        return NilaiAlternatif::select('atribut_nama')
            ->distinct()
            ->orderBy('atribut_nama')
            ->pluck('atribut_nama');
    }

    /**
     * Ringkasan luas per kelas
     */
    public function ringkasanLuas()
    {
        $alternatifs = AlternatifLahan::with(['klasifikasi'])->get();
        $result = ['S1' => [], 'S2' => [], 'S3' => [], 'N' => []];

        foreach ($alternatifs as $alternatif) {
            $path = $alternatif->geojson_path;
            if (!$path || !Storage::disk('public')->exists($path)) {
                continue;
            }

            $content = Storage::disk('public')->get($path);
            $geojson = json_decode($content, true);
            if (!$geojson) {
                continue;
            }

            $luas = $this->hitungLuasGeoJSON($geojson);
            $kelas = optional($alternatif->klasifikasi)->kelas_kesesuaian ?? 'N';
            $lokasi = $alternatif->lokasi;

            if (!isset($result[$kelas][$lokasi])) {
                $result[$kelas][$lokasi] = 0;
            }
            $result[$kelas][$lokasi] += $luas;
        }

        return response()->json($result);
    }

    /**
     * Hitung luas GeoJSON
     */
    private function hitungLuasGeoJSON($geojson)
    {
        if (($geojson['type'] ?? '') === 'FeatureCollection') {
            $total = 0;
            foreach ($geojson['features'] as $feature) {
                if (!isset($feature['geometry'])) {
                    continue;
                }
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

    /**
     * Hitung luas polygon
     */
    private function luasPolygon($coordinates)
    {
        $points = $coordinates[0];
        $first = $points[0];
        $last = end($points);

        if ($first !== $last) {
            $points[] = $first;
        }

        foreach ($points as &$point) {
            if (abs($point[0]) <= 90 && abs($point[1]) <= 180) {
                $point = [$point[1], $point[0]];
            }
        }

        $area = 0;
        $earthRadius = 6378137;

        for ($i = 0; $i < count($points) - 1; $i++) {
            $lon1 = deg2rad($points[$i][0]);
            $lat1 = deg2rad($points[$i][1]);
            $lon2 = deg2rad($points[$i + 1][0]);
            $lat2 = deg2rad($points[$i + 1][1]);

            $area += ($lon2 - $lon1) * (2 + sin($lat1) + sin($lat2));
        }

        return abs($area * $earthRadius * $earthRadius / 2);
    }
}
