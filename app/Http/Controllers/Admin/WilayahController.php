<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\NilaiAlternatif;
use Illuminate\Support\Facades\Storage;
use geoPHP;
use Shapefile\ShapefileReader;
use App\Services\ShpAHPService;

class WilayahController extends Controller
{
    public function index()
    {
        return view('admin.wilayah.index', [
            'data'=>AlternatifLahan::all(),
            'kriteria'=>Kriteria::all(),
            'nilaiAlternatif'=>NilaiAlternatif::all()->groupBy('alternatif_id'),
        ]);
    }

    public function create()
    {
        return view('admin.wilayah.create');
    }

    public function store(Request $request, ShpAHPService $ahp)
    {
        $request->validate([
            'lokasi'=>'required|string',
            'geo'=>'required|file|max:150000'
        ]);

        $file = $request->file('geo');
        $ext  = strtolower($file->getClientOriginalExtension());

        if ($ext !== 'zip') {
            return back()->with('error','Harus ZIP shapefile.');
        }

        // ========== 1. extract ZIP ==========
        $zipPath = $file->store("shp_upload","public");
        $extractDir = storage_path("app/public/shp_tmp/".uniqid("unz_"));
        mkdir($extractDir,0777,true);

        $zip = new \ZipArchive;
        $zip->open(storage_path("app/public/".$zipPath));
        $zip->extractTo($extractDir);
        $zip->close();

        $shp = $this->findShp($extractDir);
        if (!$shp) return back()->with("error","ZIP tidak berisi file .shp");

        // ========== 2. Baca shapefile ==========
        $reader = new ShapefileReader($shp);
        $newAltIds = [];

        // Mapping atribut → kriteria
        $map = [
            "C_ORG"=>1,
            "LERENG"=>2,
            "LST"=>3,
            "HARA"=>4
        ];

        while ($rec = $reader->fetchRecord()) {

    if ($rec->isDeleted()) continue;

    $raw = $rec->getGeoJSON();
    $gj  = json_decode($raw, true);

    if (!$gj) continue;

    //-----------------------------------------------
    // 1) Normalisasi struktur GeoJSON dari shapefile
    //-----------------------------------------------
    $geom  = null;
    $props = [];

    // CASE A: Feature
    if (isset($gj['geometry'])) {
        $geom  = $gj['geometry'];
        $props = $gj['properties'] ?? [];

    // CASE B: FeatureCollection
    } elseif (isset($gj['features'][0]['geometry'])) {
        $geom  = $gj['features'][0]['geometry'];
        $props = $gj['features'][0]['properties'] ?? [];

    // CASE C: Geometry Only (Polygon/MultiPolygon)
    } elseif (isset($gj['type']) && in_array($gj['type'], ['Polygon','MultiPolygon','Point'])) {
        $geom  = $gj;
        $props = [];
        $gj = [
            "type" => "Feature",
            "geometry" => $geom,
            "properties" => new \stdClass
        ];

    } else {
        \Log::warning("Record tanpa geometry valid: " . substr($raw,0,400));
        continue;
    }


    // CASE: geometry kosong atau rusak
    if (!$geom || !isset($geom['type'])) {
        \Log::warning("Geometry tidak memiliki 'type': " . json_encode($geom));
        continue;
    }

    //-----------------------------------------------
    // 2) Hitung centroid
    //-----------------------------------------------
    $centroid = $this->centroid($geom);


    //-----------------------------------------------
    // 3) Buat Alternatif
    //-----------------------------------------------
    $alt = AlternatifLahan::create([
        'lokasi'        => $request->lokasi,
        'geometry_type' => $geom['type'],
        'lat'           => $centroid[0],
        'lng'           => $centroid[1],
    ]);

    $newAltIds[] = $alt->id;


    //-----------------------------------------------
    // 4) Simpan sebagai GeoJSON SINGLE-FEATURE
    //-----------------------------------------------
    Storage::disk('public')->put(
        "geojson/alt_{$alt->id}.geojson",
        json_encode([
            "type" => "FeatureCollection",
            "features" => [$gj]
        ])
    );


    //-----------------------------------------------
    // 5) Mapping atribut SHP ke nilai kriteria
    //-----------------------------------------------
    foreach ($map as $field => $kid) {
        if (isset($props[$field])) {
            NilaiAlternatif::updateOrCreate(
                ['alternatif_id'=>$alt->id,'kriteria_id'=>$kid],
                ['nilai'=>$props[$field]]
            );
        }
    }


    //-----------------------------------------------
    // 6) INTERSECTION SUB-KRITERIA
    //-----------------------------------------------
    $layers = $this->loadSubcriteriaLayers();

    foreach ($map as $field => $kid) {

        if (!isset($layers[$field])) continue;

        $value = $this->intersectAndExtractValue($geom, $layers[$field], $field);

        if ($value !== null) {
            NilaiAlternatif::updateOrCreate(
                ['alternatif_id' => $alt->id, 'kriteria_id' => $kid],
                ['nilai_raw' => $value, 'nilai' => $value]
            );
        }
    }
}


        // ========== 3. Normalisasi AHP (pakai bobot dari SF-AHP hasil expert) ==========
        $scores = $ahp->normalizeAndCompute($newAltIds, $map);

        // ========== 4. Klasifikasi Lahan ==========
        $this->classify($newAltIds);

        return redirect()->route('admin.wilayah.index')
            ->with("success","Import SHP selesai, skor AHP dihitung, klasifikasi selesai!");
    }

    private function findShp($dir)
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($it as $f) {
            if ($f->isFile() && strtolower(substr($f->getFilename(),-4))==='.shp') {
                return $f->getPathname();
            }
        }
        return null;
    }

    private function centroid($geom)
    {
        if ($geom['type']==='Polygon') $poly=$geom['coordinates'][0];
        else $poly=$geom['coordinates'][0][0];

        $y = array_sum(array_column($poly,1))/count($poly);
        $x = array_sum(array_column($poly,0))/count($poly);
        return [$y,$x];
    }

    private function classify($ids)
    {
        $b = \App\Models\BatasKesesuaian::first();
        if (!$b) return;

        foreach($ids as $id){
            $alt = AlternatifLahan::find($id);
            if (!$alt) continue;

            $v = $alt->nilai_total;
            if ($v >= $b->batas_s1) $kelas='S1';
            elseif ($v >= $b->batas_s2) $kelas='S2';
            elseif ($v >= $b->batas_s3) $kelas='S3';
            else $kelas='N';

            \App\Models\KlasifikasiLahan::updateOrCreate(
                ['alternatif_id'=>$id],
                ['skor_normalisasi'=>$v,'kelas_kesesuaian'=>$kelas]
            );
        }
    }

    private function intersectAndExtractValue(array $geometry, $layerFeatures, string $field)
    {
        $poly1 = geoPHP::load(json_encode($geometry), 'json');
        $values = [];
        
        foreach ($layerFeatures as $feat) {

            if (!isset($feat['geometry'])) continue;
            $poly2 = geoPHP::load(json_encode($feat['geometry']), 'json');
            
            $inter = $poly1->intersection($poly2);

            if ($inter && $inter->getArea() > 0) {
                $val = $feat['properties'][$field] ?? null;
                if ($val !== null) $values[] = $val;
            }
        }

        if (empty($values)) return null;

        return array_sum($values) / count($values); // mean
    }

    private function loadSubcriteriaLayers(): array
    {
        $layers = [];

        $map = [
            "C_ORG"  => "c_org.geojson",
            "LERENG" => "lereng.geojson",
            "LST"    => "lst.geojson",
            "HARA"   => "hara.geojson",
        ];

        foreach ($map as $key => $file) {
            $path = storage_path("app/public/layers/" . $file);
            if (file_exists($path)) {
                $layers[$key] = json_decode(file_get_contents($path), true)['features'] ?? [];
            }
        }

        return $layers;
    }

}
