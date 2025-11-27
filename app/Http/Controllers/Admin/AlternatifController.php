<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\NilaiAlternatif;
use Illuminate\Http\Request;

class AlternatifController extends Controller
{
    public function index()
    {
        return view('admin.alternatif.index', [
            'data' => AlternatifLahan::all()
        ]);
    }

    public function create()
    {
        return view('admin.alternatif.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'lokasi' => 'required|string',
            'geojson' => 'nullable|file|mimes:json,geojson|max:5000',
        ]);

        $data = $request->all();

        // Upload GeoJSON
        if ($request->hasFile('geojson')) {
            $file = $request->file('geojson');
            
            $path = $file->store('geojson', 'public'); 
            $data['geojson_path'] = $path;

            // Optional: detect geometry + centroid
            $geo = json_decode(file_get_contents($file->getRealPath()), true);

            if (isset($geo['features'][0]['geometry']['type'])) {
                $data['geometry_type'] = $geo['features'][0]['geometry']['type'];
            }

            // Auto centroid
            list($lat, $lng) = $this->getCentroid($geo);
            $data['lat'] = $lat;
            $data['lng'] = $lng;
        }

        AlternatifLahan::create($data);
        return redirect()->route('admin.alternatif.index')->with("success", "Alternatif berhasil disimpan");
    }


    public function formNilai($id)
    {
        return view('admin.alternatif.nilai', [
            'alternatif' => AlternatifLahan::findOrFail($id),
            'kriteria'   => Kriteria::all(),
        ]);
    }

    public function storeNilai(Request $request, $id)
    {
        foreach ($request->nilai as $kriteria_id => $nilai) {
            NilaiAlternatif::updateOrCreate(
                ['alternatif_id' => $id, 'kriteria_id' => $kriteria_id],
                ['nilai' => $nilai]
            );
        }

        return back()->with("success", "Nilai alternatif berhasil disimpan");
    }

    private function getCentroid(array $geo)
    {
        try {
            $geometry = $geo['features'][0]['geometry'];

            // Jika Point
            if ($geometry['type'] == 'Point') {
                return [$geometry['coordinates'][1], $geometry['coordinates'][0]];
            }

            // Jika Polygon
            if ($geometry['type'] == 'Polygon') {
                $polygon = $geometry['coordinates'][0]; // outer ring
                $latSum = 0; $lngSum = 0; $count = count($polygon);

                foreach ($polygon as $point) {
                    $lngSum += $point[0];
                    $latSum += $point[1];
                }

                return [$latSum / $count, $lngSum / $count];
            }

            // Jika MultiPolygon
            if ($geometry['type'] == 'MultiPolygon') {
                $firstPoly = $geometry['coordinates'][0][0];
                $latSum = 0; $lngSum = 0; $count = count($firstPoly);

                foreach ($firstPoly as $point) {
                    $lngSum += $point[0];
                    $latSum += $point[1];
                }

                return [$latSum / $count, $lngSum / $count];
            }

        } catch (\Exception $e) {
            return [null, null];
        }

        return [null, null];
    }

}
