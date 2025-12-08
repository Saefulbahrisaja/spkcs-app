@extends('layouts.app')

@section('content')
<div class="container">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.wilayah.index') }}">Daftar Lokasi</a></li>
        <li class="breadcrumb-item active">Tambah Poligon Wilayah (Preview)</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <strong>Form Tambah Wilayah — Preview Peta</strong>
        </div>

        <div class="card-body">
            <form id="uploadForm" action="{{ route('admin.wilayah.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- NAMA LOKASI --}}
                <div class="mb-3">
                    <label class="form-label">Nama Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" placeholder="Masukkan nama lokasi" required>
                </div>

                {{-- UPLOAD SHP ZIP / GEOJSON --}}
                <div class="mb-3">
                    <label class="form-label">Upload GeoJSON / ZIP (Shapefile)</label>
                    <input id="geoFile" type="file" name="geo" class="form-control" accept=".zip,.geojson,.json" required>
                    <div id="fileHelp" class="form-text">Mendukung: .zip (shapefile), .geojson, .json</div>
                </div>

                {{-- PREVIEW MAP --}}
                <div class="mb-3">
                    <label class="form-label">Preview Peta</label>
                    <div id="mapPreview" style="height:420px;border:1px solid #ddd"></div>
                    <small class="text-muted">Polygon akan muncul di peta di atas setelah file dipilih.</small>
                </div>

                {{-- FEATURE LIST / PROPERTIES --}}
                <div class="mb-3">
                    <label class="form-label">Daftar Feature (atribut)</label>
                    <div id="featureList" style="max-height:240px; overflow:auto; border:1px solid #eee; padding:8px; background:#fafafa;">
                        <em>Belum ada file yang dipilih.</em>
                    </div>
                </div>

                {{-- TOMBOL --}}
                <button id="submitBtn" class="btn btn-success" disabled>
                    <i class="fas fa-save"></i> Simpan Wilayah (Upload)
                </button>
                <a href="{{ route('admin.wilayah.index') }}" class="btn btn-secondary ms-2">Batal</a>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<!-- JSZip untuk membaca ZIP di browser -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>

<!-- shpjs -->
<script src="https://unpkg.com/shpjs@latest/dist/shp.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const map = L.map('mapPreview').setView([-6.2, 106.8], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let geoLayer = null;
    let currentGeoJson = null;

    const fileInput = document.getElementById('geoFile');
    const submitBtn = document.getElementById('submitBtn');
    const featureList = document.getElementById('featureList');

    const clearPreview = () => {
        if (geoLayer) map.removeLayer(geoLayer);
        geoLayer = null;
        currentGeoJson = null;
        featureList.innerHTML = '<em>Belum ada file.</em>';
        submitBtn.disabled = true;
    };

    /** ===============================
     *  FUNGSI FIX UTAMA:
     *  Membaca ZIP SHP yang memiliki folder
     * ===============================*/
    async function readShpZip(file) {
        const zip = await JSZip.loadAsync(file);

        let shpFile, shxFile, dbfFile, prjFile;

        zip.forEach((relativePath, entry) => {
            if (relativePath.toLowerCase().endsWith(".shp")) shpFile = entry;
            if (relativePath.toLowerCase().endsWith(".shx")) shxFile = entry;
            if (relativePath.toLowerCase().endsWith(".dbf")) dbfFile = entry;
            if (relativePath.toLowerCase().endsWith(".prj")) prjFile = entry;
        });

        if (!shpFile || !shxFile || !dbfFile)
            throw new Error("ZIP tidak berisi SHP lengkap (.shp, .shx, .dbf)");

        const shpBuf = await shpFile.async("arraybuffer");
        const shxBuf = await shxFile.async("arraybuffer");
        const dbfBuf = await dbfFile.async("arraybuffer");
        const prjStr = prjFile ? await prjFile.async("string") : null;

        return await shp({
            shp: shpBuf,
            shx: shxBuf,
            dbf: dbfBuf,
            prj: prjStr
        });
    }

    /** ===============================
     *  FIT & SHOW GEOJSON
     * ===============================*/
    function showGeoJSON(gj) {
        if (geoLayer) map.removeLayer(geoLayer);

        geoLayer = L.geoJSON(gj, {
            style: { color: "#2b8cbe", weight: 2, fillOpacity: 0.45 }
        }).addTo(map);

        try {
            map.fitBounds(geoLayer.getBounds());
        } catch {}

        currentGeoJson = gj;
    }

    /** ===============================
     *  Tampilkan atribut
     * ===============================*/
    function renderList(gj) {
        let html = '<div class="list-group">';
        gj.features.forEach((f, i) => {
            html += `
            <div class="list-group-item">
                <strong>Feature #${i+1}</strong><br>
                <pre>${JSON.stringify(f.properties, null, 2)}</pre>
            </div>`;
        });
        html += '</div>';
        featureList.innerHTML = html;
    }

    /** ===============================
     *  HANDLE FILE UPLOAD
     * ===============================*/
    fileInput.addEventListener('change', async function(e) {
        clearPreview();
        const file = e.target.files[0];
        if (!file) return;

        const name = file.name.toLowerCase();

        try {

            if (name.endsWith(".zip")) {
                const geojson = await readShpZip(file);
                showGeoJSON(geojson);
                renderList(geojson);
                submitBtn.disabled = false;
                return;
            }

            if (name.endsWith(".json") || name.endsWith(".geojson")) {
                const txt = await file.text();
                const gj = JSON.parse(txt);
                showGeoJSON(gj);
                renderList(gj);
                submitBtn.disabled = false;
                return;
            }

            alert("Format tidak didukung");

        } catch (err) {
            console.error("Error:", err);
            alert("Gagal membaca file: " + err.message);
            clearPreview();
        }
    });
});
</script>
@endsection
