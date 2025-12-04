<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\NilaiAlternatif;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index()
    {
        return view('admin.wilayah.index', [
            'data' => AlternatifLahan::all(),
            'kriteria' => Kriteria::all(),
            'nilaiAlternatif' => NilaiAlternatif::all()->groupBy('alternatif_id'),
        ]);

    }

    public function create()
    {
        return view('admin.wilayah.create');
    }

    public function store(Request $request)
    {
        try {

            // Validasi untuk max 100 MB
            $request->validate([
                'lokasi' => 'required|string',

                'geojson' => [
                    'nullable',
                    'file',
                    'max:124000', // 100 MB
                    function ($attribute, $value, $fail) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        if (!in_array($ext, ['json', 'geojson'])) {
                            $fail("File harus berekstensi .json atau .geojson");
                        }
                    }
                ],
            ]);

            $data = $request->only(['lokasi']);

            if ($request->hasFile('geojson')) {

                $file = $request->file('geojson');

                \Log::info("=== [UPLOAD GEOJSON] File diterima: ".$file->getClientOriginalName()." | Size: ".$file->getSize()." bytes ===");

                // simpan file
                $path = $file->store('geojson', 'public');
                $data['geojson_path'] = $path;

                \Log::info("=== [UPLOAD GEOJSON] File berhasil disimpan ke storage: $path ===");

                // baca file setelah disimpan
                $fullPath = storage_path("app/public/$path");

                if (!file_exists($fullPath)) {
                    \Log::error("### [ERROR] File tidak ditemukan setelah upload: $fullPath ###");
                    return back()->with('error', 'Gagal membaca file setelah upload.');
                }

                $content = @file_get_contents($fullPath);

                if (!$content) {
                    \Log::error("### [ERROR] Gagal membaca isi file: $fullPath ###");
                    return back()->with('error', 'Gagal membaca isi file.');
                }

                $geo = json_decode($content, true);

                if (!$geo) {
                    \Log::error("### [ERROR] JSON tidak valid pada file: $fullPath ###");
                    return back()->with('error', 'File GeoJSON tidak valid.');
                }

                // geometry type
                if (isset($geo['features'][0]['geometry']['type'])) {
                    $data['geometry_type'] = $geo['features'][0]['geometry']['type'];
                }

                // centroid
                [$lat, $lng] = $this->getCentroid($geo);
                $data['lat'] = $lat;
                $data['lng'] = $lng;

                \Log::info("=== [UPLOAD GEOJSON] Geometry Type: {$data['geometry_type']}, LAT: $lat, LNG: $lng ===");
            }

            AlternatifLahan::create($data);

            \Log::info("=== [UPLOAD GEOJSON] DATA BERHASIL DISIMPAN ===");

            return redirect()
                ->route('admin.wilayah.index')
                ->with("success", "Alternatif berhasil disimpan");

        } catch (\Throwable $e) {

            // LOGGING ERROR DETAIL
            \Log::error("### [FATAL ERROR UPLOAD GEOJSON] ###");
            \Log::error("Message: " . $e->getMessage());
            \Log::error("File: " . $e->getFile());
            \Log::error("Line: " . $e->getLine());

            // TAMPILKAN ERROR DI BROWSER
            return back()->with('error', '
                <div style="padding:20px; background:#ffdddd; border-left:5px solid red;">
                    <h2 style="color:red;">⚠️ ERROR UPLOAD GEOJSON</h2>
                    <p><strong>'.$e->getMessage().'</strong></p>
                    <small>'.$e->getFile().' Line '.$e->getLine().'</small>
                </div>
            ');
        }
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
