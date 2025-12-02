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
}
