<?php
namespace App\Http\Controllers;

use App\Models\AlternatifLahan;
use Illuminate\Http\Request;

class GISController extends Controller
{
    public function geojson(Request $req)
    {
        $alternatifs = AlternatifLahan::with(['klasifikasi','vikor'])->get();

        $features = [];

        foreach ($alternatifs as $a) {

            //------------------------------------
            // 1. Tentukan geometry
            //------------------------------------
            $geom = null;

            // a) Jika punya file GEOJSON
            if ($a->geojson_path && file_exists(storage_path('app/public/'.$a->geojson_path))) {

                $fileContent = file_get_contents(storage_path('app/public/'.$a->geojson_path));
                $geom = json_decode($fileContent, true);

            }
            // b) Jika hanya ada lat & lng → buat POINT GeoJSON
            elseif ($a->lat && $a->lng) {

                $geom = [
                    "type" => "Point",
                    "coordinates" => [ floatval($a->lng), floatval($a->lat) ]
                ];

            }
            else {
                // Jika tidak ada geometry, skip
                continue;
            }

            //------------------------------------
            // 2. Set properties untuk popup
            //------------------------------------
            $properties = [
                'alternatif_id'     => $a->id,
                'lokasi'            => $a->lokasi,
                'nilai_total'       => $a->nilai_total,
                'kelas_kesesuaian'  => optional($a->klasifikasi)->kelas_kesesuaian,
                'skor_normalisasi'  => optional($a->klasifikasi)->skor_normalisasi,
                'vikor_ranking'     => optional($a->vikor)->hasil_ranking,
                'vikor_q'           => optional($a->vikor)->q_value,
                'vikor_v'           => optional($a->vikor)->v_value,
            ];

            //------------------------------------
            // 3. Masukkan ke features
            //------------------------------------
            $features[] = [
                'type'       => 'Feature',
                'geometry'   => $geom,
                'properties' => $properties
            ];
        }

        //------------------------------------
        // 4. Return FeatureCollection
        //------------------------------------
        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features
        ]);
    }

    

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

            // === AMBIL FILE GEOJSON ===
            $path = $alt->geojson_path;

            if (!$path || !\Storage::disk('public')->exists($path)) {
                continue;
            }

            $content = \Storage::disk('public')->get($path);
            $geojson = json_decode($content, true);


            if (!$geojson || !isset($geojson['type'])) {
                continue;
            }

            // === HITUNG LUAS ===
            $luas = $this->hitungLuasGeoJSON($geojson);

            // Kelas
            $kelas = optional($alt->klasifikasi)->kelas_kesesuaian ?? 'N';
            $lokasi = $alt->lokasi;

            if (!isset($result[$kelas][$lokasi])) {
                $result[$kelas][$lokasi] = 0;
            }

            $result[$kelas][$lokasi] += $luas;
        }

        return response()->json($result);
    }



    /**
     * Menghitung luas polygon GeoJSON dalam meter persegi
     */
  private function hitungLuasGeoJSON($geojson)
    {
        // 1. Jika tipe FeatureCollection
        if (($geojson['type'] ?? '') === 'FeatureCollection') {
            // Loop semua fitur, hitung semua polygon
            $total = 0;

            foreach ($geojson['features'] as $feature) {
                if (!isset($feature['geometry'])) continue;

                $total += $this->hitungLuasGeoJSON($feature['geometry']);
            }

            return $total;
        }

        // 2. Polygon
        if (($geojson['type'] ?? '') === 'Polygon') {
            return $this->luasPolygon($geojson['coordinates']);
        }

        // 3. MultiPolygon
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
     * Rumus luas polygon di bumi (haversine / spherical)
     */
    private function luasPolygon($coords)
        {
            $points = $coords[0];

            // Jika polygon belum tertutup → tutup
            $first = $points[0];
            $last = end($points);

            if ($first !== $last) {
                $points[] = $first;
            }

            // Fix LAT/LNG jika format Leaflet (lat, lng)
            foreach ($points as &$p) {
                $lat = $p[1];
                $lng = $p[0];

                // Jika elemen 1 > elemen 0 → kemungkinan lat,lng → balik
                if (abs($p[0]) <= 90 && abs($p[1]) <= 180) {
                    $p = [$p[1], $p[0]]; // swap → lng, lat
                }
            }

            // Hitung area spherical
            $area = 0;
            $radius = 6378137;

            for ($i = 0; $i < count($points) - 1; $i++) {

                $lon1 = deg2rad($points[$i][0]);
                $lat1 = deg2rad($points[$i][1]);
                $lon2 = deg2rad($points[$i+1][0]);
                $lat2 = deg2rad($points[$i+1][1]);

                $area += ($lon2 - $lon1) * (2 + sin($lat1) + sin($lat2));
            }

            return abs($area * $radius * $radius / 2);
        }
}
