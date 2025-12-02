<?php

namespace App\Http\Controllers;


use App\Models\AlternatifLahan;
use App\Models\KlasifikasiLahan;
use App\Models\PemeringkatanVikor;
use Illuminate\Http\Request;

class GISController extends Controller
{
    public function geojson(Request $req)
    {
        $alternatifs = AlternatifLahan::with(['klasifikasi','vikor'])->get();

        $features = [];
        foreach ($alternatifs as $a) {
            $geom = $a->geom ? json_decode($a->geom, true) : null;
            // jika geom kosong, skip atau buat point placeholder
            if (!$geom) continue;

            $properties = [
                'alternatif_id' => $a->id,
                'lokasi' => $a->lokasi,
                'nilai_skor' => $a->nilai_skor,
                'nilai_total' => $a->nilai_total,
                'kelas_kesesuaian' => optional($a->klasifikasi)->kelas_kesesuaian,
                'vikor_ranking' => optional($a->vikor)->hasil_ranking,
                'vikor_q' => optional($a->vikor)->q_value ?? optional($a->vikor)->q_value,
            ];

            $features[] = [
                'type' => 'Feature',
                'geometry' => $geom,
                'properties' => $properties
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }
}
