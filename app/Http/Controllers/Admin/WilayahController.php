<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlternatifLahan;
use App\Models\NilaiAlternatif;
use App\Models\Kriteria;
use Illuminate\Support\Facades\Storage;
use Shapefile\ShapefileReader;
use geoPHP;
use ZipArchive;

class WilayahController extends Controller
{
    public function index()
    {
        return view('admin.wilayah.index', [
            'data' => AlternatifLahan::all(),
            'nilaiAlternatif' => NilaiAlternatif::all()->groupBy('alternatif_id'),
        ]);
    }

    public function create()
    {
        $existingWilayah = AlternatifLahan::with('nilaiDinamis')->get()->map(function($w){

            // Ambil GeoJSON Feature
            $feature = null;
            if ($w->geojson_path && Storage::disk('public')->exists($w->geojson_path)) {
                $json = json_decode(Storage::disk('public')->get($w->geojson_path), true);
                $feature = $json['features'][0] ?? null;
            }

            return [
                'lokasi' => $w->lokasi,
                'lat'    => $w->lat,
                'lng'    => $w->lng,
                'geojson' => $feature, // Feature lengkap (geometry+properties)

                'nilai_dinamis' => $w->nilaiDinamis->map(function($n){
                    return [
                        'atribut_nama' => $n->atribut_nama,
                        'nilai'        => $n->nilai
                    ];
                })->toArray()
            ];
        });

        return view('admin.wilayah.create', [
            'existingWilayah' => $existingWilayah
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'lokasi' => 'nullable|string',
            'geo'    => 'required|file|max:150000',
            'nilai_storage' => 'required|string',
        ]);

        $nilaiWilayah = json_decode($request->nilai_storage, true);
        if (!$nilaiWilayah) return back()->with('error', 'Data wizard tidak valid.');

        // ==== BACA FILE GEO ====
        $file = $request->file('geo');
        $geojsonData = [];

        if (str_ends_with(strtolower($file->getClientOriginalName()), '.zip')) {

            // extract ZIP
            $zipPath = $file->store("shp_upload", "public");
            $extractDir = storage_path("app/public/shp_tmp/" . uniqid());
            mkdir($extractDir, 0777, true);

            $zip = new ZipArchive();
            $zip->open(storage_path("app/public/" . $zipPath));
            $zip->extractTo($extractDir);
            $zip->close();

            $shpFile = $this->findShp($extractDir);
            if (!$shpFile) return back()->with('error','ZIP tidak berisi SHP lengkap');

            $reader = new ShapefileReader($shpFile);

            while ($rec = $reader->fetchRecord()) {
                if (!$rec->isDeleted()) {
                    $geojsonData[] = json_decode($rec->getGeoJSON(), true);
                }
            }

        } else {
            $gj = json_decode(file_get_contents($file->getRealPath()), true);
            $geojsonData = $gj['features'];
        }

        // ==== SIMPAN PER POLYGON ====
        $savedIDs = [];

        foreach ($nilaiWilayah as $idx => $wil) {

            $namaWilayah = $wil['nama'] ?? "Wilayah-$idx";
            $atributList = $wil['atribut'] ?? [];
            $rawFeature  = $geojsonData[$idx] ?? null;

            if (!$rawFeature) continue;

            $feature = $this->normalizeFeature($rawFeature);
            $geom    = $feature['geometry'];

            $centroid = $this->centroid($geom);

            $alt = AlternatifLahan::create([
                'lokasi'        => $namaWilayah,
                'geometry_type' => $geom['type'],
                'lat'           => $centroid[1],
                'lng'           => $centroid[0],
            ]);

            $savedIDs[] = $alt->id;

            // simpan file geojson polygon
            $path = "geojson/alt_{$alt->id}.geojson";
            Storage::disk('public')->put($path, json_encode([
                "type" => "FeatureCollection",
                "features" => [ $feature ]
            ]));

            $alt->update(['geojson_path' => $path]);

            // simpan atribut dinamis
            foreach ($atributList as $a){
                if (!$a['nama']) continue;

                $kid = Kriteria::where('nama_kriteria', $a['nama'])->value('id');
                NilaiAlternatif::create([
                    'alternatif_id' => $alt->id,
                    'kriteria_id'   => $kid,
                    'atribut_nama'  => $a['nama'],
                    'nilai'         => $a['nilai'],
                ]);
            }
        }

        return redirect()
            ->route('admin.wilayah.index')
            ->with('success', count($savedIDs) . ' wilayah berhasil disimpan!');
    }

    public function destroy($id)
{
    $alt = AlternatifLahan::findOrFail($id);

    // Hapus file GeoJSON bila ada
    if ($alt->geojson_path && Storage::disk('public')->exists($alt->geojson_path)) {
        Storage::disk('public')->delete($alt->geojson_path);
    }

    // Hapus nilai atribut dinamis
    NilaiAlternatif::where('alternatif_id', $id)->delete();

    // Hapus data utama
    $alt->delete();

    return back()->with('success', 'Wilayah berhasil dihapus beserta file dan atributnya!');
}




    private function findShp($dir)
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($it as $f){
            if ($f->isFile() && str_ends_with(strtolower($f->getFilename()), '.shp')){
                return $f->getPathname();
            }
        }
        return null;
    }

    private function normalizeFeature($item)
    {
        if (isset($item['geometry'])) return $item;

        if (isset($item['coordinates'])) {
            return [
                "type" => "Feature",
                "geometry" => $item,
                "properties" => []
            ];
        }
        return null;
    }

    private function centroid($geom)
    {
        try {
            $g = geoPHP::load(json_encode($geom), 'json');
            $c = $g->centroid();
            return [$c->x(), $c->y()];
        } catch (\Exception $e) {

            $coords = ($geom['type'] === 'Polygon')
                ? $geom['coordinates'][0]
                : $geom['coordinates'][0][0];

            $xs = array_column($coords, 0);
            $ys = array_column($coords, 1);

            return [ array_sum($xs)/count($xs), array_sum($ys)/count($ys) ];
        }
    }
}
