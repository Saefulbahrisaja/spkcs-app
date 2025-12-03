@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>
    #map {
        width: 100%;
        height: 420px;
        border-radius: 6px;
    }

    .legend-box {
        background: white;
        padding: 8px 12px;
        border-radius: 6px;
        box-shadow: 0 0 10px rgba(0,0,0,0.15);
        font-size: 13px;
        line-height: 18px;
    }

    .legend-color {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 3px;
        margin-right: 6px;
    }
</style>
@endsection


@section('content')

<!-- ======================= DASHBOARD CARDS ======================= -->
<div class="row">

    <!-- Jumlah Kriteria -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">Jumlah Kriteria</div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link">{{ $kriteria ?? 0 }}</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <!-- Alternatif Lahan -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">Alternatif Lahan</div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link">{{ $alternatif ?? 0 }}</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <!-- Laporan -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">Laporan</div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link">{{ $laporan ?? 0 }}</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- ======================= RINGKASAN LUAS ======================= -->
<div class="row mb-4" id="ringkasanLuasContainer">

    <!-- Kartunya akan dimuat lewat fetch() -->
</div>


<!-- ======================= MAP SECTION ======================= -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-map me-2"></i>Peta Kesesuaian Lahan</h5>
    </div>

    <div class="card-body position-relative">

        <!-- Map -->
        <div id="map"></div>
        <!-- Legend -->
        <div class="legend-box position-absolute" style="top: 15px; right: 15px;">
            <div><span class="legend-color" style="background:#00aa00"></span> S1 – Sangat Sesuai</div>
            <div><span class="legend-color" style="background:#d4d40d"></span> S2 – Cukup Sesuai</div>
            <div><span class="legend-color" style="background:#ff8800"></span> S3 – Marginal</div>
            <div><span class="legend-color" style="background:#cc0000"></span> N – Tidak Sesuai</div>
        </div>
    </div>
</div>

@endsection


@section('content')

<!-- ======================= DASHBOARD CARDS ======================= -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">Jumlah Kriteria</div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="#">{{ $kriteria ?? 0 }}</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">Alternatif Lahan</div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="#">{{ $alternatif ?? 0 }}</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">Laporan</div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="#">{{ $laporan ?? 0 }}</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- ======================= MAP SECTION ======================= -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-map me-2"></i>Peta Kesesuaian Lahan</h5>
    </div>

    <div class="card-body position-relative">

        <!-- Map -->
        <div id="map"></div>

        <!-- Legend -->
        <div class="legend-box position-absolute" style="top: 15px; right: 15px;">
            <div><span class="legend-color" style="background:#00aa00"></span> S1 – Sangat Sesuai</div>
            <div><span class="legend-color" style="background:#d4d40d"></span> S2 – Cukup Sesuai</div>
            <div><span class="legend-color" style="background:#ff8800"></span> S3 – Marginal</div>
            <div><span class="legend-color" style="background:#cc0000"></span> N – Tidak Sesuai</div>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
fetch("{{ route('admin.ringkasan.luas') }}")
    .then(r => r.json())
    .then(data => {

        let totalS1 = Object.values(data.S1 ?? {}).reduce((a,b)=>a+b,0) / 10000;
        let totalS2 = Object.values(data.S2 ?? {}).reduce((a,b)=>a+b,0) / 10000;
        let totalS3 = Object.values(data.S3 ?? {}).reduce((a,b)=>a+b,0) / 10000;
        let totalN  = Object.values(data.N  ?? {}).reduce((a,b)=>a+b,0) / 10000;

        document.getElementById('ringkasanLuasContainer').innerHTML = `
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-success shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-success fw-bold">Total Luas S1</h6>
                        <div class="h5 mb-0">${totalS1.toFixed(2)} ha</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-warning shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-warning fw-bold">Total Luas S2</h6>
                        <div class="h5 mb-0">${totalS2.toFixed(2)} ha</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-orange shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-orange fw-bold">Total Luas S3</h6>
                        <div class="h5 mb-0">${totalS3.toFixed(2)} ha</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-danger shadow mb-4">
                    <div class="card-body">
                        <h6 class="text-danger fw-bold">Total Luas N</h6>
                        <div class="h5 mb-0">${totalN.toFixed(2)} ha</div>
                    </div>
                </div>
            </div>
        `;
    });
// =========================
// BASEMAPS
// =========================
var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: "© OpenStreetMap"
});
var esriSat = L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", 
    { attribution: "Tiles © Esri" }
);

var map = L.map("map", {
    center: [-6.5, 106.16],
    zoom: 9,
    layers: [osm]
});

// =========================
// WARNA KELAS
// =========================
function warnaKelas(k) {
    return {
        S1: "#00aa00",
        S2: "#d4d40d",
        S3: "#f97316",
        N: "#cc0000"
    }[k] || "#999";
}

// =========================
// BUAT LAYER GROUP PER KELAS
// =========================
var layerS1 = L.layerGroup();
var layerS2 = L.layerGroup();
var layerS3 = L.layerGroup();
var layerN  = L.layerGroup();

// Untuk memetakan string ke layerGroup
var classLayers = {
    S1: layerS1,
    S2: layerS2,
    S3: layerS3,
    N:  layerN
};

// =========================
// LOAD GEOJSON
// =========================
fetch("{{ route('map.geojson') }}")
    .then(r => r.json())
    .then(json => {
        L.geoJSON(json, {
            style: f => ({
                color: warnaKelas(f.properties.kelas_kesesuaian),
                weight: 2,
                fillOpacity: 0.45
            }),
            onEachFeature: (feature, layer) => {
                let p = feature.properties;

                layer.bindPopup(`
                    <strong>Lokasi:</strong> ${p.lokasi}<br>
                    <strong>Kelas:</strong> ${p.kelas_kesesuaian}<br>
                    <strong>Nilai Total:</strong> ${p.nilai_total}<br>
                    <strong>VIKOR Ranking:</strong> ${p.vikor_ranking}
                `);

                // Masukkan feature ke layerGroup sesuai kelas
                let kelas = p.kelas_kesesuaian || 'N';
                classLayers[kelas].addLayer(layer);
            }
        });

        // Tambahkan semua layer ke map (default: ON)
        layerS1.addTo(map);
        layerS2.addTo(map);
        layerS3.addTo(map);
        layerN.addTo(map);
    });

// =========================
// LAYER CONTROL
// =========================
var baseMaps = {
    "OSM Standard": osm,
    "Esri Satellite": esriSat
};

var overlayMaps = {
    "S1 - Sangat Sesuai": layerS1,
    "S2 - Cukup Sesuai": layerS2,
    "S3 - Marginal": layerS3,
    "N - Tidak Sesuai": layerN
};

L.control.layers(baseMaps, overlayMaps, { collapsed: false }).addTo(map);
</script>
@endsection

