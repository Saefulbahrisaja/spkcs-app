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
    private function parseNilaiNumerik(string $atribut, string $nilai): ?float
    {
        $v = strtolower(trim($nilai));
        $v = str_replace(',', '.', $v);

        return match (strtolower($atribut)) {
            'suhu rata-rata'   => floatval($v),
            'kelembaban'      => floatval(str_replace('%', '', $v)),
            'bulan kering'    => floatval($v),
            'curah hujan'     => floatval(str_replace('mm', '', $v)),
            'drainase'        => floatval($v), // skor
            'texture'         => floatval($v), // skor
            'kedalaman tanah' => floatval(str_replace('cm', '', $v)),
            'ktk'             => floatval(str_replace(['cmol','/kg'], '', $v)),
            'kejenuhan basa'  => floatval(str_replace('%', '', $v)),
            'ph tanah'        => floatval($v),
            'c-organik'       => floatval(str_replace('%', '', $v)),
            default           => null
        };
    }


    public function index()
    {
        return view('admin.wilayah.index', [
            'data' => AlternatifLahan::all(),
            'nilaiAlternatif' => NilaiAlternatif::all()->groupBy('alternatif_id'),
        ]);
    }

    public function create()
    {
        $subKriteria = Kriteria::whereNotNull('parent_id')
            ->orderBy('nama_kriteria')
            ->get(['id','nama_kriteria']);

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
            'existingWilayah' => $existingWilayah,
            'subKriteria'     => $subKriteria,
        ]);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'geo'            => 'required|file|max:150000',
            'nilai_storage'  => 'required|string',
        ]);

        $nilaiWilayah = json_decode($request->nilai_storage, true);
        if (!$nilaiWilayah) {
            return back()->with('error', 'Data atribut tidak valid.');
        }

        /* ================== BACA GEOJSON / SHP ================= */
        $file = $request->file('geo');
        $geojsonData = [];

        if (str_ends_with(strtolower($file->getClientOriginalName()), '.zip')) {
            $zipPath = $file->store("shp_upload", "public");
            $extractDir = storage_path("app/public/shp_tmp/" . uniqid());
            mkdir($extractDir, 0777, true);

            $zip = new ZipArchive();
            $zip->open(storage_path("app/public/" . $zipPath));
            $zip->extractTo($extractDir);
            $zip->close();

            $reader = new ShapefileReader($this->findShp($extractDir));
            while ($rec = $reader->fetchRecord()) {
                if (!$rec->isDeleted()) {
                    $geojsonData[] = json_decode($rec->getGeoJSON(), true);
                }
            }
        } else {
            $gj = json_decode(file_get_contents($file->getRealPath()), true);
            $geojsonData = $gj['features'];
        }

        /* ================== SIMPAN PER POLYGON ================= */
        foreach ($nilaiWilayah as $idx => $wil) {

            $namaWilayah = trim($wil['nama'] ?? "Wilayah-$idx");
            $atributList = $wil['atribut'] ?? [];
            $featureRaw  = $geojsonData[$idx] ?? null;
            if (!$featureRaw) continue;

            $feature = $this->normalizeFeature($featureRaw);
            $geom    = $feature['geometry'];
            $centroid = $this->centroid($geom);

            /* === UPDATE JIKA NAMA SAMA === */
            $alt = AlternatifLahan::where('lokasi', $namaWilayah)->first();

            if (!$alt) {
                $alt = AlternatifLahan::create([
                    'lokasi'        => $namaWilayah,
                    'geometry_type' => $geom['type'],
                    'lat'           => $centroid[1],
                    'lng'           => $centroid[0],
                ]);
            } else {
                NilaiAlternatif::where('alternatif_id', $alt->id)->delete();
            }

            /* === SIMPAN GEOJSON === */
            $path = "geojson/alt_{$alt->id}.geojson";
            Storage::disk('public')->put($path, json_encode([
                "type" => "FeatureCollection",
                "features" => [$feature]
            ]));
            $alt->update(['geojson_path' => $path]);

            /* === SIMPAN ATRIBUT === */
            foreach ($atributList as $a) {
                if (empty($a['nama']) || empty($a['nilai'])) continue;

                $nilaiNumerik = $this->parseNilaiNumerik($a['nama'], $a['nilai']);
                if ($nilaiNumerik === null) continue;

                $kid = Kriteria::where('nama_kriteria', $a['nama'])->value('id');

                NilaiAlternatif::create([
                    'alternatif_id' => $alt->id,
                    'kriteria_id'   => $kid,
                    'nilai_input'   => $a['nilai'],  
                    'nilai'         => $nilaiNumerik,
                    'atribut_nama'  => $a['nama'],  
                ]);
            }
        }

        return redirect()
            ->route('admin.wilayah.index')
            ->with('success', 'Data wilayah & atribut berhasil disimpan.');
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
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $f) {
                if ($f->isFile() && str_ends_with(strtolower($f->getFilename()), '.shp')) {
                    return $f->getPathname();
                }
            }
            return null;
        }


    private function normalizeFeature($item)
    {
        return isset($item['geometry']) ? $item : [
            "type" => "Feature",
            "geometry" => $item,
            "properties" => []
        ];
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

            return [
                array_sum(array_column($coords, 0)) / count($coords),
                array_sum(array_column($coords, 1)) / count($coords)
            ];
        }
    }

    
}
